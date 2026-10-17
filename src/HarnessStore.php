<?php
declare(strict_types=1);
namespace vielhuber\aihelper;

final class HarnessStore
{
    private const CREDENTIAL_FILES = ['auth.json', '.credentials.json', 'antigravity-oauth-token'];

    /** @var array<string, string> */
    private array $links = [];

    /** @var array<string, string> */
    private array $versions = [];

    private bool $isolated = false;

    /**
     * Run filesystem operations on the harness host when it is reached over SSH.
     */
    public function __construct(private ?\Closure $remoteCommand = null) {}

    /**
     * Recover interrupted writes before exposing the shared profile to a new run.
     *
     * @param string[] $directories
     * @param array<string, string> $links
     */
    public function prepare(array $directories, array $links): void
    {
        $this->links = array_merge($this->links, $links);
        if ($this->remoteCommand !== null) {
            $paths = implode(' ', array_map('escapeshellarg', $directories));
            ($this->remoteCommand)('umask 077; mkdir -p ' . $paths . ' && chmod 700 ' . $paths);
        }
        if ($this->remoteCommand === null) {
            foreach ($directories as $directory) {
                if (
                    !is_dir($directory) &&
                    !mkdir($directory, permissions: 0o700, recursive: true) &&
                    !is_dir($directory)
                ) {
                    throw new \RuntimeException('harness: failed to create ' . $directory);
                }
                chmod($directory, permissions: 0o700);
            }
        }
        foreach ($links as $link => $target) {
            $this->synchronize($link, $target, recover: true);
        }
    }

    /**
     * Publish atomic CLI rewrites without overwriting a newer login or refresh.
     */
    public function persist(): void
    {
        foreach ($this->links as $link => $target) {
            $this->synchronize($link, $target, recover: false);
        }
    }

    /**
     * Keep in-place CLI writers away from the shared credential file as well.
     */
    public function isolateAuthentication(): void
    {
        $this->isolated = true;
        $this->persist();
    }

    /**
     * Compare the generation read by this run, then replace the shared file atomically.
     */
    private function synchronize(string $link, string $target, bool $recover): void
    {
        clearstatcache();
        $current = null;
        $contents = null;
        $newer = false;
        $older = false;
        $linked = false;
        $generation = '';
        if ($this->remoteCommand === null) {
            $linked = is_link($link) && readlink($link) === $target;
            $current = is_file($target) ? (string) file_get_contents($target) : null;
            if (!$linked && !is_link($link) && is_file($link)) {
                $contents = (string) file_get_contents($link);
                $newer = !is_file($target) || filemtime($link) > filemtime($target);
                $older = is_file($target) && filemtime($link) < filemtime($target);
            }
            if (!$linked && !is_link($link) && file_exists($link) && !is_file($link)) {
                throw new \RuntimeException('harness: refusing to replace ' . $link);
            }
            if (is_file($link . '.aihelper-version')) {
                $generation = trim((string) file_get_contents($link . '.aihelper-version'));
            }
        }
        if ($this->remoteCommand !== null) {
            $quotedLink = escapeshellarg($link);
            $quotedTarget = escapeshellarg($target);
            $snapshot = ($this->remoteCommand)(
                'link=' .
                    $quotedLink .
                    '; target=' .
                    $quotedTarget .
                    '; ' .
                    'if [ -L "$link" ] && [ "$(readlink "$link")" = "$target" ]; then printf L; ' .
                    'elif [ -f "$link" ] && [ ! -L "$link" ]; then printf F; ' .
                    'elif [ -e "$link" ] && [ ! -L "$link" ]; then exit 1; else printf M; fi; ' .
                    '[ "$link" -nt "$target" ] && printf N; [ "$target" -nt "$link" ] && printf O; ' .
                    'printf :; if [ -f "$target" ]; then base64 < "$target" | tr -d "\\r\\n"; fi; ' .
                    'printf :; if [ -f "$link" ] && [ ! -L "$link" ]; then base64 < "$link" | tr -d "\\r\\n"; fi; ' .
                    'printf :; if [ -f "$link.aihelper-version" ]; then cat "$link.aihelper-version"; fi'
            );
            [$flags, $encodedCurrent, $encodedContents, $generation] = explode(':', $snapshot, limit: 4);
            $linked = str_contains($flags, 'L');
            $newer = str_contains($flags, 'N');
            $older = str_contains($flags, 'O');
            $current = $encodedCurrent === '' ? null : base64_decode($encodedCurrent, strict: true);
            $contents = str_contains($flags, 'F') ? base64_decode($encodedContents, strict: true) : null;
            if ($current === false || $contents === false) {
                throw new \RuntimeException('harness: failed to read store snapshot.');
            }
        }
        $tracked = preg_match('/^[a-f0-9]{64}$/D', $generation) === 1;
        if (!array_key_exists($link, $this->versions) && $tracked) {
            $this->versions[$link] = $generation;
        }
        $version = hash('sha256', $current ?? '');
        $replica = $this->isolated && in_array(basename($target), self::CREDENTIAL_FILES, strict: true);
        if ($linked && !$replica) {
            $this->versions[$link] = $version;
            return;
        }
        if (
            $replica &&
            !$linked &&
            hash('sha256', $contents ?? '') === ($this->versions[$link] ?? null) &&
            $version === ($this->versions[$link] ?? null)
        ) {
            return;
        }
        if (!$recover && !$linked && ($contents === null || trim($contents) === '')) {
            return;
        }
        $publish =
            $contents !== null &&
            trim($contents) !== '' &&
            (!$older || $tracked) &&
            ($recover && !$replica && !array_key_exists($link, $this->versions)
                ? $newer
                : ($this->versions[$link] ?? null) === $version);
        $currentAuth = json_decode($current ?? '', associative: true);
        $nextAuth = json_decode($contents ?? '', associative: true);
        if (
            in_array(basename($target), self::CREDENTIAL_FILES, strict: true) &&
            is_array($currentAuth) &&
            !is_array($nextAuth) &&
            !$recover &&
            !$linked
        ) {
            return;
        }
        foreach (['claudeAiOauth', 'tokens', 'token'] as $key) {
            $previous = $currentAuth[$key] ?? null;
            $next = $nextAuth[$key] ?? null;
            if (!is_array($previous) && !is_array($next)) {
                continue;
            }
            $accessToken = $next['accessToken'] ?? ($next['access_token'] ?? null);
            $refreshToken = $next['refreshToken'] ?? ($next['refresh_token'] ?? null);
            $previousRefresh = $previous['refreshToken'] ?? ($previous['refresh_token'] ?? null);
            if (
                !is_string($accessToken) ||
                $accessToken === '' ||
                (($previousRefresh !== null || $refreshToken !== null) &&
                    (!is_string($refreshToken) || $refreshToken === ''))
            ) {
                $publish = false;
            }
            $expiry = [];
            foreach ([$previous, $next] as $tokens) {
                $expiresAt = $tokens['expiresAt'] ?? ($tokens['expiry'] ?? null);
                if ($key === 'tokens') {
                    $parts = explode('.', (string) ($tokens['access_token'] ?? ''));
                    $claims = json_decode(
                        (string) base64_decode(strtr($parts[1] ?? '', from: '-_', to: '+/'), strict: true),
                        associative: true
                    );
                    $expiresAt = $claims['exp'] ?? $expiresAt;
                }
                $expiry[] = is_numeric($expiresAt) ? (float) $expiresAt : strtotime((string) $expiresAt);
            }
            if ($expiry[0] !== false && $expiry[1] !== false && $expiry[1] < $expiry[0]) {
                $publish = false;
            }
        }
        if ($this->remoteCommand !== null) {
            $digest =
                'digest() { if command -v sha256sum >/dev/null 2>&1; then sha256sum; else shasum -a 256; fi | cut -d " " -f 1; }; ';
            $sourceVersion = hash('sha256', $contents ?? '');
            $committed = ($this->remoteCommand)(
                'umask 077; link=' .
                    escapeshellarg($link) .
                    '; target=' .
                    escapeshellarg($target) .
                    '; ' .
                    'locks="${TMPDIR:-/tmp}/aihelper-store-$(id -u)"; mkdir -p "$locks" && ' .
                    '[ ! -L "$locks" ] && [ -O "$locks" ] && chmod 700 "$locks" || exit 1; ' .
                    $digest .
                    '( flock -w 5 9 || exit 1; ' .
                    (!$replica ? '[ ! -L "$link" ] || [ "$(readlink "$link")" != "$target" ] || exit 0; ' : '') .
                    '[ ! -f "$target" ] && current="$(printf "" | digest)" || current="$(digest < "$target")"; ' .
                    '[ "$current" = ' .
                    escapeshellarg($version) .
                    ' ] || exit 0; ' .
                    ($contents !== null
                        ? '[ "$(digest < "$link")" = ' . escapeshellarg($sourceVersion) . ' ] || exit 0; '
                        : '') .
                    ($publish
                        ? 'temporary=$(mktemp "$target.aihelper.XXXXXX") || exit 1; ' .
                            'trap \'rm -f "$temporary"\' EXIT; ' .
                            'cat "$link" > "$temporary" && chmod 600 "$temporary" || exit 1; ' .
                            '[ "$(digest < "$temporary")" = ' .
                            escapeshellarg($sourceVersion) .
                            ' ] || exit 0; ' .
                            'if [ -f "$target" ]; then [ "$(digest < "$target")" = "$current" ] || exit 0; ' .
                            'else [ "$current" = "$(printf "" | digest)" ] || exit 0; fi; ' .
                            'mv -f "$temporary" "$target" || exit 1; '
                        : '') .
                    ($replica && $publish
                        ? ''
                        : 'temporary=$(mktemp "$link.aihelper.XXXXXX") || exit 1; ' .
                            'trap \'rm -f "$temporary"\' EXIT; ' .
                            ($replica
                                ? 'if [ -f "$target" ]; then cat "$target" > "$temporary"; else printf "" > "$temporary"; fi; ' .
                                    '[ "$(digest < "$temporary")" = "$current" ] && chmod 600 "$temporary" || exit 1; '
                                : 'rm -f "$temporary" && ln -s "$target" "$temporary" || exit 1; ') .
                            'if [ -L "$link" ] && [ -d "$link" ]; then rm -f "$link" || exit 1; fi; ' .
                            'mv -f "$temporary" "$link" || exit 1; ') .
                    'printf %s ' .
                    escapeshellarg($publish ? hash('sha256', $contents) : $version) .
                    '; ' .
                    ') 9>"$locks/' .
                    hash('sha256', $target) .
                    '.lock"'
            );
            if ($committed !== '') {
                if ($replica) {
                    $this->writeGeneration($link, $committed);
                }
                $this->versions[$link] = $committed;
            }
            return;
        }
        $user = function_exists('posix_geteuid') ? posix_geteuid() : getmyuid();
        $locks = sys_get_temp_dir() . '/aihelper-store-' . $user;
        if (
            (!is_dir($locks) && !mkdir($locks, permissions: 0o700) && !is_dir($locks)) ||
            is_link($locks) ||
            fileowner($locks) !== $user
        ) {
            throw new \RuntimeException('harness: failed to create the shared store lock directory.');
        }
        chmod($locks, permissions: 0o700);
        $lockPath = $locks . '/' . hash('sha256', $target) . '.lock';
        $lock = fopen($lockPath, mode: 'c');
        if ($lock === false) {
            throw new \RuntimeException('harness: failed to lock the shared store.');
        }
        chmod($lockPath, permissions: 0o600);
        $temporary = null;
        try {
            if (!flock($lock, LOCK_EX)) {
                throw new \RuntimeException('harness: failed to lock the shared store.');
            }
            clearstatcache();
            if (
                (!$replica && is_link($link) && readlink($link) === $target) ||
                (is_file($target) ? hash_file('sha256', $target) : hash('sha256', data: '')) !== $version ||
                ($contents !== null && (!is_file($link) || hash_file('sha256', $link) !== hash('sha256', $contents)))
            ) {
                return;
            }
            if ($publish) {
                $temporary = tempnam(dirname($target), prefix: '.aihelper-');
                if (
                    $temporary === false ||
                    file_put_contents($temporary, $contents) !== strlen($contents) ||
                    !chmod($temporary, permissions: 0o600) ||
                    !rename($temporary, $target)
                ) {
                    throw new \RuntimeException('harness: failed to persist the shared store.');
                }
            }
            if (!$replica || !$publish) {
                $temporary = tempnam(dirname($link), prefix: '.aihelper-');
                if ($temporary === false) {
                    throw new \RuntimeException('harness: failed to prepare ' . $link);
                }
                $prepared = $replica
                    ? file_put_contents($temporary, $current ?? '') === strlen($current ?? '') &&
                        chmod($temporary, permissions: 0o600)
                    : unlink($temporary) && symlink($target, $temporary);
                if (!$prepared || !rename($temporary, $link)) {
                    throw new \RuntimeException('harness: failed to link ' . $link);
                }
            }
            $nextVersion = $publish ? hash('sha256', $contents) : $version;
            if ($replica) {
                $this->writeGeneration($link, $nextVersion);
            }
            $this->versions[$link] = $nextVersion;
        } finally {
            if (is_string($temporary) && (is_link($temporary) || is_file($temporary))) {
                unlink($temporary);
            }
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    /**
     * Retain the comparison baseline across crashes, including same-second token rotations.
     */
    private function writeGeneration(string $link, string $generation): void
    {
        $path = $link . '.aihelper-version';
        if ($this->remoteCommand !== null) {
            ($this->remoteCommand)(
                'umask 077; path=' .
                    escapeshellarg($path) .
                    '; ' .
                    'temporary=$(mktemp "$path.XXXXXX") || exit 1; trap \'rm -f "$temporary"\' EXIT; ' .
                    'printf %s ' .
                    escapeshellarg($generation) .
                    ' > "$temporary" && ' .
                    'chmod 600 "$temporary" && mv -f "$temporary" "$path"'
            );
            return;
        }
        $temporary = tempnam(dirname($path), prefix: '.aihelper-generation-');
        try {
            if (
                $temporary === false ||
                file_put_contents($temporary, $generation) !== strlen($generation) ||
                !chmod($temporary, permissions: 0o600) ||
                !rename($temporary, $path)
            ) {
                throw new \RuntimeException('harness: failed to persist the credential generation.');
            }
        } finally {
            if (is_string($temporary) && is_file($temporary)) {
                unlink($temporary);
            }
        }
    }
}
