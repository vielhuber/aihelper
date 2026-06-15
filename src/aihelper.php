<?php
declare(strict_types=1);
namespace vielhuber\aihelper;

use vielhuber\stringhelper\__;

abstract class aihelper
{
    public ?string $provider = null;
    public ?string $title = null;
    public ?string $name = null;
    public ?string $icon = null;
    protected ?string $url = null;
    public array $models = [];
    public ?bool $supports_mcp_remote = null;
    public ?bool $supports_stream = null;

    protected ?string $model = null;
    protected ?float $temperature = null;
    protected ?int $timeout = null;
    protected ?string $api_key = null;
    protected ?string $log = null;
    protected ?int $max_tries = null;
    protected ?bool $enable_thinking = null;
    protected ?array $mcp_servers = null;
    protected ?string $mcp_servers_call_type = null;
    protected array $mcp_servers_tools_map = [];

    protected ?bool $stream = null;
    protected mixed $stream_response = null;
    protected ?string $stream_event = null;
    protected ?string $stream_buffer_in = null;
    protected ?string $stream_buffer_data = null;
    protected ?string $stream_current_block_type = null;
    protected bool $stream_first_text_sent = false;
    protected bool $stream_running = false;
    protected bool $stream_in_think = false;
    protected string $stream_think_tag_buf = '';
    protected string $stream_reasoning_buffer = '';
    protected bool $stream_tool_call_strip_in_block = false;
    protected string $stream_tool_call_strip_tag_buf = '';
    protected ?\Closure $stream_callback = null;

    protected ?string $session_id = null;
    protected static array $sessions = [];

    protected ?bool $auto_compact = null;
    protected ?string $auto_compact_summary = null;
    protected ?string $auto_compact_cache = null;

    public static function create(
        string $provider,
        ?string $model = null,
        ?float $temperature = null,
        ?int $timeout = null,
        ?string $api_key = null,
        ?string $log = null,
        ?int $max_tries = null,
        ?array $mcp_servers = null,
        ?string $mcp_servers_call_type = null,
        ?string $session_id = null,
        ?array $history = null,
        ?bool $stream = null,
        ?string $url = null,
        ?bool $enable_thinking = null,
        ?bool $auto_compact = null
    ): ?self {
        if ($provider === 'openai') {
            return new ai_openai(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        if ($provider === 'anthropic') {
            return new ai_anthropic(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        if ($provider === 'google') {
            return new ai_google(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        if ($provider === 'xai') {
            return new ai_xai(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        if ($provider === 'deepseek') {
            return new ai_deepseek(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        if ($provider === 'openrouter') {
            return new ai_openrouter(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        if ($provider === 'llamacpp') {
            return new ai_llamacpp(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        if ($provider === 'lmstudio') {
            return new ai_lmstudio(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        if ($provider === 'nvidia') {
            return new ai_nvidia(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        if ($provider === 'codex') {
            return new ai_codex(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        if ($provider === 'elevenlabs') {
            return new ai_elevenlabs(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        if ($provider === 'test') {
            return new ai_test(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                mcp_servers_call_type: $mcp_servers_call_type,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url,
                enable_thinking: $enable_thinking,
                auto_compact: $auto_compact
            );
        }
        return null;
    }

    public static function getProviders(): array
    {
        $data = [];
        foreach (
            [
                new ai_anthropic(),
                new ai_google(),
                new ai_openai(),
                new ai_xai(),
                new ai_deepseek(),
                new ai_openrouter(),
                new ai_codex(),
                new ai_llamacpp(),
                new ai_lmstudio(),
                new ai_nvidia(),
                new ai_elevenlabs(),
                new ai_test()
            ]
            as $providers__value
        ) {
            $data[] = [
                'provider' => $providers__value->provider,
                'title' => $providers__value->title,
                'name' => $providers__value->name,
                'icon' => $providers__value->icon,
                'models' => $providers__value->models
            ];
        }
        return $data;
    }

    public static function getMcpOnlineStatus(?string $url = null, ?string $authorization_token = null): bool
    {
        try {
            // add trailing slash to avoid 307 redirect
            if (substr($url, -1) !== '/') {
                $url .= '/';
            }

            // use mcp ping endpoint
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode([
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'ping'
                ])
            );
            $headers = ['Content-Type: application/json', 'Accept: application/json, text/event-stream'];
            if ($authorization_token) {
                $headers[] = 'Authorization: Bearer ' . $authorization_token;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (($httpCode >= 200 && $httpCode < 400) || $httpCode === 401 || $httpCode === 403) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception) {
            return false;
        }
    }

    public static function getMcpMetaInfo(?string $url = null, ?string $authorization_token = null): array
    {
        $data = [
            'name' => null,
            'online' => false,
            'instructions' => null,
            'tools' => []
        ];

        $data['online'] = self::getMcpOnlineStatus($url, $authorization_token);

        if ($data['online'] === false) {
            return $data;
        }

        try {
            // name / instructions
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode([
                    'jsonrpc' => '2.0',
                    'id' => 0,
                    'method' => 'initialize',
                    'params' => [
                        'protocolVersion' => date('Y-m-d'),
                        'capabilities' => new \stdClass(),
                        'clientInfo' => [
                            'name' => 'ping',
                            'version' => '1.0.0'
                        ]
                    ]
                ])
            );
            $headers = ['Content-Type: application/json', 'Accept: application/json, text/event-stream'];
            if ($authorization_token) {
                $headers[] = 'Authorization: Bearer ' . $authorization_token;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $initResponse = curl_exec($ch);
            if ($initResponse) {
                // parse sse response if needed
                if (strpos($initResponse, 'event: message') !== false) {
                    preg_match('/^data: (.+)$/m', $initResponse, $matches);
                    if (isset($matches[1])) {
                        $initResponse = trim($matches[1]);
                    }
                }

                $initData = json_decode($initResponse, true);
                if (isset($initData['result']['serverInfo']['name'])) {
                    $data['name'] = $initData['result']['serverInfo']['name'];
                }
                if (isset($initData['result']['instructions'])) {
                    $data['instructions'] = $initData['result']['instructions'];
                }
            }

            // tools
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode([
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'tools/list'
                ])
            );
            $headers = ['Content-Type: application/json', 'Accept: application/json, text/event-stream'];
            if ($authorization_token) {
                $headers[] = 'Authorization: Bearer ' . $authorization_token;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $toolsResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode >= 200 && $httpCode < 300 && $toolsResponse) {
                // parse sse response if needed
                if (strpos($toolsResponse, 'event: message') !== false) {
                    preg_match('/^data: (.+)$/m', $toolsResponse, $matches);
                    if (isset($matches[1])) {
                        $toolsResponse = trim($matches[1]);
                    }
                }
                $toolsData = json_decode($toolsResponse, true);
                if (isset($toolsData['result']['tools']) && is_array($toolsData['result']['tools'])) {
                    $data['tools'] = $toolsData['result']['tools'];
                }
            }
            return $data;
        } catch (\Exception) {
            return $data;
        }
    }

    public static function callMcpTool(
        ?string $name = null,
        ?array $args = [],
        ?string $url = null,
        ?string $authorization_token = null
    ): ?array {
        try {
            // add trailing slash to avoid 307 redirect
            if (substr($url, -1) !== '/') {
                $url .= '/';
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode([
                    'jsonrpc' => '2.0',
                    'id' => rand(100, 999),
                    'method' => 'tools/call',
                    'params' => [
                        'name' => $name,
                        'arguments' => (object) $args
                    ]
                ])
            );
            $headers = ['Content-Type: application/json', 'Accept: application/json, text/event-stream'];
            if ($authorization_token) {
                $headers[] = 'Authorization: Bearer ' . $authorization_token;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode >= 200 && $httpCode < 300 && $response) {
                // parse sse response if needed
                if (strpos($response, 'event: message') !== false) {
                    preg_match('/^data: (.+)$/m', $response, $matches);
                    if (isset($matches[1])) {
                        $response = trim($matches[1]);
                    }
                }
                $decoded_response = json_decode($response, true);
                return $decoded_response;
            }
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function getDefaultModel(): ?string
    {
        foreach ($this->models as $models__value) {
            if ($models__value['default'] === true) {
                return $models__value['name'];
            }
        }
        return null;
    }

    public function __construct(
        ?string $model = null,
        ?float $temperature = null,
        ?int $timeout = null,
        ?string $api_key = null,
        ?string $log = null,
        ?int $max_tries = null,
        ?array $mcp_servers = null,
        ?string $mcp_servers_call_type = null,
        ?string $session_id = null,
        ?array $history = null,
        ?bool $stream = null,
        ?string $url = null,
        ?bool $enable_thinking = null,
        ?bool $auto_compact = null
    ) {
        if ($temperature === null) {
            $temperature = 1.0;
        }
        if ($timeout === null) {
            $timeout = 300;
        }
        if ($log !== null) {
            $this->log = $log;
        }
        if ($url !== null) {
            $this->url = $url;
        }
        if ($enable_thinking !== null) {
            $this->enable_thinking = $enable_thinking;
        }
        if ($api_key !== null) {
            $this->api_key = $api_key;
        }
        if (empty($this->models) && method_exists($this, 'fetchModels')) {
            foreach ($this->fetchModels() as $models__value) {
                $this->models[] = [
                    'name' => $models__value['name'],
                    'context_length' => $models__value['context_length'] ?? 128000,
                    'max_output_tokens' => $models__value['max_output_tokens'] ?? 16384,
                    'costs' => $models__value['costs'] ?? ['input' => 0, 'input_cached' => 0, 'output' => 0],
                    'supports_temperature' => $models__value['supports_temperature'] ?? true,
                    'supports_tools' => $models__value['supports_tools'] ?? true,
                    'default' => isset($models__value['default']) ? $models__value['default'] : false,
                    'test' => isset($models__value['test']) ? $models__value['test'] : false
                ];
            }
        }
        if ($model === null) {
            $model = $this->getDefaultModel();
        }
        if (method_exists($this, 'loadModel')) {
            $this->loadModel($model);
        }
        $this->max_tries = $max_tries !== null ? $max_tries : 1;
        $supports_tools = true;
        foreach ($this->models as $models__value) {
            if (($models__value['name'] ?? null) !== $model) {
                continue;
            }
            $supports_tools = $models__value['supports_tools'] ?? true;
            break;
        }
        $supports_mcp = $this->supports_mcp_remote || $supports_tools;
        if ($supports_mcp && $mcp_servers !== null && !empty($mcp_servers)) {
            if (is_array(current($mcp_servers))) {
                $this->mcp_servers = $mcp_servers;
            } else {
                $this->mcp_servers = [$mcp_servers];
            }
        }
        if ($supports_mcp && $this->mcp_servers !== null) {
            if ($mcp_servers_call_type === 'local' && $supports_tools) {
                $this->mcp_servers_call_type = 'local';
            } elseif ($this->supports_mcp_remote) {
                $this->mcp_servers_call_type = 'remote';
            } else {
                $this->mcp_servers_call_type = 'local';
            }
        }
        $this->stream = $this->supports_stream && $stream === true ? true : false;

        $this->model = $model;
        $this->temperature = $temperature;
        $this->timeout = $timeout;
        if (__::nx($session_id)) {
            $this->session_id = md5(uniqid());
        } else {
            $this->session_id = $session_id;
        }
        if (!array_key_exists($this->session_id, self::$sessions)) {
            self::$sessions[$this->session_id] = [];
        }
        if (__::x($history)) {
            self::$sessions[$this->session_id] = $history;
        }
        // auto-compact setup. persistent compact state (running summary +
        // compacted session snapshot as JSON) lives in the system temp dir,
        // keyed by session_id so that subsequent calls with the same
        // session_id reuse the cached state. autoCompactSession() handles
        // both reading the cache (rehydration) and writing it (persistence).
        if ($auto_compact === true) {
            $this->auto_compact = true;
            $cacheDir = sys_get_temp_dir() . '/aihelper-cache';
            // trailing is_dir() handles the parallel-worker race
            if (!is_dir($cacheDir) && !mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
                $this->log('⚠️ auto_compact: failed to create cache dir ' . $cacheDir);
            }
            $this->auto_compact_cache =
                $cacheDir . '/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->session_id) . '.txt';
        }
    }

    public function ask(?string $prompt = null, mixed $files = null): array
    {
        $this->autoCompactSession();
        $return = ['response' => null, 'success' => false, 'costs' => 0.0];
        $max_tries = $this->max_tries;
        while ($return['success'] === false && $max_tries > 0) {
            if ($max_tries < $this->max_tries) {
                $attempt = $this->max_tries - $max_tries;
                $backoff_s = 15 * (int) pow(2, $attempt - 1);
                $this->log('⚠️ tries left: ' . $max_tries . ' — backoff ' . $backoff_s . 's');
                sleep($backoff_s);
            }
            try {
                $return = $this->askThis(
                    prompt: $prompt,
                    files: $files,
                    add_prompt_to_session: $max_tries === $this->max_tries,
                    prev_output_text: null,
                    prev_costs: $return['costs']
                );
            } catch (\RuntimeException $e) {
                if (str_starts_with($e->getMessage(), 'whitespace runaway')) {
                    $this->log('⚠️ caught whitespace runaway — converting to retry: ' . $e->getMessage());
                    $return = [
                        'response' => 'whitespace runaway detected, retrying',
                        'success' => false,
                        'costs' => $return['costs'] ?? 0.0
                    ];
                } else {
                    throw $e;
                }
            }
            $this->log($return, 'return');
            $max_tries--;
        }
        $this->log(
            sprintf(
                'success=%s call_type=%s map_count=%d',
                var_export($return['success'] ?? null, true),
                (string) ($this->mcp_servers_call_type ?? 'null'),
                count($this->mcp_servers_tools_map ?? [])
            ),
            'pre-tool-loop'
        );
        if (
            $return['success'] === true &&
            $this->mcp_servers_call_type === 'local' &&
            !empty($this->mcp_servers_tools_map)
        ) {
            $return = $this->runLocalToolLoop($return);
        }
        return $return;
    }

    public function image(
        ?string $prompt = null,
        int $n = 1,
        ?string $aspect_ratio = null,
        mixed $input_file = null,
        ?string $output_file = null
    ): array {
        $supports = false;
        foreach ($this->models as $models__value) {
            if (($models__value['name'] ?? null) === $this->model) {
                $supports = ($models__value['supports_text_to_image'] ?? false) === true;
                break;
            }
        }
        if ($supports !== true) {
            throw new \BadMethodCallException('Model "' . $this->model . '" does not support image generation.');
        }
        return $this->imageThis(
            prompt: $prompt,
            n: $n,
            aspect_ratio: $aspect_ratio,
            input_file: $input_file,
            output_file: $output_file
        );
    }

    public function audio(
        ?string $prompt = null,
        ?string $voice = null,
        ?float $speed = null,
        ?string $output_file = null
    ): array {
        $supports = false;
        foreach ($this->models as $models__value) {
            if (($models__value['name'] ?? null) === $this->model) {
                $supports = ($models__value['supports_text_to_audio'] ?? false) === true;
                break;
            }
        }
        if ($supports !== true) {
            throw new \BadMethodCallException('Model "' . $this->model . '" does not support audio generation.');
        }
        return $this->audioThis(prompt: $prompt, voice: $voice, speed: $speed, output_file: $output_file);
    }

    /**
     * SSRF guard for caller-supplied http(s) URLs: only allow public IPs.
     * Reject when DNS resolves to private/reserved/loopback/link-local ranges
     * (FILTER_FLAG_NO_PRIV_RANGE | NO_RES_RANGE), or when the host cannot be
     * resolved at all.
     */
    protected static function isPublicHttpUrl(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!is_string($host) || $host === '') {
            return false;
        }
        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false;
    }

    /**
     * Fetch a URL's body via curl with a fixed timeout and no redirects.
     * Returns null on any failure (HTTP >= 400, transport error, empty body).
     * Used in place of file_get_contents() to avoid the @-suppression and to
     * keep redirects from defeating the SSRF guard.
     */
    protected static function fetchUrlBinary(string $url, int $timeout): ?string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_CONNECTTIMEOUT => $timeout
        ]);
        $bin = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (!is_string($bin) || $bin === '' || $http >= 400) {
            return null;
        }
        return $bin;
    }

    protected function imageThis(
        ?string $prompt = null,
        int $n = 1,
        ?string $aspect_ratio = null,
        mixed $input_file = null,
        ?string $output_file = null
    ): array {
        $is_edit = $input_file !== null;
        $headers = [];
        $tmp_input = null;
        if ($this->name === 'google') {
            // Google Imagen via the `:predict` endpoint. Different URL pattern,
            // different auth (query-param `?key=`), different body shape, and
            // no edit support — `imagen-capability` would be a separate model.
            if ($is_edit) {
                $this->log('⛔ image: Imagen :predict does not support edit/input_file');
                return [
                    'response' => 'Imagen generate does not support image-to-image edit',
                    'success' => false,
                    'costs' => 0.0
                ];
            }
            $aspect_payload = '1:1';
            if (
                $aspect_ratio !== null &&
                $aspect_ratio !== '' &&
                preg_match('/^(\d+(?:\.\d+)?)\s*:\s*(\d+(?:\.\d+)?)$/', $aspect_ratio, $aspect_ratio__match) === 1 &&
                (float) $aspect_ratio__match[1] > 0 &&
                (float) $aspect_ratio__match[2] > 0
            ) {
                $aspect_ratio__target = (float) $aspect_ratio__match[1] / (float) $aspect_ratio__match[2];
                $aspect_ratio__candidates = [
                    '1:1' => 1.0,
                    '16:9' => 16 / 9,
                    '9:16' => 9 / 16,
                    '4:3' => 4 / 3,
                    '3:4' => 3 / 4
                ];
                $aspect_ratio__best_delta = PHP_FLOAT_MAX;
                foreach ($aspect_ratio__candidates as $label => $val) {
                    $d = abs(log($aspect_ratio__target / $val));
                    if ($d < $aspect_ratio__best_delta) {
                        $aspect_ratio__best_delta = $d;
                        $aspect_payload = $label;
                    }
                }
            }
            $payload = [
                'instances' => [['prompt' => (string) $prompt]],
                'parameters' => [
                    'sampleCount' => $n,
                    'aspectRatio' => $aspect_payload,
                    'personGeneration' => 'ALLOW_ADULT'
                ]
            ];
            $endpoint = $this->url . '/models/' . $this->model . ':predict?key=' . $this->api_key;
            $headers = ['Content-Type: application/json'];
            $body = json_encode($payload);
        } else {
            // OpenAI / xAI / DALL-E shape. Edits: OpenAI uses multipart
            // `/images/edits`, xAI uses the same path but expects JSON with
            // image:{url,type}.
            $endpoint = $this->url . '/images/' . ($is_edit ? 'edits' : 'generations');
            $payload = ['model' => $this->model, 'prompt' => (string) $prompt, 'n' => $n];
            // Universal `aspect_ratio` ("16:9", "1:1", …) is translated per-provider:
            // xAI accepts it natively as `aspect_ratio` (discrete enum); OpenAI
            // only knows `size` and needs the ratio mapped to one of its pixel enums.
            if (
                $aspect_ratio !== null &&
                $aspect_ratio !== '' &&
                preg_match('/^(\d+(?:\.\d+)?)\s*:\s*(\d+(?:\.\d+)?)$/', $aspect_ratio, $aspect_ratio__match) === 1 &&
                (float) $aspect_ratio__match[1] > 0 &&
                (float) $aspect_ratio__match[2] > 0
            ) {
                $aspect_ratio__target = (float) $aspect_ratio__match[1] / (float) $aspect_ratio__match[2];
                if ($this->name === 'xai') {
                    $aspect_ratio__candidates = [
                        '1:1' => 1.0,
                        '16:9' => 16 / 9,
                        '9:16' => 9 / 16,
                        '4:3' => 4 / 3,
                        '3:4' => 3 / 4,
                        '3:2' => 3 / 2,
                        '2:3' => 2 / 3,
                        '2:1' => 2.0,
                        '1:2' => 0.5,
                        '19.5:9' => 19.5 / 9,
                        '9:19.5' => 9 / 19.5,
                        '20:9' => 20 / 9,
                        '9:20' => 9 / 20
                    ];
                    $aspect_ratio__payload_key = 'aspect_ratio';
                    $aspect_ratio__fallback = '1:1';
                } elseif (str_starts_with((string) $this->model, 'dall-e-2')) {
                    $aspect_ratio__candidates = ['256x256' => 1.0, '512x512' => 1.0, '1024x1024' => 1.0];
                    $aspect_ratio__payload_key = 'size';
                    $aspect_ratio__fallback = '1024x1024';
                } elseif (str_starts_with((string) $this->model, 'dall-e-3')) {
                    $aspect_ratio__candidates = [
                        '1024x1024' => 1.0,
                        '1792x1024' => 1792 / 1024,
                        '1024x1792' => 1024 / 1792
                    ];
                    $aspect_ratio__payload_key = 'size';
                    $aspect_ratio__fallback = '1024x1024';
                } else {
                    // gpt-image-1 and successors — three supported pixel sizes
                    $aspect_ratio__candidates = [
                        '1024x1024' => 1.0,
                        '1536x1024' => 1536 / 1024,
                        '1024x1536' => 1024 / 1536
                    ];
                    $aspect_ratio__payload_key = 'size';
                    $aspect_ratio__fallback = '1024x1024';
                }
                $aspect_ratio__best = $aspect_ratio__fallback;
                $aspect_ratio__best_delta = PHP_FLOAT_MAX;
                foreach ($aspect_ratio__candidates as $label => $val) {
                    $d = abs(log($aspect_ratio__target / $val));
                    if ($d < $aspect_ratio__best_delta) {
                        $aspect_ratio__best_delta = $d;
                        $aspect_ratio__best = $label;
                    }
                }
                $payload[$aspect_ratio__payload_key] = $aspect_ratio__best;
            } elseif ($aspect_ratio === 'auto' && $this->name === 'xai') {
                $payload['aspect_ratio'] = 'auto';
            }
            // dall-e-2/3 require explicit response_format to get base64;
            // gpt-image-* returns it by default and rejects the param.
            if (str_starts_with((string) $this->model, 'dall-e')) {
                $payload['response_format'] = 'b64_json';
            }
            $headers = ['Authorization: Bearer ' . $this->api_key];
            if ($is_edit && $this->name === 'xai') {
                // xAI edit schema: JSON body with image:{url, type}.
                $img_url = null;
                $type = 'base64';
                if (is_string($input_file) && is_file($input_file)) {
                    $mime = mime_content_type($input_file);
                    if ($mime === false) {
                        $mime = 'image/png';
                    }
                    $bin = file_get_contents($input_file);
                    if ($bin === false) {
                        $this->log('⛔ image: failed to read input_file ' . $input_file);
                        return ['response' => null, 'success' => false, 'costs' => 0.0];
                    }
                    $img_url = 'data:' . $mime . ';base64,' . base64_encode($bin);
                } elseif (
                    is_string($input_file) &&
                    (str_starts_with($input_file, 'http://') || str_starts_with($input_file, 'https://'))
                ) {
                    if (!self::isPublicHttpUrl($input_file)) {
                        $this->log('⛔ image: refused private/reserved url ' . $input_file);
                        return ['response' => null, 'success' => false, 'costs' => 0.0];
                    }
                    $img_url = $input_file;
                    $type = 'image_url';
                } elseif (is_string($input_file) && str_contains($input_file, ';base64,')) {
                    $img_url = $input_file;
                } elseif (is_string($input_file)) {
                    $img_url = 'data:image/png;base64,' . $input_file;
                }
                if ($img_url === null) {
                    $this->log('⛔ image: invalid input_file for xai edit');
                    return ['response' => null, 'success' => false, 'costs' => 0.0];
                }
                $payload['image'] = ['url' => $img_url, 'type' => $type];
                $headers[] = 'Content-Type: application/json';
                $body = json_encode($payload);
            } elseif ($is_edit) {
                // OpenAI multipart edit
                $curl_file = null;
                if ($input_file instanceof \CURLFile) {
                    $curl_file = $input_file;
                } elseif (is_string($input_file) && is_file($input_file)) {
                    $curl_file = new \CURLFile($input_file);
                } else {
                    $tmp_input = tempnam(sys_get_temp_dir(), 'aih_');
                    if (
                        is_string($input_file) &&
                        (str_starts_with($input_file, 'http://') || str_starts_with($input_file, 'https://'))
                    ) {
                        if (!self::isPublicHttpUrl($input_file)) {
                            unlink($tmp_input);
                            $this->log('⛔ image: refused private/reserved url ' . $input_file);
                            return ['response' => null, 'success' => false, 'costs' => 0.0];
                        }
                        $bin = self::fetchUrlBinary($input_file, (int) ($this->timeout ?? 30));
                        if ($bin === null) {
                            unlink($tmp_input);
                            $this->log('⛔ image: failed to fetch input_file url ' . $input_file);
                            return ['response' => null, 'success' => false, 'costs' => 0.0];
                        }
                        if (file_put_contents($tmp_input, $bin) === false) {
                            unlink($tmp_input);
                            $this->log('⛔ image: failed to write fetched input to tempfile');
                            return ['response' => null, 'success' => false, 'costs' => 0.0];
                        }
                    } else {
                        $b64 =
                            is_string($input_file) && str_contains($input_file, ';base64,')
                                ? explode(';base64,', $input_file, 2)[1]
                                : (string) $input_file;
                        $decoded = base64_decode($b64, true);
                        if ($decoded === false) {
                            unlink($tmp_input);
                            $this->log('⛔ image: invalid base64 input_file');
                            return ['response' => null, 'success' => false, 'costs' => 0.0];
                        }
                        if (file_put_contents($tmp_input, $decoded) === false) {
                            unlink($tmp_input);
                            $this->log('⛔ image: failed to write decoded input to tempfile');
                            return ['response' => null, 'success' => false, 'costs' => 0.0];
                        }
                    }
                    $curl_file = new \CURLFile($tmp_input);
                }
                $payload['image'] = $curl_file;
                // multipart — pass the array directly so curl picks Content-Type
                $body = $payload;
            } else {
                $headers[] = 'Content-Type: application/json';
                $body = json_encode($payload);
            }
        }
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => $this->timeout ?? 300
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($tmp_input !== null && is_file($tmp_input)) {
            unlink($tmp_input);
        }
        if ($raw === false || $http >= 400) {
            $msg = 'image HTTP ' . $http . ' err=' . ($err ?: '') . ' body=' . substr((string) $raw, 0, 500);
            $this->log('⛔ ' . $msg);
            return ['response' => $msg, 'success' => false, 'costs' => 0.0];
        }
        $data = json_decode((string) $raw, true);
        if ($this->name === 'google') {
            // Imagen response shape: predictions[].bytesBase64Encoded
            $items = is_array($data) ? $data['predictions'] ?? [] : [];
            if (!is_array($items) || count($items) === 0) {
                return ['response' => null, 'success' => false, 'costs' => 0.0];
            }
            $b64s = [];
            foreach ($items as $it) {
                if (!empty($it['bytesBase64Encoded'])) {
                    $b64s[] = (string) $it['bytesBase64Encoded'];
                }
            }
            if ($b64s === []) {
                return ['response' => null, 'success' => false, 'costs' => 0.0];
            }
        } else {
            // OpenAI/xAI shape: data[].b64_json or data[].url (download + encode)
            $items = is_array($data) ? $data['data'] ?? [] : [];
            if (!is_array($items) || count($items) === 0) {
                return ['response' => null, 'success' => false, 'costs' => 0.0];
            }
            $download_timeout = (int) ($this->timeout ?? 30);
            $download_failed = false;
            $b64s = array_map(function ($it) use ($download_timeout, &$download_failed) {
                if (!empty($it['b64_json'])) {
                    return (string) $it['b64_json'];
                }
                if (!empty($it['url'])) {
                    $bin = self::fetchUrlBinary((string) $it['url'], $download_timeout);
                    if ($bin === null) {
                        $download_failed = true;
                        return '';
                    }
                    return base64_encode($bin);
                }
                return '';
            }, $items);
            if ($download_failed || in_array('', $b64s, true)) {
                $this->log('⛔ image: failed to download one or more result urls');
                return ['response' => null, 'success' => false, 'costs' => 0.0];
            }
        }
        if ($output_file !== null) {
            $info = pathinfo($output_file);
            $dir = $info['dirname'] ?? '.';
            $base = $info['filename'] ?? 'out';
            $ext = isset($info['extension']) ? '.' . $info['extension'] : '';
            $paths = [];
            foreach ($b64s as $i => $b64) {
                $path = $n === 1 ? $output_file : $dir . '/' . $base . '-' . ($i + 1) . $ext;
                if (file_put_contents($path, base64_decode($b64)) === false) {
                    $this->log('⛔ image: failed to write output_file ' . $path);
                    return ['response' => null, 'success' => false, 'costs' => 0.0];
                }
                $paths[] = $path;
            }
            $response = $n === 1 ? $paths[0] : $paths;
        } else {
            $response = $n === 1 ? $b64s[0] : $b64s;
        }
        $cost_per = 0.0;
        foreach ($this->models as $m) {
            if (($m['name'] ?? null) === $this->model) {
                $cost_per = (float) ($m['costs']['image'] ?? 0 ?: 0);
                break;
            }
        }
        return ['response' => $response, 'success' => true, 'costs' => $cost_per * count($b64s)];
    }

    protected function audioThis(
        ?string $prompt = null,
        ?string $voice = null,
        ?float $speed = null,
        ?string $output_file = null
    ): array {
        $endpoint = $this->url . '/audio/speech';
        $payload = ['model' => $this->model, 'input' => (string) $prompt, 'voice' => $voice ?? 'alloy'];
        if ($speed !== null) {
            $payload['speed'] = $speed;
        }
        if ($output_file !== null) {
            $ext = strtolower((string) pathinfo($output_file, PATHINFO_EXTENSION));
            if (in_array($ext, ['mp3', 'wav', 'opus', 'flac', 'aac', 'pcm'], true)) {
                $payload['response_format'] = $ext;
            }
        }
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $this->api_key, 'Content-Type: application/json'],
            CURLOPT_TIMEOUT => $this->timeout ?? 300
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($raw === false || $http >= 400) {
            $msg = 'audio HTTP ' . $http . ' err=' . ($err ?: '') . ' body=' . substr((string) $raw, 0, 500);
            $this->log('⛔ ' . $msg);
            return ['response' => $msg, 'success' => false, 'costs' => 0.0];
        }
        $costs = 0.0;
        foreach ($this->models as $m) {
            if (($m['name'] ?? null) === $this->model) {
                // costs.audio is the $/character rate. Authoritative for the
                // legacy character-billed tts-1 / tts-1-hd models. For the
                // token-billed gpt-4o-mini-tts this is an approximation only
                // (~1.3× off, since OpenAI bills text+audio tokens separately
                // at ~$0.015/min of audio).
                $costs = (float) ($m['costs']['audio'] ?? 0 ?: 0) * mb_strlen((string) $prompt);
                break;
            }
        }
        if ($output_file !== null) {
            if (file_put_contents($output_file, $raw) === false) {
                $this->log('⛔ audio: failed to write output_file ' . $output_file);
                return ['response' => null, 'success' => false, 'costs' => 0.0];
            }
            return ['response' => $output_file, 'success' => true, 'costs' => $costs];
        }
        return ['response' => base64_encode((string) $raw), 'success' => true, 'costs' => $costs];
    }

    /**
     * Auto-compact the current session if it is about to exceed the model's
     * context window. Opt-in via the $auto_compact constructor flag. Safe to
     * call on any provider — works on the provider-agnostic self::$sessions
     * array and uses bringPromptInFormat() to produce the summary message in
     * the host provider's native shape.
     *
     * Strategy (Running Summary):
     *   - keep first $keep_head messages verbatim (prepended system prompts /
     *     skills)
     *   - keep last $keep_tail messages verbatim (recent turns)
     *   - replace everything in between with one summary message that extends
     *     the previous running summary
     *
     * Summary is produced by a nested, stripped-down aihelper instance on the
     * same provider/model (no tools, no streaming, no nested compact,
     * temperature 0 for determinism). The final summary text is persisted to
     * sys_get_temp_dir() + "/aihelper-cache/<session_id>.txt" so subsequent
     * aihelper instances with the same session_id pick up where we left off.
     *
     * Everything lives in this one function by design — the transcript
     * builder, token estimator and nested summarizer are small enough to be
     * inlined and benefit from keeping the whole compaction flow in a single
     * readable place.
     */
    /**
     * Strip inline base64 image data URIs from a session payload before token
     * estimation. Walks the structure recursively and replaces any
     * `data:image/...;base64,…` URL with a tiny placeholder, while counting
     * the strip-outs in `&$count`. Used by autoCompactSession() so the
     * char-based token heuristic does not blow up on image-bearing turns.
     */
    private static function stripInlineImagesForTokenCount(mixed $node, int &$count): mixed
    {
        if (is_string($node)) {
            if (str_starts_with($node, 'data:image/') && str_contains($node, ';base64,')) {
                $count++;
                return 'data:image/*;base64,STRIPPED';
            }
            return $node;
        }
        if (is_array($node)) {
            $out = [];
            foreach ($node as $k => $v) {
                $out[$k] = self::stripInlineImagesForTokenCount($v, $count);
            }
            return $out;
        }
        if (is_object($node)) {
            $out = clone $node;
            foreach (get_object_vars($out) as $k => $v) {
                $out->$k = self::stripInlineImagesForTokenCount($v, $count);
            }
            return $out;
        }
        return $node;
    }

    /**
     * Replace inline image content blocks across all known provider shapes
     * with a tiny text stub. Used after a successful compaction to evict
     * base64-heavy attachments from `head` and `tail` so subsequent compacts
     * see a realistic baseline. The summary call that ran beforehand already
     * carried the model's interpretation of the image forward in prose.
     *
     * Recognises:
     *  - OpenAI: ["type" => "image_url", "image_url" => ["url" => "data:..."]]
     *  - Anthropic: ["type" => "image", "source" => ["type" => "base64", "data" => "..."]]
     *  - Google: ["inline_data" => ["mime_type" => "image/...", "data" => "..."]]
     *  - bare data URIs anywhere in the tree (fallback)
     */
    private static function replaceInlineImagesWithStubs(mixed $node, int &$count): mixed
    {
        $stub_text =
            '[Bild aus früherem Turn entfernt während Kontext-Kompression — Inhalt in Zusammenfassung erhalten]';

        if (is_array($node)) {
            // detect known image-container shapes and swap the whole block out
            $is_openai_image = isset($node['type']) && $node['type'] === 'image_url' && isset($node['image_url']);
            $is_anthropic_image = isset($node['type']) && $node['type'] === 'image' && isset($node['source']);
            $is_google_image = isset($node['inline_data']) && is_array($node['inline_data']);
            if ($is_openai_image || $is_anthropic_image) {
                $count++;
                return ['type' => 'text', 'text' => $stub_text];
            }
            if ($is_google_image) {
                $count++;
                return ['text' => $stub_text];
            }
            $out = [];
            foreach ($node as $k => $v) {
                $out[$k] = self::replaceInlineImagesWithStubs($v, $count);
            }
            return $out;
        }
        if (is_object($node)) {
            $arr = (array) $node;
            $is_openai_image = ($arr['type'] ?? null) === 'image_url' && isset($arr['image_url']);
            $is_anthropic_image = ($arr['type'] ?? null) === 'image' && isset($arr['source']);
            $is_google_image = isset($arr['inline_data']);
            if ($is_openai_image || $is_anthropic_image) {
                $count++;
                return (object) ['type' => 'text', 'text' => $stub_text];
            }
            if ($is_google_image) {
                $count++;
                return (object) ['text' => $stub_text];
            }
            $out = clone $node;
            foreach (get_object_vars($out) as $k => $v) {
                $out->$k = self::replaceInlineImagesWithStubs($v, $count);
            }
            return $out;
        }
        if (is_string($node) && str_starts_with($node, 'data:image/') && str_contains($node, ';base64,')) {
            $count++;
            return $stub_text;
        }
        return $node;
    }

    public function autoCompactSession(): void
    {
        // ---- tunables (inlined by design — callers only flip auto_compact) -
        $threshold = 0.65; // trigger when tokens exceed this fraction of ctx —
        // earlier than 0.7 to leave headroom for the
        // summarizer call itself (system + transcript prompt)
        // and the next assistant turn that follows compaction
        $keep_head = 10; // first N messages (prepended prompts + early tool-use
        // demonstrations) stay verbatim — important so the
        // model retains a clear example of the structured
        // tool_calls format and does not regress to emitting
        // tool_calls as plain-text JSON after compaction
        $keep_tail = 6; // last N messages stay verbatim (recent exchange).
        // sized to fit at least two complete tool roundtrips
        // (user → assistant.tool_calls → tool → assistant.text)
        // so a recent tool result never gets summarised away
        // before the assistant answered on top of it
        $chars_per_token = 3; // char→token estimator — tool-heavy sessions are
        // dominated by JSON (args, results) where 1 token
        // ≈ 3 chars; the prior 4 underestimated usage and
        // delayed compaction past safe headroom

        // ---- guards --------------------------------------------------------
        if ($this->auto_compact !== true) {
            return;
        }
        if (empty($this->session_id) || !isset(self::$sessions[$this->session_id])) {
            return;
        }
        $session = self::$sessions[$this->session_id];
        if (!is_array($session)) {
            return;
        }

        // ---- rehydrate from disk snapshot (if present) --------------------
        // a previous process compacted this session and wrote a JSON snapshot
        // (head + summary + tail). on a fresh process the caller passes the
        // *full* history again — we replace its already-compacted prefix with
        // the snapshot, keeping only the messages added after the snapshot
        // anchor. this avoids re-running a (slow, expensive) compact on every
        // worker pickup. only runs once per process — auto_compact_summary !==
        // null after rehydration acts as the run-once flag.
        if (
            $this->auto_compact_summary === null &&
            $this->auto_compact_cache !== null &&
            is_file($this->auto_compact_cache) &&
            is_readable($this->auto_compact_cache)
        ) {
            $cache_raw = file_get_contents($this->auto_compact_cache);
            if (is_string($cache_raw) && $cache_raw !== '') {
                $cache_data = json_decode($cache_raw, true);
                if (
                    is_array($cache_data) &&
                    isset($cache_data['summary']) &&
                    isset($cache_data['session']) &&
                    is_array($cache_data['session'])
                ) {
                    // new JSON snapshot format
                    $snapshot = $cache_data['session'];
                    if (count($snapshot) > 0) {
                        // shape-agnostic message hash so we can locate the
                        // snapshot's last message inside the freshly-loaded
                        // history. role + serialised content/tool_calls/parts
                        // is unique enough across providers (openai/anthropic
                        // /google) without depending on db ids.
                        $messageHash = function (array $m): string {
                            return md5(
                                ($m['role'] ?? '') .
                                    '|' .
                                    json_encode($m['content'] ?? null) .
                                    '|' .
                                    json_encode($m['tool_calls'] ?? null) .
                                    '|' .
                                    json_encode($m['parts'] ?? null) .
                                    '|' .
                                    json_encode($m['tool_call_id'] ?? null)
                            );
                        };
                        $anchor = is_array($snapshot[count($snapshot) - 1])
                            ? $snapshot[count($snapshot) - 1]
                            : (array) $snapshot[count($snapshot) - 1];
                        $anchor_role = $anchor['role'] ?? '';
                        $anchor_hash = $messageHash($anchor);
                        // walk the freshly-loaded history; latest match wins
                        // (defensive against duplicate "OK" assistants etc.)
                        $anchor_idx = null;
                        foreach ($session as $i => $msg) {
                            $m = is_array($msg) ? $msg : (array) $msg;
                            if (($m['role'] ?? '') === $anchor_role && $messageHash($m) === $anchor_hash) {
                                $anchor_idx = $i;
                            }
                        }
                        if ($anchor_idx !== null) {
                            // splice: snapshot replaces everything up to (and
                            // including) the anchor; everything after the
                            // anchor in the fresh history are messages that
                            // arrived since the last compact and must be kept.
                            $tail_after_anchor = array_slice($session, $anchor_idx + 1);
                            self::$sessions[$this->session_id] = array_merge($snapshot, $tail_after_anchor);
                            $session = self::$sessions[$this->session_id];
                            $this->auto_compact_summary = (string) $cache_data['summary'];
                            $this->log(
                                '🔄 auto_compact: rehydrated snapshot — ' .
                                    count($snapshot) .
                                    ' compacted msgs + ' .
                                    count($tail_after_anchor) .
                                    ' new'
                            );
                        }
                        // anchor not found → snapshot is stale (db edited,
                        // session_id reused, etc). fall through; the regular
                        // compact path below will re-establish a fresh snapshot.
                    }
                } else {
                    // legacy plain-text format (pre-snapshot) — only the
                    // running summary, no session payload. keep it so the
                    // summarizer can continue/extend it on the next compact.
                    $this->auto_compact_summary = $cache_raw;
                }
            }
        }

        if (count($session) < $keep_head + $keep_tail + 1) {
            return; // nothing to compact yet
        }

        // ---- threshold check ----------------------------------------------
        // base64 image payloads make the JSON length a *terrible* token proxy:
        // a 370 KB png is ~500 KB base64 → char-heuristic claims ~167k tokens,
        // while the provider actually bills 5–15k tokens for the same image.
        // strip the inline data URIs before measuring, then add a fixed cost
        // per image so the heuristic still reflects their presence without
        // overshooting by 10–30×.
        $image_token_cost = 1500; // conservative upper bound for high-detail
        $images_in_session = 0;
        $session_for_count = self::stripInlineImagesForTokenCount($session, $images_in_session);
        $session_json = json_encode($session_for_count);
        $current_tokens = is_string($session_json) ? (int) ceil(strlen($session_json) / $chars_per_token) : 0;
        $current_tokens += $images_in_session * $image_token_cost;
        $context_length = $this->getContextLengthForModel();
        $threshold_tokens = (int) ($context_length * $threshold);
        if ($current_tokens <= $threshold_tokens) {
            return; // still within budget
        }

        $this->log(
            '🗜️ auto_compact: ' .
                $current_tokens .
                ' > ' .
                $threshold_tokens .
                ' tokens (ctx ' .
                $context_length .
                '), compacting ' .
                (count($session) - $keep_head - $keep_tail) .
                ' middle messages'
        );

        // ---- split head / middle / tail -----------------------------------
        // Tool-call boundary safety: a `tool` message is only valid when its
        // immediately preceding entry is an `assistant` with `tool_calls`.
        // Strict Jinja templates (e.g. MiniMax-M2.7) raise a Jinja exception
        // when this invariant is violated. Naive slicing at fixed offsets can
        // strand a tool message at the start of `tail` (its parent assistant
        // got compacted into the summary) or at the end of `head` (its
        // assistant follows in middle). Walk both boundaries until they land
        // on a safe role.
        $head_end = $keep_head;
        $tail_start = count($session) - $keep_tail;

        // grow head forward so it doesn't END with an assistant whose tool_calls
        // result lives in middle — keep the assistant + tool result paired.
        // also: if head currently ends WITH a tool message, that's already
        // matched on its left (preceding assistant tool_calls in head) and ok.
        while ($head_end < $tail_start) {
            $last = is_array($session[$head_end - 1] ?? null)
                ? $session[$head_end - 1]
                : (array) ($session[$head_end - 1] ?? []);
            $next = is_array($session[$head_end] ?? null) ? $session[$head_end] : (array) ($session[$head_end] ?? []);
            $last_has_tool_calls = ($last['role'] ?? '') === 'assistant' && !empty($last['tool_calls']);
            $next_is_tool = ($next['role'] ?? '') === 'tool';
            if ($last_has_tool_calls && $next_is_tool) {
                $head_end++;
                continue;
            }
            break;
        }

        // grow tail backward so it doesn't START with a `tool` message — the
        // matching assistant tool_calls would be in the summary and template
        // would orphan it. shift left until tail starts on user/assistant.
        while ($tail_start > $head_end) {
            $first = is_array($session[$tail_start] ?? null)
                ? $session[$tail_start]
                : (array) ($session[$tail_start] ?? []);
            if (($first['role'] ?? '') === 'tool') {
                $tail_start--;
                continue;
            }
            // also: if tail starts with an assistant that has only `content`
            // referring to a prior tool result (e.g. post-tool-turn), that's
            // ok — assistant text after a tool result is a legal end-of-turn.
            // we only correct the tool-as-first case above.
            break;
        }

        $head = array_slice($session, 0, $head_end);
        $middle = array_slice($session, $head_end, $tail_start - $head_end);
        $tail = array_slice($session, $tail_start);

        // ---- flatten middle to plain-text transcript ----------------------
        // recursive extractor (closure so we don't need a named helper) —
        // handles all provider shapes: OpenAI blocks, Anthropic content
        // blocks, Google parts. pulls strings from any `text`/`content`/
        // `parts` key and numeric indexes, ignoring meta keys like `type`.
        $extract = function (mixed $node) use (&$extract): string {
            if ($node === null) {
                return '';
            }
            if (is_string($node)) {
                return $node;
            }
            if (is_object($node)) {
                $node = (array) $node;
            }
            if (!is_array($node)) {
                return '';
            }
            $parts = [];
            foreach ($node as $k => $v) {
                if (in_array($k, ['text', 'content', 'parts'], true) || is_int($k)) {
                    $parts[] = $extract($v);
                }
            }
            return trim(implode(' ', array_filter($parts, fn($p) => $p !== '')));
        };
        $prior_summary_marker = '[Zusammenfassung des bisherigen Verlaufs';
        $transcript_lines = [];
        foreach ($middle as $msg) {
            $msg_arr = is_array($msg) ? $msg : (array) $msg;
            $role = $msg_arr['role'] ?? 'unknown';
            $text = $extract($msg_arr['content'] ?? ($msg_arr['parts'] ?? null));
            if ($text !== '' && str_contains($text, $prior_summary_marker)) {
                continue;
            }
            if ($text === '') {
                // tool-call envelope without text — preserve tool name/args so
                // the summarizer knows what happened
                if (!empty($msg_arr['tool_calls'])) {
                    $text = '[tool_calls] ' . substr((string) json_encode($msg_arr['tool_calls']), 0, 500);
                } elseif (!empty($msg_arr['function_call'])) {
                    $text = '[function_call] ' . substr((string) json_encode($msg_arr['function_call']), 0, 500);
                }
            }
            if ($text !== '') {
                $transcript_lines[] = strtoupper((string) $role) . ': ' . $text;
            }
        }
        $transcript = implode("\n\n", $transcript_lines);

        // ---- nested summarizer call ---------------------------------------
        $system_prompt =
            'Du bist ein Kontext-Komprimierer. Fasse den folgenden Gesprächsverlauf strukturiert zusammen, ' .
            'sodass ein nachfolgender Assistent ohne den vollen Verlauf weiterarbeiten kann.' .
            "\n\n" .
            'Bewahre unbedingt:' .
            "\n" .
            '- Nutzer-Ziel und offene Fragen' .
            "\n" .
            '- Bereits ausgeführte Tool-Aufrufe mit ihren Argumenten und Ergebnis-Kernfakten (z.B. "fetch_mails mit limit=10 ergab 10 Mails, Betreffe: ...")' .
            "\n" .
            '- Vom Nutzer geäußerte Präferenzen und Entscheidungen' .
            "\n" .
            '- Wichtige Werte (IDs, Namen, Datumsangaben) die im weiteren Verlauf referenziert werden könnten' .
            "\n\n" .
            'Format: Markdown mit Abschnitten "Ziel", "Ausgeführte Aktionen", "Schlüsselwerte", "Offene Punkte".' .
            "\n" .
            'Kürze aggressiv. Keine Prosa-Einleitung, kein "Zusammenfassung:" am Anfang.';
        $user_prompt = '';
        if ($this->auto_compact_summary !== null && $this->auto_compact_summary !== '') {
            $user_prompt .=
                "Bisherige Zusammenfassung (weiterführen und ergänzen):\n\n" .
                $this->auto_compact_summary .
                "\n\n----\n\n";
        }
        $user_prompt .= "Neu hinzugekommener Verlauf (diesen integrieren):\n\n" . $transcript;

        $new_summary = null;
        try {
            // fresh session per summarizer run — a stable suffix would collide
            // across successive compactions within the same process because
            // aihelper's constructor keeps stale data when history is empty
            // (__::x([]) === false, so history: [] does not overwrite).
            $summarizer = self::create(
                provider: $this->name,
                model: $this->model,
                temperature: 0.0,
                api_key: $this->api_key,
                url: $this->url,
                log: $this->log,
                max_tries: 1,
                session_id: $this->session_id . '::compact::' . uniqid('', true),
                history: [],
                stream: false,
                enable_thinking: false,
                auto_compact: false
            );
            if ($summarizer !== null) {
                $summarizer->prependPromptToSession($system_prompt);
                $result = $summarizer->ask($user_prompt);
                // drop ephemeral summarizer session so long-running worker
                // processes don't accumulate dead compact-sessions
                $sid = $summarizer->getSessionId();
                if ($sid !== null) {
                    unset(self::$sessions[$sid]);
                }
                if (is_array($result) && ($result['success'] ?? false) === true) {
                    $text = is_string($result['response'] ?? null) ? trim($result['response']) : '';
                    if ($text !== '') {
                        $new_summary = $text;
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->log('⚠️ auto_compact summarizer error: ' . $e->getMessage());
        }

        // ---- apply result to session --------------------------------------
        if ($new_summary === null) {
            // summarizer failed — drop middle without summary rather than
            // blocking the user. logged above so we can debug later.
            $this->log('⚠️ auto_compact: summarizer returned empty, dropping middle without summary');
            self::$sessions[$this->session_id] = array_merge($head, $tail);
            return;
        }
        $this->auto_compact_summary = $new_summary;
        $summary_banner =
            "[Zusammenfassung des bisherigen Verlaufs — die zwischen den initialen Instruktionen und den letzten Turns liegenden Nachrichten wurden komprimiert]\n\n" .
            $new_summary;
        $summary_message = $this->bringPromptInFormat($summary_banner);
        // strip inline image attachments from head + tail once the summary has
        // captured what the model extracted from them. otherwise a single big
        // image in the first user turn sits unreachable in `head` forever and
        // every subsequent compact runs against the same bloated baseline
        // (62-loop observed in the wild). the summary always carries forward
        // whatever the assistant already pulled out of the image.
        $images_removed = 0;
        $head = self::replaceInlineImagesWithStubs($head, $images_removed);
        $tail = self::replaceInlineImagesWithStubs($tail, $images_removed);
        if ($images_removed > 0) {
            $this->log('🖼️ auto_compact: stripped ' . $images_removed . ' image attachment(s) from head/tail');
        }
        self::$sessions[$this->session_id] = array_merge($head, [$summary_message], $tail);
        // persist a full JSON snapshot (summary + compacted session). on the
        // next process boot the rehydration block at the top of this function
        // splices this snapshot in front of any new messages from the freshly
        // loaded history, so the (slow) compact pass runs only when the
        // threshold is actually re-breached — not on every worker pickup.
        if ($this->auto_compact_cache !== null) {
            $cache_payload = json_encode(
                [
                    'summary' => $new_summary,
                    'session' => self::$sessions[$this->session_id]
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            if ($cache_payload !== false && file_put_contents($this->auto_compact_cache, $cache_payload) === false) {
                $this->log('⚠️ auto_compact: failed to persist cache to ' . $this->auto_compact_cache);
            }
        }
    }

    /**
     * Unwrap weak-tool-caller envelope mimicry.
     *
     * Some open-weight models (observed: gemma-4-31b-it) over-mimic the openai
     * tool-call schema example shown in the system prompt and emit the FULL
     * wrapper {"name": "<tool>", "arguments": {...}} INSIDE the arguments
     * payload, instead of just the inner args object. This helper detects the
     * wrapped pattern and unwraps it once. It is conservative: it only unwraps
     * when the outer args are exactly {name: <string>, arguments: <object>}
     * AND the inner name matches the actual tool name (so legitimate tools
     * with literal "name"/"arguments" parameters are not mis-unwrapped).
     */
    protected function unwrapMimickedToolArgs(string $tool_name, array $args): array
    {
        if (count($args) !== 2) {
            return $args;
        }
        if (!array_key_exists('name', $args) || !array_key_exists('arguments', $args)) {
            return $args;
        }
        if (!is_string($args['name']) || !is_array($args['arguments'])) {
            return $args;
        }
        if ($args['name'] !== $tool_name) {
            return $args;
        }
        $this->log('unwrapped envelope-mimicked tool args for "' . $tool_name . '"', 'local tool loop');
        return $args['arguments'];
    }

    protected function runLocalToolLoop(array $return): array
    {
        $is_anthropic = in_array($this->name, ['anthropic', 'xai', 'deepseek'], true);
        $is_google = $this->name === 'google';
        $is_chat_completions = in_array($this->name, ['openrouter', 'llamacpp', 'nvidia', 'codex'], true);
        $max_tool_rounds = 200;
        // loop-guard: track consecutive identical (name, args) tool calls and
        // short-circuit after the threshold so that weak tool-callers (observed
        // with gemma-4) don't spin on a "verification" tool until $max_tool_rounds.
        $repeat_signature = null;
        $repeat_count = 0;
        $repeat_threshold = 3;
        // cumulative loop-guard: count how often each (name, args) signature
        // has appeared across this whole ask() call AND store a hash of every
        // result it produced. when a signature has been seen >= cumulative
        // threshold times AND every single one of those calls produced a
        // byte-identical result, the model is clearly not making progress —
        // this catches the alternating-pattern loops the consecutive guard
        // above misses (e.g. click→snapshot→click→snapshot where the snapshot
        // keeps returning the same page state because the click doesn't
        // actually navigate). safe for long browser sessions: each new page
        // yields a different snapshot result, so the *unique*-results count
        // grows and the cumulative guard never trips.
        $signature_results = [];
        $cumulative_threshold = 5;
        while ($max_tool_rounds > 0) {
            // extract pending tool calls from session
            $tool_calls = [];
            $session = self::$sessions[$this->session_id] ?? [];
            if ($is_google) {
                // google: functionCall parts inside last model message
                $last = !empty($session) ? end($session) : null;
                if ($last !== null && isset($last['role']) && $last['role'] === 'model' && isset($last['parts'])) {
                    foreach ($last['parts'] as $part) {
                        $fc = is_object($part) ? $part->functionCall ?? null : $part['functionCall'] ?? null;
                        if ($fc !== null) {
                            $name = is_object($fc) ? $fc->name : $fc['name'];
                            $args = is_object($fc) ? (array) ($fc->args ?? []) : $fc['args'] ?? [];
                            $args = $this->unwrapMimickedToolArgs($name, $args);
                            $tool_calls[] = [
                                'id' => $name,
                                'name' => $name,
                                'arguments' => $args
                            ];
                        }
                    }
                }
            } elseif ($is_anthropic) {
                // anthropic: tool_use blocks inside last assistant message content
                $last = !empty($session) ? end($session) : null;
                if (
                    $last !== null &&
                    isset($last['role']) &&
                    $last['role'] === 'assistant' &&
                    isset($last['content']) &&
                    is_array($last['content'])
                ) {
                    foreach ($last['content'] as $block) {
                        $type = is_object($block) ? $block->type ?? null : $block['type'] ?? null;
                        if ($type === 'tool_use') {
                            $name = is_object($block) ? $block->name : $block['name'];
                            $args = is_object($block) ? (array) ($block->input ?? []) : $block['input'] ?? [];
                            $args = $this->unwrapMimickedToolArgs($name, $args);
                            $tool_calls[] = [
                                'id' => is_object($block) ? $block->id : $block['id'],
                                'name' => $name,
                                'arguments' => $args
                            ];
                        }
                    }
                }
            } elseif ($is_chat_completions) {
                // chat completions: tool_calls inside last assistant message
                $last = !empty($session) ? end($session) : null;
                if (
                    $last !== null &&
                    isset($last['role']) &&
                    $last['role'] === 'assistant' &&
                    isset($last['tool_calls']) &&
                    is_array($last['tool_calls'])
                ) {
                    foreach ($last['tool_calls'] as $tc) {
                        $name = $tc['function']['name'] ?? '';
                        $args = json_decode($tc['function']['arguments'] ?? '{}', true) ?: [];
                        // unwrap envelope-mimicking args produced by some weak tool-callers
                        // (observed with gemma-4): the model emits the FULL openai tool-call
                        // wrapper {"name":"...","arguments":{...}} INSIDE the arguments string
                        // instead of just the inner args object. detect and unwrap.
                        $args = $this->unwrapMimickedToolArgs($name, $args);
                        $tool_calls[] = [
                            'id' => $tc['id'] ?? '',
                            'name' => $name,
                            'arguments' => $args
                        ];
                    }
                }
            } else {
                // responses api: function_call items as top-level session entries
                for ($i = count($session) - 1; $i >= 0; $i--) {
                    if (isset($session[$i]['type']) && $session[$i]['type'] === 'function_call') {
                        $name = $session[$i]['name'];
                        $args = json_decode($session[$i]['arguments'], true) ?? [];
                        $args = $this->unwrapMimickedToolArgs($name, $args);
                        $tool_calls[] = [
                            'id' => $session[$i]['call_id'],
                            'name' => $name,
                            'arguments' => $args
                        ];
                    } else {
                        break;
                    }
                }
            }
            if (empty($tool_calls)) {
                break;
            }
            $this->log(count($tool_calls) . ' tool call(s)', 'local tool loop');
            $tool_results = [];
            foreach ($tool_calls as $tc) {
                // loop-guard: if the same (name, args) is emitted N times in a row,
                // refuse to execute and return a forceful stop-instruction to the model.
                $signature = $tc['name'] . '|' . json_encode($tc['arguments'], JSON_UNESCAPED_UNICODE);
                if ($signature === $repeat_signature) {
                    $repeat_count++;
                } else {
                    $repeat_signature = $signature;
                    $repeat_count = 1;
                }
                if ($repeat_count > $repeat_threshold) {
                    $this->log(
                        'loop-guard tripped for "' . $tc['name'] . '" after ' . $repeat_threshold . ' identical calls',
                        'local tool loop'
                    );
                    $tool_results[] = [
                        'id' => $tc['id'],
                        'name' => $tc['name'],
                        'output' =>
                            'Error: this tool was already called ' .
                            $repeat_threshold .
                            ' times with identical arguments and produced the same result each time. ' .
                            'STOP repeating this call. The previous result is final — proceed to the next step in the task, or finalize your answer.'
                    ];
                    continue;
                }
                // cumulative guard: identical args + identical results
                // accumulating without intermediate progress
                $prev_hashes = $signature_results[$signature] ?? [];
                if (count($prev_hashes) >= $cumulative_threshold && count(array_unique($prev_hashes)) === 1) {
                    $this->log(
                        'loop-guard tripped (cumulative): "' .
                            $tc['name'] .
                            '" called ' .
                            count($prev_hashes) .
                            ' times with identical args + identical results',
                        'local tool loop'
                    );
                    $tool_results[] = [
                        'id' => $tc['id'],
                        'name' => $tc['name'],
                        'output' =>
                            'Error: this tool has already been called ' .
                            count($prev_hashes) .
                            ' times with identical arguments AND every call returned a byte-identical result. ' .
                            'The page/system state is not changing — continuing with this approach will keep producing the same result. ' .
                            'CHANGE STRATEGY: try a different tool, different parameters, or finalize your answer.'
                    ];
                    continue;
                }
                if (!isset($this->mcp_servers_tools_map[$tc['name']])) {
                    $this->log('unknown tool: ' . $tc['name'], 'local tool loop');
                    $output = 'Error: unknown tool "' . $tc['name'] . '"';
                } else {
                    $server = $this->mcp_servers_tools_map[$tc['name']];
                    $this->log(
                        $tc['name'] . '(' . json_encode($tc['arguments'], JSON_UNESCAPED_UNICODE) . ')',
                        'local tool call'
                    );
                    if ($this->stream === true) {
                        echo ": keepalive\n\n";
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    }
                    $result = self::callMcpTool(
                        name: $tc['name'],
                        args: $tc['arguments'],
                        url: $server['url'],
                        authorization_token: $server['authorization_token']
                    );
                    if ($result === null) {
                        $output = 'Error: tool call failed (no response from MCP server)';
                    } elseif (isset($result['result']['content']) && is_array($result['result']['content'])) {
                        $parts = [];
                        foreach ($result['result']['content'] as $item) {
                            $parts[] = $item['text'] ?? json_encode($item, JSON_UNESCAPED_UNICODE);
                        }
                        $output = implode("\n", $parts);
                    } else {
                        $output = json_encode($result, JSON_UNESCAPED_UNICODE);
                    }
                    // truncate very large tool outputs to prevent context overflow
                    $max_output_chars = 100000;
                    if (mb_strlen($output) > $max_output_chars) {
                        $original_len = mb_strlen($output);
                        $trimmed = $output;
                        // try JSON-aware truncation
                        $decoded = json_decode(trim($output), true);
                        if ($decoded !== null) {
                            $truncate_json = function ($data, int $max_str = 500, int $max_arr = 5) use (
                                &$truncate_json
                            ) {
                                if (is_array($data) && array_is_list($data)) {
                                    $sliced = array_map(
                                        fn($v) => $truncate_json($v, $max_str, $max_arr),
                                        array_slice($data, 0, $max_arr)
                                    );
                                    if (count($data) > $max_arr) {
                                        $sliced[] =
                                            '[... ' .
                                            (count($data) - $max_arr) .
                                            ' more items, ' .
                                            count($data) .
                                            ' total]';
                                    }
                                    return $sliced;
                                }
                                if (is_array($data)) {
                                    return array_map(fn($v) => $truncate_json($v, $max_str, $max_arr), $data);
                                }
                                if (is_string($data) && mb_strlen($data) > $max_str) {
                                    return mb_substr($data, 0, $max_str) . '... [' . mb_strlen($data) . ' chars]';
                                }
                                return $data;
                            };
                            $trimmed = json_encode(
                                $truncate_json($decoded),
                                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
                            );
                        } else {
                            $trimmed = mb_substr($output, 0, $max_output_chars);
                        }
                        $output =
                            $trimmed . "\n\n[... truncated from $original_len to " . mb_strlen($trimmed) . ' chars]';
                    }
                    $this->log(mb_substr($output, 0, 200), 'local tool result');
                }
                // record the hash of this round's output for the cumulative
                // loop-guard. this happens unconditionally (also for unknown-
                // tool errors) so a model that keeps calling a non-existent
                // tool also gets caught after $cumulative_threshold attempts.
                $signature_results[$signature][] = md5((string) $output);
                $tool_results[] = ['id' => $tc['id'], 'name' => $tc['name'], 'output' => $output];
            }
            // append tool results in provider-specific format
            if ($is_google) {
                $response_parts = [];
                foreach ($tool_results as $tr) {
                    $response_parts[] = [
                        'functionResponse' => [
                            'name' => $tr['name'],
                            'response' => ['result' => $tr['output']]
                        ]
                    ];
                }
                self::$sessions[$this->session_id][] = [
                    'role' => 'user',
                    'parts' => $response_parts
                ];
            } elseif ($is_anthropic) {
                $result_blocks = [];
                foreach ($tool_results as $tr) {
                    $result_blocks[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $tr['id'],
                        'content' => $tr['output']
                    ];
                }
                self::$sessions[$this->session_id][] = [
                    'role' => 'user',
                    'content' => $result_blocks
                ];
            } elseif ($is_chat_completions) {
                foreach ($tool_results as $tr) {
                    self::$sessions[$this->session_id][] = [
                        'role' => 'tool',
                        'tool_call_id' => $tr['id'],
                        'content' => $tr['output']
                    ];
                }
            } else {
                foreach ($tool_results as $tr) {
                    self::$sessions[$this->session_id][] = [
                        'type' => 'function_call_output',
                        'call_id' => $tr['id'],
                        'output' => $tr['output']
                    ];
                }
            }
            // truncate older tool outputs in session to prevent context overflow
            $this->truncateOlderToolOutputs();

            // Retry the follow-up LLM call honoring max_tries so transient
            // empty responses (e.g. llama.cpp slot contention under parallel
            // load) get a chance to recover. Mirrors the retry loop in ask().
            // add_prompt_to_session stays false — there is no user prompt in
            // tool-loop follow-ups — so the simple "only retry if failed"
            // condition is sufficient without the first-attempt guard that
            // ask() needs.
            // also re-run auto-compact here: tool results can balloon the
            // session beyond the model's context length between iterations,
            // and ask()'s single up-front call cannot foresee that.
            $this->autoCompactSession();
            $return['success'] = false;
            $max_tries = $this->max_tries;
            while ($return['success'] === false && $max_tries > 0) {
                if ($max_tries < $this->max_tries) {
                    $attempt = $this->max_tries - $max_tries;
                    $backoff_s = 15 * (int) pow(2, $attempt - 1);
                    $this->log('⚠️ tries left: ' . $max_tries . ' — backoff ' . $backoff_s . 's');
                    sleep($backoff_s);
                }
                try {
                    $return = $this->askThis(
                        prompt: null,
                        files: null,
                        add_prompt_to_session: false,
                        prev_output_text: null,
                        prev_costs: $return['costs']
                    );
                } catch (\RuntimeException $e) {
                    if (str_starts_with($e->getMessage(), 'whitespace runaway')) {
                        $this->log(
                            '⚠️ caught whitespace runaway (tool-loop) — converting to retry: ' . $e->getMessage()
                        );
                        $return = [
                            'response' => 'whitespace runaway detected, retrying',
                            'success' => false,
                            'costs' => $return['costs'] ?? 0.0
                        ];
                    } else {
                        throw $e;
                    }
                }
                $this->log($return, 'local tool loop return');
                $max_tries--;
            }
            $max_tool_rounds--;
        }
        return $return;
    }

    protected function truncateOlderToolOutputs(int $max_chars = 25000): void
    {
        $session = &self::$sessions[$this->session_id];
        // find tool output entries and truncate all except the last batch
        // (the last batch was just added and should remain intact)
        $is_tool_output = function (mixed $e): bool {
            if (!is_array($e)) {
                return false;
            }
            if (isset($e['type']) && $e['type'] === 'function_call_output') {
                return true;
            }
            if (isset($e['role']) && $e['role'] === 'tool') {
                return true;
            }
            if (isset($e['role']) && $e['role'] === 'user') {
                foreach ($e['content'] ?? [] as $b) {
                    if ((is_object($b) ? $b->type ?? null : $b['type'] ?? null) === 'tool_result') {
                        return true;
                    }
                }
                foreach ($e['parts'] ?? [] as $p) {
                    if (is_object($p) ? isset($p->functionResponse) : isset($p['functionResponse'])) {
                        return true;
                    }
                }
            }
            return false;
        };
        $last_tool_output_idx = -1;
        for ($i = count($session) - 1; $i >= 0; $i--) {
            if ($is_tool_output($session[$i])) {
                $last_tool_output_idx = $i;
                break;
            }
        }
        for ($i = 0; $i < count($session); $i++) {
            if ($i >= $last_tool_output_idx) {
                break;
            }
            $entry = &$session[$i];
            if (!is_array($entry)) {
                continue;
            }
            // openai responses api: function_call_output
            if (
                isset($entry['type']) &&
                $entry['type'] === 'function_call_output' &&
                isset($entry['output']) &&
                is_string($entry['output'])
            ) {
                if (mb_strlen($entry['output']) > $max_chars) {
                    $entry['output'] = mb_substr($entry['output'], 0, $max_chars) . "\n[... truncated ...]";
                }
            }
            // chat completions: role=tool
            if (
                isset($entry['role']) &&
                $entry['role'] === 'tool' &&
                isset($entry['content']) &&
                is_string($entry['content'])
            ) {
                if (mb_strlen($entry['content']) > $max_chars) {
                    $entry['content'] = mb_substr($entry['content'], 0, $max_chars) . "\n[... truncated ...]";
                }
            }
            // anthropic: role=user with tool_result blocks
            if (
                isset($entry['role']) &&
                $entry['role'] === 'user' &&
                isset($entry['content']) &&
                is_array($entry['content'])
            ) {
                foreach ($entry['content'] as &$block) {
                    $type = is_object($block) ? $block->type ?? null : $block['type'] ?? null;
                    if ($type === 'tool_result') {
                        $content = is_object($block) ? $block->content ?? null : $block['content'] ?? null;
                        if (is_string($content) && mb_strlen($content) > $max_chars) {
                            $truncated = mb_substr($content, 0, $max_chars) . "\n[... truncated ...]";
                            if (is_object($block)) {
                                $block->content = $truncated;
                            } else {
                                $block['content'] = $truncated;
                            }
                        }
                    }
                }
            }
            // google: role=user with functionResponse parts
            if (
                isset($entry['role']) &&
                $entry['role'] === 'user' &&
                isset($entry['parts']) &&
                is_array($entry['parts'])
            ) {
                foreach ($entry['parts'] as &$part) {
                    $fr = is_object($part) ? $part->functionResponse ?? null : $part['functionResponse'] ?? null;
                    if ($fr !== null) {
                        $result = is_object($fr) ? $fr->response->result ?? null : $fr['response']['result'] ?? null;
                        if (is_string($result) && mb_strlen($result) > $max_chars) {
                            $truncated = mb_substr($result, 0, $max_chars) . "\n[... truncated ...]";
                            if (is_object($fr)) {
                                $fr->response->result = $truncated;
                            } else {
                                $part['functionResponse']['response']['result'] = $truncated;
                            }
                        }
                    }
                }
            }
        }
    }

    protected function buildLocalToolsArgs(
        string $schema_key = 'parameters',
        bool $wrap_function_type = false,
        array $strip_schema_keys = []
    ): array {
        if (empty($this->mcp_servers_tools_map)) {
            // fetch tools/list from all MCP servers in parallel
            $mh = curl_multi_init();
            $handles = [];
            foreach ($this->mcp_servers as $mcp__value) {
                $url = $mcp__value['url'] ?? null;
                if ($url === null) {
                    continue;
                }
                if (substr($url, -1) !== '/') {
                    $url .= '/';
                }
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt(
                    $ch,
                    CURLOPT_POSTFIELDS,
                    json_encode([
                        'jsonrpc' => '2.0',
                        'id' => 1,
                        'method' => 'tools/list'
                    ])
                );
                $headers = ['Content-Type: application/json', 'Accept: application/json, text/event-stream'];
                if (!empty($mcp__value['authorization_token'])) {
                    $headers[] = 'Authorization: Bearer ' . $mcp__value['authorization_token'];
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_UNRESTRICTED_AUTH, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_multi_add_handle($mh, $ch);
                $handles[] = ['ch' => $ch, 'mcp' => $mcp__value];
            }
            // execute all in parallel
            do {
                $status = curl_multi_exec($mh, $active);
                if ($active) {
                    curl_multi_select($mh);
                }
            } while ($active && $status === CURLM_OK);
            // collect results
            foreach ($handles as $h) {
                $response = curl_multi_getcontent($h['ch']);
                $httpCode = curl_getinfo($h['ch'], CURLINFO_HTTP_CODE);
                curl_multi_remove_handle($mh, $h['ch']);
                if ($httpCode < 200 || $httpCode >= 300 || !$response) {
                    continue;
                }
                if (strpos($response, 'event: message') !== false) {
                    preg_match('/^data: (.+)$/m', $response, $matches);
                    if (isset($matches[1])) {
                        $response = trim($matches[1]);
                    }
                }
                $toolsData = json_decode($response, true);
                if (!isset($toolsData['result']['tools']) || !is_array($toolsData['result']['tools'])) {
                    continue;
                }
                $url = $h['mcp']['url'] ?? null;
                $authorization_token = $h['mcp']['authorization_token'] ?? null;
                $allowed_tools = $h['mcp']['allowed_tools'] ?? null;
                foreach ($toolsData['result']['tools'] as $tool) {
                    if ($allowed_tools !== null && !in_array($tool['name'], $allowed_tools, true)) {
                        continue;
                    }
                    $schema = self::buildLocalToolsArgsSanitize(
                        $tool['inputSchema'] ?? ['type' => 'object'],
                        $strip_schema_keys
                    );
                    $tool_def = [
                        'name' => $tool['name'],
                        'description' => $tool['description'] ?? '',
                        $schema_key => $schema
                    ];
                    if ($wrap_function_type) {
                        $tool_def['type'] = 'function';
                    }
                    $this->mcp_servers_tools_map[$tool['name']] = [
                        'url' => $url,
                        'authorization_token' => $authorization_token,
                        'schema' => $tool_def
                    ];
                }
            }
            curl_multi_close($mh);
        }
        $tools = [];
        foreach ($this->mcp_servers_tools_map as $tool_entry) {
            $tools[] = $tool_entry['schema'];
        }
        return $tools;
    }

    protected static function buildLocalToolsArgsSanitize(array $schema, array $strip_keys = []): array
    {
        foreach ($strip_keys as $strip_key) {
            unset($schema[$strip_key]);
        }
        foreach ($schema as $key => &$value) {
            if (is_array($value)) {
                // "type": ["array", "null"] → "type": "array"
                if ($key === 'type' && !empty($value) && array_is_list($value)) {
                    $value = $value[0];
                } elseif (empty($value) && $key === 'items') {
                    $value = ['type' => 'string'];
                } elseif (empty($value) && in_array($key, ['properties', 'additionalProperties'], true)) {
                    $value = new \stdClass();
                } else {
                    $value = self::buildLocalToolsArgsSanitize($value, $strip_keys);
                }
            }
        }
        // ensure "items" exists when "type" is "array"
        if (isset($schema['type']) && $schema['type'] === 'array' && !isset($schema['items'])) {
            $schema['items'] = ['type' => 'string'];
        }
        return $schema;
    }

    abstract public function fetchModels(): array;

    abstract protected function askThis(
        ?string $prompt = null,
        mixed $files = null,
        bool $add_prompt_to_session = true,
        ?string $prev_output_text = null,
        float $prev_costs = 0.0,
        int $length_continuation_count = 0
    ): array;

    /**
     * If the model was cut off by the length limit (max_tokens / finish_reason=length /
     * incomplete / finishReason=MAX_TOKENS), append the partial response to the session
     * and auto-continue via a fresh askThis call. Returns the recursive result, or null
     * if no continuation is needed. Capped at 10 continuations per turn.
     */
    protected function continueIfNotFinished(
        mixed $response,
        string $output_text,
        float $costs,
        int $length_continuation_count
    ): ?array {
        if ($length_continuation_count >= 10 || !__::x($response?->result ?? null)) {
            return null;
        }
        $r = $response->result;
        $is_cutoff =
            ($r->stop_reason ?? null) === 'max_tokens' ||
            ($r->choices[0]->finish_reason ?? null) === 'length' ||
            (($r->status ?? null) === 'incomplete' &&
                ($r->incomplete_details->reason ?? null) === 'max_output_tokens') ||
            ($r->candidates[0]->finishReason ?? null) === 'MAX_TOKENS';
        if (!$is_cutoff) {
            return null;
        }
        $this->log('length cutoff detected — auto-continuing (' . ($length_continuation_count + 1) . '/10)');
        $this->addResponseToSession($response);
        return $this->askThis(
            prompt: 'Continue.',
            files: null,
            add_prompt_to_session: true,
            prev_output_text: $output_text,
            prev_costs: $costs,
            length_continuation_count: $length_continuation_count + 1
        );
    }

    public function ping(): bool
    {
        return !empty($this->fetchModels());
    }

    abstract protected function makeApiCall(?array $args = null): mixed;

    protected function applyTemperatureParameter(array $args, ?string $container_key = null): array
    {
        if ($this->temperature === null) {
            return $args;
        }

        $supports_temperature = true;
        foreach ($this->models as $models__value) {
            if (($models__value['name'] ?? null) !== $this->model) {
                continue;
            }

            $supports_temperature = $models__value['supports_temperature'] ?? true;
            break;
        }

        if ($supports_temperature === false) {
            return $args;
        }

        if ($container_key === null) {
            $args['temperature'] = $this->temperature;
            return $args;
        }

        if (!isset($args[$container_key]) || !is_array($args[$container_key])) {
            $args[$container_key] = [];
        }
        $args[$container_key]['temperature'] = $this->temperature;

        return $args;
    }

    protected function trimPrompt(string $prompt): string
    {
        return __::trim_whitespace(__::trim_indentation($prompt));
    }

    abstract protected function bringPromptInFormat(string $prompt, mixed $files = null): array;

    abstract protected function addResponseToSession(mixed $response): void;

    protected function truncateMcpToolResultContent(mixed $content, int $max_length = 500): mixed
    {
        if (!is_array($content)) {
            return $content;
        }

        for ($i = 0; $i < count($content); $i++) {
            if (
                isset($content[$i]->type) &&
                $content[$i]->type === 'mcp_tool_result' &&
                isset($content[$i]->content) &&
                is_array($content[$i]->content)
            ) {
                foreach ($content[$i]->content as $content_item__key => $content_item__value) {
                    $type = is_object($content_item__value)
                        ? $content_item__value?->type ?? null
                        : $content_item__value['type'] ?? null;
                    $text = is_object($content_item__value)
                        ? $content_item__value?->text ?? null
                        : $content_item__value['text'] ?? null;
                    if ($type !== 'text' || !is_string($text) || mb_strlen($text) <= $max_length) {
                        continue;
                    }

                    $original_length = mb_strlen($text);
                    $truncated = mb_substr($text, 0, $max_length);
                    $truncated .= "\n\n[... content truncated: $original_length chars reduced to $max_length chars ...]";

                    if (is_object($content_item__value)) {
                        $content[$i]->content[$content_item__key]->text = $truncated;
                    } else {
                        $content[$i]->content[$content_item__key]['text'] = $truncated;
                    }
                }
            }
        }

        return $content;
    }

    protected function modifyArgsLocal(?array $args): ?array
    {
        $model_name = strtolower($this->model ?? '');
        $enable_thinking = $this->enable_thinking;
        $uses_tools = !empty($args['tools']) && is_array($args['tools']);

        // --- detect profile ---
        $profile = 'default';
        if ($uses_tools) {
            $profile = 'agentic';
        } else {
            $prompt_text = '';
            // scan input (responses api) or messages (chat completions)
            $items = $args['input'] ?? ($args['messages'] ?? []);
            foreach (array_reverse($items) as $item) {
                if (!is_array($item) || ($item['role'] ?? null) !== 'user') {
                    continue;
                }
                $content = $item['content'] ?? [];
                if (is_string($content)) {
                    $prompt_text = $content;
                    break;
                }
                foreach ($content as $part) {
                    if (is_array($part) && isset($part['text'])) {
                        $prompt_text .= ' ' . $part['text'];
                    }
                }
                break;
            }
            $prompt_text = mb_strtolower(trim($prompt_text));

            if ($prompt_text !== '') {
                $creative_keywords = [
                    'geschichte',
                    'kreativ',
                    'gedicht',
                    'erzähl',
                    'schreib',
                    'story',
                    'märchen',
                    'roman',
                    'szene',
                    'witz',
                    'witzig',
                    'lustig',
                    'ulkig',
                    'humor',
                    'komisch'
                ];
                $reasoning_keywords = [
                    'denke',
                    'überlege',
                    'analysiere',
                    'erkläre',
                    'warum',
                    'berechne',
                    'löse',
                    'beweise',
                    'vergleiche',
                    'schlussfolgere'
                ];
                $matches = fn(array $keywords) => array_reduce(
                    $keywords,
                    fn($carry, $kw) => $carry || str_contains($prompt_text, $kw),
                    false
                );
                if ($matches($creative_keywords)) {
                    $profile = 'creative';
                } elseif (
                    $matches($reasoning_keywords) ||
                    preg_match('/\d+\s*[\*\+\-x\/]\s*\d+/', $prompt_text) === 1
                ) {
                    $profile = 'reasoning';
                }
            }
        }

        // --- sampling parameters per model family ---
        if (str_contains($model_name, 'qwq')) {
            $args += ['top_p' => 0.95, 'top_k' => 40];
        } elseif (
            preg_match('/qwen(\d+)\.(\d+)/', $model_name, $_qm) === 1 &&
            ((int) $_qm[1] >= 4 || ((int) $_qm[1] === 3 && (int) $_qm[2] >= 5))
        ) {
            // Matches Qwen3.5+ (3.5, 3.6, 3.7, … 3.10, …) and any Qwen4+ (4.x,
            // 5.x, …). Keeps the regex forward-compatible with future releases.
            // Official Unsloth recommendation (https://unsloth.ai/docs/models/qwen3.6):
            //   Thinking / General:  temp=1.0, top_p=0.95, top_k=20, min_p=0.0, presence_penalty=1.5
            //   Instruct / General:  temp=0.7, top_p=0.8,  top_k=20, min_p=0.0, presence_penalty=1.5
            // presence_penalty=1.5 is critical to prevent repetition loops during reasoning
            // (confirmed looping on 3.6-35B-A3B without it — same MoE/A3B architecture as 3.5).
            // Resolve effective thinking mode:
            //   - explicit $enable_thinking=true/false from caller takes precedence
            //   - null means "use server-side default" — we can't see that default here,
            //     but our llama-server is started with enable_thinking=true for this
            //     family, so null is treated like true for sampling purposes.
            $thinking_effective = $enable_thinking !== false;
            if ($thinking_effective === true) {
                $args['temperature'] = 1.0;
                $args += [
                    'top_p' => 0.95,
                    'top_k' => 20,
                    'min_p' => 0.0,
                    'presence_penalty' => 1.5,
                    'repeat_penalty' => 1.0
                ];
            } else {
                $args['temperature'] = 0.7;
                $args += [
                    'top_p' => 0.8,
                    'top_k' => 20,
                    'min_p' => 0.0,
                    'presence_penalty' => 1.5,
                    'repeat_penalty' => 1.0
                ];
            }
            // Only emit chat_template_kwargs when the caller explicitly wants to
            // override the server default. Leaving it unset keeps the llama-server
            // startup value (--chat-template-kwargs) in charge.
            if ($enable_thinking !== null) {
                $args['chat_template_kwargs'] = ($args['chat_template_kwargs'] ?? []) + [
                    'enable_thinking' => $enable_thinking
                ];
                // soft hint to keep the <think> block bounded — Qwen3.x's chat
                // template renders this into a system-level instruction (not a
                // hard server-side cap), so the model self-conditions rather
                // than getting truncated mid-thought
                if ($enable_thinking === true) {
                    $args['chat_template_kwargs'] += ['thinking_budget' => 2000];
                }
            }
        } elseif (str_contains($model_name, 'qwen3')) {
            $args += ['top_p' => 0.8, 'top_k' => 20];
        } elseif (str_contains($model_name, 'minimax') && str_contains($model_name, 'm2')) {
            // Official MiniMax M2.7 recommendation (https://unsloth.ai/docs/models/minimax-m27
            // and the MiniMaxAI/MiniMax-M2.7 HuggingFace card):
            // temperature=1.0, top_p=0.95, top_k=40, min_p=0.01 — and explicitly
            // NO penalty parameters. The model was tuned to work without
            // presence_penalty, frequency_penalty or repeat_penalty; adding them
            // disturbs the token distribution and (with llama.cpp at Q4_K_XL)
            // causes the model to emit EOS prematurely after just the intro
            // text, before any tool calls. If repetition loops in the <think>
            // stream re-appear, prefer a small frequency_penalty (~0.3) over a
            // large presence_penalty.
            $args['temperature'] = 1.0;
            $args += [
                'top_p' => 0.95,
                'top_k' => 40,
                'min_p' => 0.01
            ];
        } elseif (preg_match('/gemma-?(\d+)/', $model_name, $_gm) === 1 && (int) $_gm[1] >= 4) {
            // Official Gemma 4 recommendation (https://unsloth.ai/docs/models/gemma-4):
            // temperature=1.0, top_p=0.95, top_k=64. No penalty parameters
            // documented. enable_thinking is controlled via chat_template_kwargs
            // server-side (set in runpod.sh per-model startup).
            $args['temperature'] = 1.0;
            $args += [
                'top_p' => 0.95,
                'top_k' => 64
            ];
            // Allow caller to override the server-default enable_thinking flag
            // (mirrors the Qwen3.5+ branch above for consistency).
            if ($enable_thinking !== null) {
                $args['chat_template_kwargs'] = ($args['chat_template_kwargs'] ?? []) + [
                    'enable_thinking' => $enable_thinking
                ];
            }
        } elseif (
            preg_match('/glm-?(\d+)\.?(\d+)?/', $model_name, $_glm) === 1 &&
            ((int) $_glm[1] >= 5 || ((int) $_glm[1] === 4 && (int) ($_glm[2] ?? 0) >= 7))
        ) {
            // Covers GLM-4.7 / GLM-4.7-Flash and GLM-5.x / GLM-5.1.
            // Officially documented sampler profiles:
            //   General      : temperature=1.0, top_p=0.95, min_p=0.01    (https://unsloth.ai/docs/models/glm-5.1)
            //   Tool-Calling : temperature=0.7, top_p=1.0,  min_p=0.01    (https://unsloth.ai/docs/models/tutorials/glm-4.7-flash)
            // repeat_penalty must be disabled (=1.0) per the GLM-4.7-Flash tutorial.
            // Charly's master and any sub-chat that issues MCP tools both run as
            // tool-calling — switch profile based on $uses_tools so the same
            // handler covers both paths cleanly.
            // hybrid-thinking enable_thinking is only valid for GLM ≥ 5; the
            // GLM-4.7-Flash tutorial does not document a thinking flag, so we
            // only emit chat_template_kwargs when the caller explicitly requests
            // an override AND we're on a hybrid-thinking model line.
            //
            // Anti-loop hardening (community-sourced, not in unsloth's official
            // sampler profile but observed to mitigate the deterministic
            // <think>-block runaway documented in
            // https://huggingface.co/unsloth/GLM-4.7-Flash-GGUF/discussions/26
            // and https://github.com/ggml-org/llama.cpp/issues/19613):
            //   - top_k=40 caps the candidate pool, preventing the model from
            //     getting stuck cycling between low-probability synonyms during
            //     reasoning
            //   - DRY (Don't Repeat Yourself) sampler bites n-gram repetitions
            //     at the sampler level without disturbing the global token
            //     distribution like repeat_penalty would
            //   - thinking_budget_tokens=4000 is a per-request hint to llama.cpp
            //     to inject </think> after N reasoning tokens; documented for
            //     Qwen3.5+ in llama.cpp Discussion #21445, untested for GLM but
            //     llama.cpp ignores unknown keys silently, so it costs nothing
            if ($uses_tools === true) {
                $args['temperature'] = 0.7;
                $args += [
                    'top_p' => 1.0,
                    'top_k' => 40,
                    'min_p' => 0.01,
                    'repeat_penalty' => 1.0,
                    'dry_multiplier' => 0.8,
                    'dry_base' => 1.75,
                    'dry_allowed_length' => 2,
                    'thinking_budget_tokens' => 4000
                ];
            } else {
                $args['temperature'] = 1.0;
                $args += [
                    'top_p' => 0.95,
                    'top_k' => 40,
                    'min_p' => 0.01,
                    'repeat_penalty' => 1.0,
                    'dry_multiplier' => 0.8,
                    'dry_base' => 1.75,
                    'dry_allowed_length' => 2,
                    'thinking_budget_tokens' => 4000
                ];
            }
            $glm_major = (int) $_glm[1];
            if ($enable_thinking !== null && $glm_major >= 5) {
                $args['chat_template_kwargs'] = ($args['chat_template_kwargs'] ?? []) + [
                    'enable_thinking' => $enable_thinking
                ];
            }
        } elseif (preg_match('/kimi-?k(\d+)\.?(\d+)?/', $model_name, $_kim) === 1) {
            // Official Kimi K2.6 recommendation (https://unsloth.ai/docs/models/kimi-k2.6):
            //   Thinking Mode (default): temperature=1.0, top_p=0.95
            //   Instant Mode (non-think): temperature=0.6, top_p=0.95
            // Hybrid thinking model — enable_thinking via chat_template_kwargs.
            // Treats Kimi-Dev variants as legacy (they don't match this regex,
            // they match `kimi-dev` not `kimi-k…`).
            $thinking_effective = $enable_thinking !== false;
            $args['temperature'] = $thinking_effective === true ? 1.0 : 0.6;
            $args += [
                'top_p' => 0.95
            ];
            if ($enable_thinking !== null) {
                $args['chat_template_kwargs'] = ($args['chat_template_kwargs'] ?? []) + [
                    'enable_thinking' => $enable_thinking
                ];
            }
        } elseif (str_contains($model_name, 'gpt-oss') && $uses_tools) {
            $args += ['top_p' => 0.9, 'top_k' => 20];
        }

        // --- qwen3: suppress runaway thinking via empty <think> priming ---
        // DISABLED: we now rely on Qwen's recommended sampling params (presence_penalty=1.5)
        // to control reasoning loops while keeping thinking enabled. kept as commented-out
        // fallback in case loops re-appear. re-enable this block if needed.
        // if (str_contains($model_name, 'qwen3') && $provider !== 'llamacpp') {
        //     $think_block = "<think>\n\n</think>\n\n";
        //     // responses api format
        //     if (!empty($args['input']) && is_array($args['input'])) {
        //         $already_primed = false;
        //         foreach ($args['input'] as $item) {
        //             if (!is_array($item) || ($item['role'] ?? null) !== 'assistant') {
        //                 continue;
        //             }
        //             foreach ($item['content'] ?? [] as $part) {
        //                 if (is_array($part) && ($part['text'] ?? '') === $think_block) {
        //                     $already_primed = true;
        //                     break 2;
        //                 }
        //             }
        //         }
        //         if (!$already_primed) {
        //             $args['input'][] = [
        //                 'role' => 'assistant',
        //                 'content' => [['type' => 'output_text', 'text' => $think_block]]
        //             ];
        //         }
        //     }
        //     // chat completions format
        //     if (!empty($args['messages']) && is_array($args['messages'])) {
        //         $already_primed = false;
        //         foreach ($args['messages'] as $msg) {
        //             if (($msg['role'] ?? null) === 'assistant' && ($msg['content'] ?? '') === $think_block) {
        //                 $already_primed = true;
        //                 break;
        //             }
        //         }
        //         if (!$already_primed) {
        //             $args['messages'][] = ['role' => 'assistant', 'content' => $think_block];
        //         }
        //     }
        // }

        // --- output limits per profile ---
        // Matches Qwen3.5+ and Qwen4+ (see detection notes in the sampling branch above).
        if (
            preg_match('/qwen(\d+)\.(\d+)/', $model_name, $_qm) === 1 &&
            ((int) $_qm[1] >= 4 || ((int) $_qm[1] === 3 && (int) $_qm[2] >= 5))
        ) {
            if ($uses_tools) {
                $args += ['max_output_tokens' => 12000, 'parallel_tool_calls' => false, 'max_tool_calls' => 30];
            } elseif ($profile === 'creative') {
                $args += ['max_output_tokens' => 2500];
            } elseif ($profile === 'reasoning') {
                $args += ['max_output_tokens' => 4000];
            } else {
                $args += ['max_output_tokens' => 8000];
            }
        } elseif (str_contains($model_name, 'qwen3')) {
            $args += ['max_output_tokens' => 8000];
        }
        if (str_contains($model_name, 'minimax') && str_contains($model_name, 'm2')) {
            if ($uses_tools) {
                $args += ['max_output_tokens' => 12000, 'parallel_tool_calls' => false, 'max_tool_calls' => 30];
            } elseif ($profile === 'creative') {
                $args += ['max_output_tokens' => 2500];
            } elseif ($profile === 'reasoning') {
                $args += ['max_output_tokens' => 4000];
            } else {
                $args += ['max_output_tokens' => 8000];
            }
        }
        if (str_contains($model_name, 'glm')) {
            if ($uses_tools) {
                $args += ['max_output_tokens' => 12000, 'parallel_tool_calls' => false, 'max_tool_calls' => 30];
            } elseif ($profile === 'creative') {
                $args += ['max_output_tokens' => 2500];
            } elseif ($profile === 'reasoning') {
                $args += ['max_output_tokens' => 4000];
            } else {
                $args += ['max_output_tokens' => 8000];
            }
        }
        if (preg_match('/gemma-?(\d+)/', $model_name, $_gm) === 1 && (int) $_gm[1] >= 4) {
            if ($uses_tools) {
                $args += ['max_output_tokens' => 12000, 'parallel_tool_calls' => false, 'max_tool_calls' => 30];
            } elseif ($profile === 'creative') {
                $args += ['max_output_tokens' => 2500];
            } elseif ($profile === 'reasoning') {
                $args += ['max_output_tokens' => 4000];
            } else {
                $args += ['max_output_tokens' => 8000];
            }
        }

        // for chat completions (llamacpp/openrouter): map max_output_tokens to max_tokens
        if (isset($args['messages']) && isset($args['max_output_tokens']) && !isset($args['max_tokens'])) {
            $args['max_tokens'] = $args['max_output_tokens'];
        }

        unset($args['reasoning'], $args['ttl']);

        return $args;
    }

    protected function stripThinkingBlocks(string $text): string
    {
        // remove <think>...</think> blocks produced by reasoning models (e.g. QwQ).
        // also strip orphan closing </think> tags — llama-server occasionally
        // misclassifies the closing tag as content when the think block is
        // empty (post-tool-turn after a tool_call), leaving '</think>...' at
        // the start of the assistant content.
        $text = preg_replace('/<think>.*?<\/think>\s*/s', '', $text);
        $text = preg_replace('/^\s*<\/think>\s*/', '', $text);
        return trim($text);
    }

    /**
     * Whitespace-runaway detector for streamed buffers.
     *
     * Inspects the trailing whitespace run on every currently-growing stream
     * buffer (reasoning + every assembling tool_call's arguments). Stateless —
     * the buffers themselves are the source of truth. Throws when any trailing
     * run exceeds the threshold — symptom of a sampling loop (observed on
     * GPT-5.5 around JSON tool_call closing boundaries: model produces
     * \n/\t/space tokens indefinitely instead of emitting the closing brace).
     *
     * @throws \RuntimeException when the trailing whitespace run exceeds the threshold.
     */
    protected function detectWhitespaceEndlessLoop(): void
    {
        $threshold = 500;
        $buffers = [['reasoning', $this->stream_reasoning_buffer]];
        $tcs = $this->stream_response->result->choices[0]->message->tool_calls ?? [];
        foreach ($tcs as $tc) {
            if (isset($tc['function']['arguments'])) {
                $buffers[] = ['tool_call arguments', $tc['function']['arguments']];
            }
        }
        foreach ($buffers as [$context, $buffer]) {
            $trail = strlen($buffer) - strlen(rtrim($buffer, " \t\n\r"));
            if ($trail > $threshold) {
                throw new \RuntimeException(
                    'whitespace runaway: ' .
                        $trail .
                        ' trailing whitespace chars in ' .
                        $context .
                        ' stream (threshold ' .
                        $threshold .
                        ') — likely sampling-loop'
                );
            }
        }
    }

    /**
     * Stateful removal of <tool_call>...</tool_call> and <minimax:tool_call>...</minimax:tool_call>
     * blocks from a streamed text. Handles tags split across chunks by buffering partial tag prefixes.
     * Used to hide tool call XML from user-visible content/reasoning streams
     * (they are extracted separately by the reasoning_buffer parser).
     */
    protected function stripToolCallBlocks(string $text): string
    {
        // strip both minimax and standard tool_call blocks
        $text = $this->stripToolCallBlocksPair($text, '<minimax:tool_call>', '</minimax:tool_call>');
        $text = $this->stripToolCallBlocksPair($text, '<tool_call>', '</tool_call>');
        return $text;
    }

    protected function stripToolCallBlocksPair(string $text, string $open_tag, string $close_tag): string
    {
        $pending = $this->stream_tool_call_strip_tag_buf . $text;
        $this->stream_tool_call_strip_tag_buf = '';
        $out = '';

        while ($pending !== '') {
            $needle = $this->stream_tool_call_strip_in_block ? $close_tag : $open_tag;
            $pos = strpos($pending, $needle);
            if ($pos !== false) {
                if (!$this->stream_tool_call_strip_in_block) {
                    $out .= substr($pending, 0, $pos);
                    $this->stream_tool_call_strip_in_block = true;
                    $pending = substr($pending, $pos + strlen($open_tag));
                } else {
                    $this->stream_tool_call_strip_in_block = false;
                    $pending = substr($pending, $pos + strlen($close_tag));
                }
            } else {
                // no match found; buffer a possible partial-tag suffix
                $max_len = max(strlen($open_tag), strlen($close_tag)) - 1;
                $buf_len = 0;
                for ($i = min($max_len, strlen($pending)); $i >= 1; $i--) {
                    $tail = substr($pending, -$i);
                    if (strpos($open_tag, $tail) === 0 || strpos($close_tag, $tail) === 0) {
                        $buf_len = $i;
                        break;
                    }
                }
                if ($buf_len > 0) {
                    $this->stream_tool_call_strip_tag_buf = substr($pending, -$buf_len);
                    $pending = substr($pending, 0, strlen($pending) - $buf_len);
                }
                if (!$this->stream_tool_call_strip_in_block) {
                    $out .= $pending;
                }
                break;
            }
        }
        return $out;
    }

    protected function resetToolCallStripState(): void
    {
        $this->stream_tool_call_strip_in_block = false;
        $this->stream_tool_call_strip_tag_buf = '';
    }

    protected static function extractErrorMessage(mixed $input): ?string
    {
        // accepts either a parsed error array (from stream callbacks)
        // or a full response object (from askThis)
        if (is_array($input)) {
            $error = $input;
        } elseif (is_object($input)) {
            $error = $input->result->error ?? null;
            if ($error === null && ($input->result->type ?? null) === 'error') {
                $error = $input->result->error ?? null;
            }
            if ($error === null) {
                return null;
            }
        } else {
            return null;
        }
        if (is_string($error)) {
            return $error;
        }
        // normalize object to array
        if (is_object($error)) {
            $error = json_decode(json_encode($error), true);
        }
        if (!is_array($error)) {
            return null;
        }
        if (!empty($error['metadata']['raw'])) {
            return $error['metadata']['raw'];
        }
        $msg = $error['message'] ?? json_encode($error, JSON_UNESCAPED_UNICODE);
        // enrich with metadata details (e.g. openrouter error_type, provider_name)
        if (!empty($error['metadata']) && is_array($error['metadata'])) {
            $details = array_filter($error['metadata'], fn($v) => is_string($v));
            if (!empty($details)) {
                $msg .= ' (' . implode(', ', array_map(fn($k, $v) => "$k: $v", array_keys($details), $details)) . ')';
            }
        }
        return $msg;
    }

    protected function normalizeStreamTextDelta(
        string $text,
        string $existing_text = '',
        bool $strip_leading_newlines = false
    ): string {
        if ($strip_leading_newlines) {
            $text = ltrim($text, "\n");
        }

        if ($text === '') {
            return '';
        }

        $existing_newline_count = 0;
        if ($existing_text !== '' && preg_match('/\n+$/', $existing_text, $existing_matches) === 1) {
            $existing_newline_count = strlen($existing_matches[0]);
        }

        if ($existing_newline_count > 0 && preg_match('/^\n+/', $text, $leading_matches) === 1) {
            $allowed_leading_newlines = max(0, 2 - min($existing_newline_count, 2));
            $text = str_repeat("\n", $allowed_leading_newlines) . substr($text, strlen($leading_matches[0]));
        }

        return preg_replace('/\n{3,}/', "\n\n", $text);
    }

    protected function parseJson(mixed $msg): mixed
    {
        if (is_string($msg) && __::string_is_json(trim($msg))) {
            return json_decode(trim($msg));
        }
        if (is_string($msg) && preg_match('/```(?:json)?\s*(\{.*?\}|\[.*?\])\s*```/s', $msg, $m) === 1) {
            $decoded = json_decode($m[1]);
            if ($decoded !== null || strtolower(trim($m[1])) === 'null') {
                return $decoded;
            }
        }
        if (is_string($msg) && preg_match('/\{(?:[^{}]|(?R))*\}|\[(?:[^\[\]]|(?R))*\]/', $msg, $m) === 1) {
            $decoded = json_decode($m[0]);
            if ($decoded !== null || strtolower(trim($m[0])) === 'null') {
                return $decoded;
            }
        }
        return $msg;
    }

    public function enable_log(string $filename): void
    {
        $this->log = $filename;
    }

    public function disable_log(): void
    {
        $this->log = null;
    }

    public function getSessionId(): ?string
    {
        return $this->session_id;
    }

    public function getSessionContent(): array
    {
        return self::$sessions[$this->session_id];
    }

    public function prependPromptToSession(string $prompt, mixed $files = null): void
    {
        $prompt = $this->trimPrompt($prompt);
        array_unshift(self::$sessions[$this->session_id], $this->bringPromptInFormat($prompt, $files));
    }

    public function appendPromptToSession(string $prompt, mixed $files = null): void
    {
        $prompt = $this->trimPrompt($prompt);
        self::$sessions[$this->session_id][] = $this->bringPromptInFormat($prompt, $files);
    }

    public function log(mixed $msg, ?string $prefix = null): void
    {
        if ($this->log !== null) {
            if (!is_string($msg)) {
                $msg = json_encode($msg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            /*
            $msg = str_replace(["\r\n", "\r", "\n"], ' ', $msg);
            $msg = preg_replace_callback(
                '/s:(\d+):"(.*?)";/s',
                function ($matches) {
                    return strlen($matches[2]) > 1000 ? 's:' . $matches[1] . ':"...";' : $matches[0];
                },
                $msg
            );
            */
            $msg =
                'ℹ️' .
                ' ' .
                $this->name .
                ' - ' .
                $this->model .
                ' - ' .
                \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)))
                    ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
                    ->format('Y-m-d H:i:s.u') .
                ($prefix !== null ? ' - ' . $prefix : '') .
                ' ' .
                'ℹ️' .
                PHP_EOL .
                $msg .
                PHP_EOL .
                PHP_EOL;
            file_put_contents($this->log, $msg, FILE_APPEND);
        }
    }

    public function getTestModels(): array
    {
        return array_map(
            function ($models__value) {
                return $models__value['name'];
            },
            array_values(
                array_filter($this->models, function ($models__value) {
                    return $models__value['test'] === true;
                })
            )
        );
    }

    protected function getContextLengthForModel(): int
    {
        foreach ($this->models as $models__value) {
            if ($models__value['name'] === $this->model) {
                return $models__value['context_length'] ?? 128000;
            }
        }
        return 128000;
    }

    protected function getMaxOutputTokensForModel(): int
    {
        foreach ($this->models as $models__value) {
            if ($models__value['name'] === $this->model) {
                return $models__value['max_output_tokens'] ?? 16384;
            }
        }
        return 16384;
    }

    protected function addCosts(mixed $response, array &$return): void
    {
        //$this->log($response, 'add costs');
        //$this->log('response with length ' . strlen(json_encode($response)), 'add costs');

        $input_tokens = 0;
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->usage ?? null) &&
            __::x($response?->result?->usage?->input_tokens ?? null)
        ) {
            $input_tokens += $response->result->usage->input_tokens;
        }
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->usageMetadata ?? null) &&
            __::x($response?->result?->usageMetadata?->promptTokenCount ?? null)
        ) {
            $input_tokens += $response->result->usageMetadata->promptTokenCount;
        }
        // chat completions format (openrouter)
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->usage ?? null) &&
            __::x($response?->result?->usage?->prompt_tokens ?? null)
        ) {
            $input_tokens += $response->result->usage->prompt_tokens;
        }

        $input_cached_tokens = 0;
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->usage ?? null) &&
            __::x($response?->result?->usage?->input_tokens_details ?? null) &&
            __::x($response?->result?->usage?->input_tokens_details?->cached_tokens ?? null)
        ) {
            $input_cached_tokens += $response->result->usage->input_tokens_details->cached_tokens;
        }
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->usage ?? null) &&
            __::x($response?->result?->usage?->cache_creation_input_tokens ?? null)
        ) {
            $input_cached_tokens += $response->result->usage->cache_creation_input_tokens;
        }
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->usage ?? null) &&
            __::x($response?->result?->usage?->cache_read_input_tokens ?? null)
        ) {
            $input_cached_tokens += $response->result->usage->cache_read_input_tokens;
        }

        $output_tokens = 0;
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->usage ?? null) &&
            __::x($response?->result?->usage?->output_tokens ?? null)
        ) {
            $output_tokens += $response->result->usage->output_tokens;
        }
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->usageMetadata ?? null) &&
            __::x($response?->result?->usageMetadata?->candidatesTokenCount ?? null)
        ) {
            $output_tokens += $response->result->usageMetadata->candidatesTokenCount;
        }
        // chat completions format (openrouter)
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->usage ?? null) &&
            __::x($response?->result?->usage?->completion_tokens ?? null)
        ) {
            $output_tokens += $response->result->usage->completion_tokens;
        }

        $costs = 0;
        foreach ($this->models as $models__value) {
            if ($models__value['name'] === $this->model) {
                // image/audio model entries carry costs.image|audio instead of
                // input/output token rates — guard with ?? 0 so a stray ask()
                // call on a non-text model does not warn.
                $costs =
                    $input_tokens * ($models__value['costs']['input'] ?? 0) +
                    $input_cached_tokens * ($models__value['costs']['input_cached'] ?? 0) +
                    $output_tokens * ($models__value['costs']['output'] ?? 0);
                break;
            }
        }

        $this->log((float) round($costs, 5) . ' - response with length ' . strlen(json_encode($response)), 'add costs');
        $return['costs'] += (float) round($costs, 5);
        if (!isset($return['output_tokens'])) {
            $return['output_tokens'] = 0;
        }
        $return['output_tokens'] += $output_tokens;
    }

    protected function getStreamCallback(): ?\Closure
    {
        if ($this->stream === false) {
            return null;
        }

        $this->stream_event = null;
        $this->stream_buffer_in = '';
        $this->stream_buffer_data = '';
        $this->stream_current_block_type = null;
        $this->stream_first_text_sent = false;
        $this->stream_running = false;
        $this->stream_in_think = false;
        $this->stream_think_tag_buf = '';
        $this->stream_callback = null;

        if ($this->name === 'anthropic' || $this->name === 'test') {
            // mimic non stream result
            $this->stream_response = (object) [
                'result' => (object) [
                    'content' => [],
                    'stop_reason' => null,
                    'usage' => (object) [
                        'input_tokens' => 0,
                        'cache_creation_input_tokens' => 0,
                        'cache_read_input_tokens' => 0,
                        'output_tokens' => 0
                    ]
                ]
            ];

            $this->stream_callback = function ($chunk) {
                /*
                echo $chunk;
                return strlen($chunk);
                */

                $this->log($chunk, 'chunk');
                $this->stream_buffer_in .= $chunk;

                // check if chunk is full json
                if (json_decode($chunk, true) !== null) {
                    $parsed = json_decode($chunk, true);
                    if (isset($parsed['error']) && isset($parsed['error']['message'])) {
                        $this->stream_response->result->error = (object) [
                            'message' => self::extractErrorMessage($parsed['error'])
                        ];
                    }
                }

                // parse line by line
                if (strpos($this->stream_buffer_in, "\n") !== false) {
                    while (($pos = strpos($this->stream_buffer_in, "\n")) !== false) {
                        $line = rtrim(substr($this->stream_buffer_in, 0, $pos), "\r");
                        $this->stream_buffer_in = substr($this->stream_buffer_in, $pos + 1);

                        if (strpos($line, 'event: ') === 0) {
                            $this->stream_event = substr($line, 7);
                            continue;
                        }

                        if (strpos($line, 'data: ') === 0) {
                            $dataLine = substr($line, 6);
                            $this->stream_buffer_data =
                                $this->stream_buffer_data === ''
                                    ? $dataLine
                                    : $this->stream_buffer_data . "\n" . $dataLine;
                            continue;
                        }

                        if ($line === '' && $this->stream_event !== null && $this->stream_buffer_data !== '') {
                            $parsed = json_decode($this->stream_buffer_data, true);
                            $this->stream_running = true;

                            // extract stop_reason from message_delta event
                            if (
                                isset($parsed['type']) &&
                                $parsed['type'] === 'message_delta' &&
                                isset($parsed['delta']['stop_reason'])
                            ) {
                                $this->stream_response->result->stop_reason = $parsed['delta']['stop_reason'];
                            }

                            // add new content block
                            if (isset($parsed['type']) && $parsed['type'] === 'content_block_start') {
                                $initial_block_type = $parsed['content_block']['type'] ?? null;
                                $initial_thinking = $parsed['content_block']['thinking'] ?? '';
                                if (
                                    $initial_block_type === 'thinking' &&
                                    is_string($initial_thinking) &&
                                    $initial_thinking !== ''
                                ) {
                                    echo "event: reasoning\n";
                                    echo 'data: ' .
                                        json_encode(
                                            ['delta' => $initial_thinking],
                                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                                        ) .
                                        "\n\n";
                                    if (ob_get_level() > 0) {
                                        ob_flush();
                                    }
                                    flush();
                                }
                                // if this is not the first block and previous was text, add separator
                                if (
                                    $this->stream_current_block_type === 'text' &&
                                    !empty($this->stream_response->result->content)
                                ) {
                                    $lastBlock = end($this->stream_response->result->content);
                                    if (isset($lastBlock->text) && !preg_match('/\n$/', $lastBlock->text)) {
                                        $text = "\n\n";
                                        $lastBlock->text .= $text;
                                        echo 'data: ' .
                                            json_encode([
                                                'id' => uniqid(),
                                                'choices' => [['delta' => ['content' => $text]]]
                                            ]) .
                                            "\n\n";
                                        if (ob_get_level() > 0) {
                                            ob_flush();
                                        }
                                        flush();
                                        $this->stream_running = false;
                                    }
                                }
                                // add the full content block from the API
                                if (isset($parsed['content_block'])) {
                                    $this->stream_response->result->content[] = (object) $parsed['content_block'];
                                }
                                $this->stream_current_block_type = $parsed['content_block']['type'] ?? null;
                            }

                            // stream delta content
                            if (isset($parsed['type']) && $parsed['type'] === 'content_block_delta') {
                                $index = $parsed['index'] ?? 0;
                                if (isset($this->stream_response->result->content[$index])) {
                                    $block = &$this->stream_response->result->content[$index];

                                    // handle text delta
                                    if (isset($parsed['delta']['text'])) {
                                        $text = $parsed['delta']['text'];
                                        $existing_text = isset($block->text) ? $block->text : '';

                                        // strip leading newlines at the start of each text block
                                        $text = $this->normalizeStreamTextDelta(
                                            $text,
                                            $existing_text,
                                            $existing_text === ''
                                        );
                                        if ($text === '') {
                                            $this->stream_buffer_data = '';
                                            $this->stream_event = null;
                                            continue;
                                        }
                                        $this->stream_first_text_sent = true;

                                        if (!isset($block->text)) {
                                            $block->text = '';
                                        }
                                        $block->text .= $text;

                                        echo 'data: ' .
                                            json_encode([
                                                'id' => uniqid(),
                                                'choices' => [['delta' => ['content' => $text]]]
                                            ]) .
                                            "\n\n";
                                        if (ob_get_level() > 0) {
                                            ob_flush();
                                        }
                                        flush();
                                        $this->stream_running = false;
                                    }

                                    // handle tool_use input delta (partial_json)
                                    if (isset($parsed['delta']['partial_json'])) {
                                        // convert input to string if it's an object/array from content_block_start
                                        if (!isset($block->input) || !is_string($block->input)) {
                                            $block->input = '';
                                        }
                                        $block->input .= $parsed['delta']['partial_json'];
                                    }

                                    // handle thinking delta
                                    $delta_type = $parsed['delta']['type'] ?? null;
                                    $is_thinking_delta =
                                        $delta_type === 'thinking_delta' ||
                                        ($delta_type === null && isset($parsed['delta']['thinking']));
                                    if ($is_thinking_delta && isset($parsed['delta']['thinking'])) {
                                        $thinking_chunk = (string) $parsed['delta']['thinking'];
                                        if ($thinking_chunk !== '') {
                                            if (!isset($block->thinking)) {
                                                $block->thinking = '';
                                            }
                                            $block->thinking .= $thinking_chunk;
                                            echo "event: reasoning\n";
                                            echo 'data: ' .
                                                json_encode(
                                                    ['delta' => $thinking_chunk],
                                                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                                                ) .
                                                "\n\n";
                                            if (ob_get_level() > 0) {
                                                ob_flush();
                                            }
                                            flush();
                                            $this->stream_running = false;
                                        }
                                    }

                                    // handle signature delta (required for multi-turn: thinking blocks must be resent with valid signature)
                                    if (isset($parsed['delta']['signature'])) {
                                        $block->signature = $parsed['delta']['signature'];
                                    }
                                }
                            }

                            // content_block_stop: finalize content blocks and parse partial_json to real json
                            if (isset($parsed['type']) && $parsed['type'] === 'content_block_stop') {
                                $index = $parsed['index'] ?? count($this->stream_response->result->content) - 1;

                                // parse partial_json input to real json object for tool_use/mcp_tool_use blocks
                                if (isset($this->stream_response->result->content[$index])) {
                                    $block = &$this->stream_response->result->content[$index];
                                    if (isset($block->input) && is_string($block->input)) {
                                        // convert empty string to empty object (anthropic API requires tool_use.input to be a dict, not an array — json_encode(new stdClass()) → "{}", json_encode([]) → "[]")
                                        if ($block->input === '') {
                                            $block->input = new \stdClass();
                                        } else {
                                            $parsedInput = json_decode($block->input);
                                            if (is_object($parsedInput)) {
                                                $block->input = $parsedInput;
                                            } elseif (is_array($parsedInput) && count($parsedInput) === 0) {
                                                // json_decode('{}') with assoc=false returns stdClass, but guard against '[]' → stay a dict
                                                $block->input = new \stdClass();
                                            } elseif ($parsedInput !== null) {
                                                $block->input = $parsedInput;
                                            }
                                        }
                                    }
                                }

                                // add newline for pause_turn (next sentence coming in new request)
                                if (
                                    $this->stream_current_block_type === 'text' &&
                                    $this->stream_response->result->stop_reason === 'pause_turn'
                                ) {
                                    if (
                                        isset($this->stream_response->result->content[$index]) &&
                                        isset($this->stream_response->result->content[$index]->text)
                                    ) {
                                        $text = "\n\n";
                                        $this->stream_response->result->content[$index]->text .= $text;

                                        echo 'data: ' .
                                            json_encode([
                                                'id' => uniqid(),
                                                'choices' => [['delta' => ['content' => $text]]]
                                            ]) .
                                            "\n\n";
                                        if (ob_get_level() > 0) {
                                            ob_flush();
                                        }
                                        flush();
                                        $this->stream_running = false;
                                    }
                                }
                            }

                            if (isset($parsed['usage'])) {
                                $this->stream_response->result->usage->input_tokens +=
                                    $parsed['usage']['input_tokens'] ?? 0;
                                $this->stream_response->result->usage->cache_creation_input_tokens +=
                                    $parsed['usage']['cache_creation_input_tokens'] ?? 0;
                                $this->stream_response->result->usage->cache_read_input_tokens +=
                                    $parsed['usage']['cache_read_input_tokens'] ?? 0;
                                $this->stream_response->result->usage->output_tokens +=
                                    $parsed['usage']['output_tokens'] ?? 0;
                                $this->log(
                                    'ADDED USAGE [' .
                                        json_encode($parsed['usage']) .
                                        ' - overall cur ' .
                                        json_encode($this->stream_response->result->usage) .
                                        ']',
                                    'stream usage'
                                );
                            }

                            if (isset($parsed['type']) && $parsed['type'] === 'message_stop') {
                                // only send [DONE] if not pause_turn (because stream continues)
                                if ($this->stream_response->result->stop_reason !== 'pause_turn') {
                                    // finally sleep to ensure all chunks arrive
                                    sleep(2);
                                    echo "data: [DONE]\n\n";
                                    if (ob_get_level() > 0) {
                                        ob_flush();
                                    }
                                    flush();
                                    $this->stream_running = false;
                                }
                            }

                            // send SSE keepalive comment for non-text events (tool calls, thinking, etc.)
                            // to prevent client/infrastructure timeout during long-running tool use
                            if ($this->stream_running) {
                                echo ": keepalive\n\n";
                                if (ob_get_level() > 0) {
                                    ob_flush();
                                }
                                flush();
                            }
                        }

                        if ($line === '') {
                            $this->stream_event = null;
                            $this->stream_buffer_data = '';
                            continue;
                        }
                    }
                }

                return strlen($chunk);
            };
        }

        if ($this->name === 'openai' || $this->name === 'lmstudio') {
            // mimic non stream result
            $this->stream_response = (object) [
                'result' => (object) [
                    'id' => null,
                    'output' => [
                        (object) [
                            'type' => 'message',
                            'content' => [
                                (object) [
                                    'type' => 'output_text',
                                    'text' => ''
                                ]
                            ]
                        ]
                    ],
                    'usage' => (object) [
                        'input_tokens' => 0,
                        'cache_creation_input_tokens' => 0,
                        'cache_read_input_tokens' => 0,
                        'output_tokens' => 0
                    ]
                ]
            ];

            $this->stream_callback = function ($chunk) {
                /*
                echo $chunk;
                return strlen($chunk);
                */

                $this->log($chunk, 'chunk');
                $this->stream_buffer_in .= $chunk;

                // check if chunk is full json
                if (json_decode($chunk, true) !== null) {
                    $parsed = json_decode($chunk, true);
                    if (isset($parsed['error']) && isset($parsed['error']['message'])) {
                        $this->stream_response->result->error = (object) [
                            'message' => self::extractErrorMessage($parsed['error'])
                        ];
                    }
                }

                // parse line by line
                if (strpos($this->stream_buffer_in, "\n") !== false) {
                    while (($pos = strpos($this->stream_buffer_in, "\n")) !== false) {
                        $line = rtrim(substr($this->stream_buffer_in, 0, $pos), "\r");
                        $this->stream_buffer_in = substr($this->stream_buffer_in, $pos + 1);

                        if (strpos($line, 'event: ') === 0) {
                            $this->stream_event = substr($line, 7);
                            continue;
                        }

                        if (strpos($line, 'data: ') === 0) {
                            $dataLine = substr($line, 6);
                            $this->stream_buffer_data =
                                $this->stream_buffer_data === ''
                                    ? $dataLine
                                    : $this->stream_buffer_data . "\n" . $dataLine;
                            continue;
                        }

                        if ($line === '' && $this->stream_event !== null && $this->stream_buffer_data !== '') {
                            $parsed = json_decode($this->stream_buffer_data, true);
                            $this->stream_running = true;

                            if (
                                isset($parsed['type']) &&
                                $parsed['type'] === 'response.output_item.added' &&
                                isset($parsed['item']['type']) &&
                                $parsed['item']['type'] === 'message'
                            ) {
                                $this->stream_first_text_sent = false;
                            }

                            // response.reasoning_summary_text.delta = OpenAI o3 condensed reasoning
                            // response.reasoning_text.delta = LM Studio native reasoning (e.g. Qwen3.5)
                            if (
                                isset($parsed['type']) &&
                                ($parsed['type'] === 'response.reasoning_summary_text.delta' ||
                                    $parsed['type'] === 'response.reasoning_text.delta')
                            ) {
                                if (isset($parsed['delta']) && $parsed['delta'] !== '') {
                                    echo "event: reasoning\n";
                                    echo 'data: ' . json_encode(['delta' => $parsed['delta']]) . "\n\n";
                                    if (ob_get_level() > 0) {
                                        ob_flush();
                                    }
                                    flush();
                                    $this->stream_running = false;
                                }
                            }

                            if (isset($parsed['type']) && $parsed['type'] === 'response.output_text.delta') {
                                if (isset($parsed['delta'])) {
                                    $raw = $parsed['delta'];

                                    // split delta into normal text and <think>...</think> reasoning parts
                                    $pending = $this->stream_think_tag_buf . $raw;
                                    $this->stream_think_tag_buf = '';
                                    $normal_text = '';
                                    $reasoning_text = '';

                                    while ($pending !== '') {
                                        $tag = $this->stream_in_think ? '<\/think>' : '<think>';
                                        $pos = strpos($pending, $this->stream_in_think ? '</think>' : '<think>');
                                        if ($pos !== false) {
                                            if ($this->stream_in_think) {
                                                $reasoning_text .= substr($pending, 0, $pos);
                                                $this->stream_in_think = false;
                                                $pending = substr($pending, $pos + strlen('</think>'));
                                            } else {
                                                $normal_text .= substr($pending, 0, $pos);
                                                $this->stream_in_think = true;
                                                $pending = substr($pending, $pos + strlen('<think>'));
                                            }
                                        } else {
                                            // no closing/opening tag found; buffer partial tag at end
                                            $max_len = strlen($this->stream_in_think ? '</think>' : '<think>') - 1;
                                            $buf_len = 0;
                                            for ($i = min($max_len, strlen($pending)); $i >= 1; $i--) {
                                                $tail = substr($pending, -$i);
                                                if (strpos('<think>', $tail) === 0 || strpos('</think>', $tail) === 0) {
                                                    $buf_len = $i;
                                                    break;
                                                }
                                            }
                                            if ($buf_len > 0) {
                                                $this->stream_think_tag_buf = substr($pending, -$buf_len);
                                                $pending = substr($pending, 0, strlen($pending) - $buf_len);
                                            }
                                            if ($this->stream_in_think) {
                                                $reasoning_text .= $pending;
                                            } else {
                                                $normal_text .= $pending;
                                            }
                                            break;
                                        }
                                    }

                                    if ($reasoning_text !== '') {
                                        echo "event: reasoning\n";
                                        echo 'data: ' . json_encode(['delta' => $reasoning_text]) . "\n\n";
                                        if (ob_get_level() > 0) {
                                            ob_flush();
                                        }
                                        flush();
                                        $this->stream_running = false;
                                    }

                                    if ($normal_text !== '') {
                                        $existing_text = $this->stream_response->result->output[0]->content[0]->text;

                                        // strip leading newlines from the very first text chunk
                                        $normal_text = $this->normalizeStreamTextDelta(
                                            $normal_text,
                                            $existing_text,
                                            !$this->stream_first_text_sent
                                        );
                                    }

                                    if ($normal_text === '') {
                                        $this->stream_buffer_data = '';
                                        $this->stream_event = null;
                                        continue;
                                    }
                                    $this->stream_first_text_sent = true;

                                    $this->stream_response->result->output[0]->content[0]->text .= $normal_text;

                                    echo 'data: ' .
                                        json_encode([
                                            'id' => uniqid(),
                                            'choices' => [['delta' => ['content' => $normal_text]]]
                                        ]) .
                                        "\n\n";
                                    if (ob_get_level() > 0) {
                                        ob_flush();
                                    }
                                    flush();
                                    $this->stream_running = false;
                                }
                            }

                            if (isset($parsed['response']) && isset($parsed['response']['usage'])) {
                                $this->stream_response->result->usage->input_tokens +=
                                    $parsed['response']['usage']['input_tokens'] ?? null;
                                $this->stream_response->result->usage->cache_creation_input_tokens +=
                                    $parsed['response']['usage']['input_tokens_details']['cached_tokens'] ?? null;
                                $this->stream_response->result->usage->cache_read_input_tokens += 0;
                                $this->stream_response->result->usage->output_tokens +=
                                    $parsed['response']['usage']['output_tokens'] ?? null;
                            }

                            if (isset($parsed['type']) && $parsed['type'] === 'response.completed') {
                                $this->stream_response->result->id = $parsed['response']['id'] ?? null;
                                // carry over full output items (incl. function_call) for the tool loop
                                if (isset($parsed['response']['output']) && is_array($parsed['response']['output'])) {
                                    $this->stream_response->result->output = json_decode(
                                        json_encode($parsed['response']['output'])
                                    );
                                }
                                // finally sleep to ensure all chunks arrive
                                sleep(2);
                                echo "data: [DONE]\n\n";
                                if (ob_get_level() > 0) {
                                    ob_flush();
                                }
                                flush();
                                $this->stream_running = false;
                            }

                            if (isset($parsed['type']) && $parsed['type'] === 'response.failed') {
                                $this->stream_response->result->error = (object) [
                                    'message' => isset($parsed['response']['error'])
                                        ? self::extractErrorMessage($parsed['response']['error'])
                                        : 'unknown error'
                                ];
                                echo "data: [DONE]\n\n";
                                if (ob_get_level() > 0) {
                                    ob_flush();
                                }
                                flush();
                                $this->stream_running = false;
                            }

                            // send SSE keepalive comment for non-text events (tool calls, MCP results, etc.)
                            // to prevent client/infrastructure timeout during long-running agentic runs
                            if ($this->stream_running) {
                                echo ": keepalive\n\n";
                                if (ob_get_level() > 0) {
                                    ob_flush();
                                }
                                flush();
                            }
                        }

                        if ($line === '') {
                            $this->stream_event = null;
                            $this->stream_buffer_data = '';
                            continue;
                        }
                    }
                }

                return strlen($chunk);
            };
        }

        if (
            $this->name === 'openrouter' ||
            $this->name === 'llamacpp' ||
            $this->name === 'nvidia' ||
            $this->name === 'codex'
        ) {
            // mimic non-stream result (chat completions format)
            $this->stream_response = (object) [
                'result' => (object) [
                    'choices' => [
                        (object) [
                            'finish_reason' => null,
                            'message' => (object) [
                                'role' => 'assistant',
                                'content' => '',
                                'tool_calls' => []
                            ]
                        ]
                    ],
                    'usage' => (object) [
                        'prompt_tokens' => 0,
                        'completion_tokens' => 0
                    ]
                ]
            ];

            $this->stream_reasoning_buffer = '';
            $this->resetToolCallStripState();

            $this->stream_callback = function ($chunk) {
                $this->log($chunk, 'chunk');
                $this->stream_buffer_in .= $chunk;

                // check if chunk is full json (error)
                if (json_decode($chunk, true) !== null) {
                    $parsed = json_decode($chunk, true);
                    if (isset($parsed['error']) && isset($parsed['error']['message'])) {
                        $this->stream_response->result->error = (object) [
                            'message' => self::extractErrorMessage($parsed['error'])
                        ];
                    }
                }

                if (strpos($this->stream_buffer_in, "\n") !== false) {
                    while (($pos = strpos($this->stream_buffer_in, "\n")) !== false) {
                        $line = rtrim(substr($this->stream_buffer_in, 0, $pos), "\r");
                        $this->stream_buffer_in = substr($this->stream_buffer_in, $pos + 1);

                        $this->detectWhitespaceEndlessLoop();

                        if (strpos($line, 'data: ') !== 0) {
                            continue;
                        }

                        $dataLine = substr($line, 6);

                        if ($dataLine === '[DONE]') {
                            sleep(2);
                            echo "data: [DONE]\n\n";
                            if (ob_get_level() > 0) {
                                ob_flush();
                            }
                            flush();
                            continue;
                        }

                        $parsed = json_decode($dataLine, true);
                        if ($parsed === null) {
                            continue;
                        }

                        if (isset($parsed['error'])) {
                            $this->stream_response->result->error = (object) [
                                'message' => self::extractErrorMessage($parsed['error'])
                            ];
                            continue;
                        }

                        if (isset($parsed['usage'])) {
                            $this->stream_response->result->usage->prompt_tokens +=
                                $parsed['usage']['prompt_tokens'] ?? 0;
                            $this->stream_response->result->usage->completion_tokens +=
                                $parsed['usage']['completion_tokens'] ?? 0;
                        }

                        // capture finish_reason
                        if (
                            isset($parsed['choices'][0]['finish_reason']) &&
                            $parsed['choices'][0]['finish_reason'] !== null
                        ) {
                            $this->stream_response->result->choices[0]->finish_reason =
                                $parsed['choices'][0]['finish_reason'];
                        }

                        if (!isset($parsed['choices'][0]['delta'])) {
                            continue;
                        }

                        $delta = $parsed['choices'][0]['delta'];

                        // tool_calls delta
                        if (isset($delta['tool_calls'])) {
                            foreach ($delta['tool_calls'] as $tc_delta) {
                                $idx = $tc_delta['index'] ?? 0;
                                $tool_calls = &$this->stream_response->result->choices[0]->message->tool_calls;
                                while (count($tool_calls) <= $idx) {
                                    $tool_calls[] = [
                                        'id' => '',
                                        'type' => 'function',
                                        'function' => ['name' => '', 'arguments' => '']
                                    ];
                                }
                                if (isset($tc_delta['id'])) {
                                    $tool_calls[$idx]['id'] .= $tc_delta['id'];
                                }
                                if (isset($tc_delta['function']['name'])) {
                                    $tool_calls[$idx]['function']['name'] .= $tc_delta['function']['name'];
                                }
                                if (isset($tc_delta['function']['arguments'])) {
                                    $tool_calls[$idx]['function']['arguments'] .= $tc_delta['function']['arguments'];
                                }
                            }
                            echo ": keepalive\n\n";
                            if (ob_get_level() > 0) {
                                ob_flush();
                            }
                            flush();
                            continue;
                        }

                        // handle reasoning delta (OpenRouter sends reasoning as separate field)
                        $reasoning = $delta['reasoning'] ?? ($delta['reasoning_content'] ?? null);
                        if ($reasoning !== null && $reasoning !== '') {
                            // always keep full reasoning (including tool_call XML) in buffer
                            // for the reasoning_buffer parser to extract tool calls from
                            $this->stream_reasoning_buffer .= $reasoning;
                            // strip tool_call XML from what's streamed to the user, but keep
                            // legitimate whitespace (newlines between paragraphs, etc.)
                            $reasoning_visible = $this->stripToolCallBlocks($reasoning);
                            if ($reasoning_visible !== '') {
                                $this->stream_running = true;
                                echo "event: reasoning\n";
                                echo 'data: ' . json_encode(['delta' => $reasoning_visible]) . "\n\n";
                                if (ob_get_level() > 0) {
                                    ob_flush();
                                }
                                flush();
                            }
                        }

                        $raw = $delta['content'] ?? null;
                        if ($raw === null || $raw === '') {
                            continue;
                        }

                        // handle think tags
                        $pending = $this->stream_think_tag_buf . $raw;
                        $this->stream_think_tag_buf = '';
                        $normal_text = '';
                        $reasoning_text = '';

                        while ($pending !== '') {
                            $pos = strpos($pending, $this->stream_in_think ? '</think>' : '<think>');
                            if ($pos !== false) {
                                if ($this->stream_in_think) {
                                    $reasoning_text .= substr($pending, 0, $pos);
                                    $this->stream_in_think = false;
                                    $pending = substr($pending, $pos + strlen('</think>'));
                                } else {
                                    $normal_text .= substr($pending, 0, $pos);
                                    $this->stream_in_think = true;
                                    $pending = substr($pending, $pos + strlen('<think>'));
                                }
                            } else {
                                $max_len = strlen($this->stream_in_think ? '</think>' : '<think>') - 1;
                                $buf_len = 0;
                                for ($i = min($max_len, strlen($pending)); $i >= 1; $i--) {
                                    $tail = substr($pending, -$i);
                                    if (strpos('<think>', $tail) === 0 || strpos('</think>', $tail) === 0) {
                                        $buf_len = $i;
                                        break;
                                    }
                                }
                                if ($buf_len > 0) {
                                    $this->stream_think_tag_buf = substr($pending, -$buf_len);
                                    $pending = substr($pending, 0, strlen($pending) - $buf_len);
                                }
                                if ($this->stream_in_think) {
                                    $reasoning_text .= $pending;
                                } else {
                                    $normal_text .= $pending;
                                }
                                break;
                            }
                        }

                        // keep full reasoning_text in buffer for the parser to extract tool calls from
                        if ($reasoning_text !== '') {
                            $this->stream_reasoning_buffer .= $reasoning_text;
                        }

                        // strip tool_call XML from user-visible reasoning stream
                        $reasoning_visible = $reasoning_text !== '' ? $this->stripToolCallBlocks($reasoning_text) : '';
                        if ($reasoning_visible !== '') {
                            echo "event: reasoning\n";
                            echo 'data: ' . json_encode(['delta' => $reasoning_visible]) . "\n\n";
                            if (ob_get_level() > 0) {
                                ob_flush();
                            }
                            flush();
                        }

                        if ($normal_text !== '') {
                            // also buffer content for tool call extraction (qwen3 may emit
                            // tool_call XML directly in content, not just reasoning)
                            $this->stream_reasoning_buffer .= $normal_text;
                            // strip tool_call XML from user-visible content
                            $normal_text = $this->stripToolCallBlocks($normal_text);
                        }

                        if ($normal_text !== '') {
                            $existing_text = $this->stream_response->result->choices[0]->message->content;
                            $normal_text = $this->normalizeStreamTextDelta(
                                $normal_text,
                                $existing_text,
                                !$this->stream_first_text_sent
                            );
                        }

                        if ($normal_text === '') {
                            continue;
                        }

                        $this->stream_first_text_sent = true;
                        $this->stream_response->result->choices[0]->message->content .= $normal_text;
                        $this->stream_running = true;

                        echo 'data: ' .
                            json_encode([
                                'id' => uniqid(),
                                'choices' => [['delta' => ['content' => $normal_text]]]
                            ]) .
                            "\n\n";
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    }
                }

                return strlen($chunk);
            };
        }

        if ($this->name === 'google') {
            // mimic non stream result
            $this->stream_response = (object) [
                'result' => (object) [
                    'candidates' => [
                        (object) [
                            'content' => (object) [
                                'parts' => []
                            ]
                        ]
                    ],
                    'usageMetadata' => (object) [
                        'promptTokenCount' => 0,
                        'candidatesTokenCount' => 0
                    ]
                ]
            ];

            $this->stream_callback = function ($chunk) {
                $this->log($chunk, 'chunk');
                $this->stream_buffer_in .= $chunk;

                while (($pos = strpos($this->stream_buffer_in, "\n")) !== false) {
                    $line = rtrim(substr($this->stream_buffer_in, 0, $pos), "\r");
                    $this->stream_buffer_in = substr($this->stream_buffer_in, $pos + 1);

                    if (strpos($line, 'data: ') === 0) {
                        $dataLine = substr($line, 6);
                        if ($dataLine === '') {
                            continue;
                        }
                        $parsed = json_decode($dataLine, true);
                        if ($parsed === null) {
                            continue;
                        }

                        // error
                        if (isset($parsed['error'])) {
                            $this->stream_response->result->error = (object) [
                                'message' => self::extractErrorMessage($parsed['error'])
                            ];
                            continue;
                        }

                        // text delta
                        if (isset($parsed['candidates'][0]['content']['parts'])) {
                            foreach ($parsed['candidates'][0]['content']['parts'] as $part) {
                                if (isset($part['text']) && !empty($part['thought'])) {
                                    // thinking/reasoning — send as separate event, don't accumulate
                                    $this->stream_running = true;
                                    echo "event: reasoning\n";
                                    echo 'data: ' . json_encode(['delta' => $part['text']]) . "\n\n";
                                    if (ob_get_level() > 0) {
                                        ob_flush();
                                    }
                                    flush();
                                } elseif (isset($part['text'])) {
                                    $text = $part['text'];
                                    // accumulate (raw, before normalization)
                                    $parts = &$this->stream_response->result->candidates[0]->content->parts;
                                    if (empty($parts) || !isset(end($parts)->text)) {
                                        $parts[] = (object) ['text' => $text];
                                    } else {
                                        $parts[count($parts) - 1]->text .= $text;
                                    }
                                    // normalize
                                    $existing_text = $parts[count($parts) - 1]->text;
                                    $text = $this->normalizeStreamTextDelta(
                                        $text,
                                        substr($existing_text, 0, -strlen($text)),
                                        !$this->stream_first_text_sent
                                    );
                                    if ($text === '') {
                                        continue;
                                    }
                                    $this->stream_first_text_sent = true;
                                    // echo SSE
                                    $this->stream_running = true;
                                    echo 'data: ' .
                                        json_encode([
                                            'id' => uniqid(),
                                            'choices' => [['delta' => ['content' => $text]]]
                                        ]) .
                                        "\n\n";
                                    if (ob_get_level() > 0) {
                                        ob_flush();
                                    }
                                    flush();
                                }
                                if (isset($part['functionCall'])) {
                                    $parts = &$this->stream_response->result->candidates[0]->content->parts;
                                    $fc = $part['functionCall'];
                                    // ensure args is always an object (empty args would serialize as [] otherwise)
                                    if (!isset($fc['args']) || (is_array($fc['args']) && empty($fc['args']))) {
                                        $fc['args'] = new \stdClass();
                                    }
                                    $partObj = ['functionCall' => (object) $fc];
                                    if (isset($part['thoughtSignature'])) {
                                        $partObj['thoughtSignature'] = $part['thoughtSignature'];
                                    }
                                    $parts[] = (object) $partObj;
                                    if ($this->stream_running) {
                                        echo ": keepalive\n\n";
                                        if (ob_get_level() > 0) {
                                            ob_flush();
                                        }
                                        flush();
                                    }
                                }
                            }
                        }

                        // usage / finish
                        if (isset($parsed['usageMetadata'])) {
                            if (isset($parsed['usageMetadata']['promptTokenCount'])) {
                                $this->stream_response->result->usageMetadata->promptTokenCount =
                                    $parsed['usageMetadata']['promptTokenCount'];
                            }
                            if (isset($parsed['usageMetadata']['candidatesTokenCount'])) {
                                $this->stream_response->result->usageMetadata->candidatesTokenCount =
                                    $parsed['usageMetadata']['candidatesTokenCount'];
                            }
                        }
                        if (isset($parsed['candidates'][0]['finishReason'])) {
                            sleep(2);
                            echo "data: [DONE]\n\n";
                            if (ob_get_level() > 0) {
                                ob_flush();
                            }
                            flush();
                            $this->stream_running = false;
                        }
                    }
                }

                return strlen($chunk);
            };
        }

        if (!(headers_sent() || ob_get_length() > 0)) {
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');
            header('Cache-Control: no-cache, no-transform');
        }
        $initial_ob_level = ob_get_level();
        while (ob_get_level() > $initial_ob_level) {
            ob_end_clean();
        }
        // set php settings
        if (!(headers_sent() || ob_get_length() > 0)) {
            try {
                ini_set('zlib.output_compression', '0');
            } catch (\ValueError $e) {
            }
            try {
                ini_set('output_buffering', '0');
            } catch (\ValueError $e) {
            }
            try {
                ini_set('implicit_flush', '1');
            } catch (\ValueError $e) {
            }
        }
        // 2k padding (for browsers)
        ob_implicit_flush(true);
        echo ': pad ' . str_repeat(' ', 2048) . "\n\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();

        return $this->stream_callback;
    }
}

class ai_openai extends aihelper
{
    public ?string $provider = 'OpenAI';

    public ?string $title = 'OpenAI';

    public ?string $name = 'openai';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 260"><path d="M239.184 106.203a64.72 64.72 0 0 0-5.576-53.103C219.452 28.459 191 15.784 163.213 21.74A65.586 65.586 0 0 0 52.096 45.22a64.72 64.72 0 0 0-43.23 31.36c-14.31 24.602-11.061 55.634 8.033 76.74a64.67 64.67 0 0 0 5.525 53.102c14.174 24.65 42.644 37.324 70.446 31.36a64.72 64.72 0 0 0 48.754 21.744c28.481.025 53.714-18.361 62.414-45.481a64.77 64.77 0 0 0 43.229-31.36c14.137-24.558 10.875-55.423-8.083-76.483m-97.56 136.338a48.4 48.4 0 0 1-31.105-11.255l1.535-.87l51.67-29.825a8.6 8.6 0 0 0 4.247-7.367v-72.85l21.845 12.636c.218.111.37.32.409.563v60.367c-.056 26.818-21.783 48.545-48.601 48.601M37.158 197.93a48.35 48.35 0 0 1-5.781-32.589l1.534.921l51.722 29.826a8.34 8.34 0 0 0 8.441 0l63.181-36.425v25.221a.87.87 0 0 1-.358.665l-52.335 30.184c-23.257 13.398-52.97 5.431-66.404-17.803M23.549 85.38a48.5 48.5 0 0 1 25.58-21.333v61.39a8.29 8.29 0 0 0 4.195 7.316l62.874 36.272l-21.845 12.636a.82.82 0 0 1-.767 0L41.353 151.53c-23.211-13.454-31.171-43.144-17.804-66.405zm179.466 41.695l-63.08-36.63L161.73 77.86a.82.82 0 0 1 .768 0l52.233 30.184a48.6 48.6 0 0 1-7.316 87.635v-61.391a8.54 8.54 0 0 0-4.4-7.213m21.742-32.69l-1.535-.922l-51.619-30.081a8.39 8.39 0 0 0-8.492 0L99.98 99.808V74.587a.72.72 0 0 1 .307-.665l52.233-30.133a48.652 48.652 0 0 1 72.236 50.391zM88.061 139.097l-21.845-12.585a.87.87 0 0 1-.41-.614V65.685a48.652 48.652 0 0 1 79.757-37.346l-1.535.87l-51.67 29.825a8.6 8.6 0 0 0-4.246 7.367zm11.868-25.58L128.067 97.3l28.188 16.218v32.434l-28.086 16.218l-28.188-16.218z"/></svg>
    SVG;

    protected ?string $url = 'https://api.openai.com/v1';

    public ?bool $supports_mcp_remote = true;

    public ?bool $supports_stream = true;

    public array $models = [
        [
            'name' => 'gpt-3.5-turbo',
            'context_length' => 16384,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0.0000005, 'input_cached' => 0.0000005, 'output' => 0.0000015],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-3.5-turbo-0125',
            'context_length' => 16384,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0.0000005, 'input_cached' => 0.0000005, 'output' => 0.0000015],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-3.5-turbo-1106',
            'context_length' => 16384,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0.000001, 'input_cached' => 0.000001, 'output' => 0.000002],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4',
            'context_length' => 8192,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0.00003, 'input_cached' => 0.00003, 'output' => 0.00006],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4-0613',
            'context_length' => 8192,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0.00003, 'input_cached' => 0.00003, 'output' => 0.00006],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4-turbo',
            'context_length' => 128000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0.00001, 'input_cached' => 0.00001, 'output' => 0.00003],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4-turbo-2024-04-09',
            'context_length' => 128000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0.00001, 'input_cached' => 0.00001, 'output' => 0.00003],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1',
            'context_length' => 1047576,
            'max_output_tokens' => 32768,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000005, 'output' => 0.000008],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1-2025-04-14',
            'context_length' => 1047576,
            'max_output_tokens' => 32768,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000005, 'output' => 0.000008],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1-mini',
            'context_length' => 1047576,
            'max_output_tokens' => 32768,
            'costs' => ['input' => 0.0000004, 'input_cached' => 0.0000001, 'output' => 0.0000016],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1-mini-2025-04-14',
            'context_length' => 1047576,
            'max_output_tokens' => 32768,
            'costs' => ['input' => 0.0000004, 'input_cached' => 0.0000001, 'output' => 0.0000016],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1-nano',
            'context_length' => 1047576,
            'max_output_tokens' => 32768,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.000000025, 'output' => 0.0000004],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1-nano-2025-04-14',
            'context_length' => 1047576,
            'max_output_tokens' => 32768,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.000000025, 'output' => 0.0000004],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o',
            'context_length' => 128000,
            'max_output_tokens' => 16384,
            'costs' => ['input' => 0.0000025, 'input_cached' => 0.00000125, 'output' => 0.00001],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-2024-05-13',
            'context_length' => 128000,
            'max_output_tokens' => 16384,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.000005, 'output' => 0.000015],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-2024-08-06',
            'context_length' => 128000,
            'max_output_tokens' => 16384,
            'costs' => ['input' => 0.0000025, 'input_cached' => 0.00000125, 'output' => 0.00001],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-2024-11-20',
            'context_length' => 128000,
            'max_output_tokens' => 16384,
            'costs' => ['input' => 0.0000025, 'input_cached' => 0.00000125, 'output' => 0.00001],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-mini',
            'context_length' => 128000,
            'max_output_tokens' => 16384,
            'costs' => ['input' => 0.00000015, 'input_cached' => 0.000000075, 'output' => 0.0000006],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-mini-2024-07-18',
            'context_length' => 128000,
            'max_output_tokens' => 16384,
            'costs' => ['input' => 0.00000015, 'input_cached' => 0.000000075, 'output' => 0.0000006],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'gpt-5-2025-08-07',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-chat-latest',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-codex',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-mini',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000025, 'input_cached' => 0.000000025, 'output' => 0.000002],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'gpt-5-mini-2025-08-07',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000025, 'input_cached' => 0.000000025, 'output' => 0.000002],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-nano',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000005, 'input_cached' => 0.000000005, 'output' => 0.0000004],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-nano-2025-08-07',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000005, 'input_cached' => 0.000000005, 'output' => 0.0000004],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-pro',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.000015, 'output' => 0.00012],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-pro-2025-10-06',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.000015, 'output' => 0.00012],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1-2025-11-13',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1-chat-latest',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1-codex',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1-codex-max',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1-codex-mini',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000025, 'input_cached' => 0.000000025, 'output' => 0.000002],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2-2025-12-11',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2-chat-latest',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2-codex',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2-pro',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.000021, 'input_cached' => 0.000021, 'output' => 0.000168],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2-pro-2025-12-11',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.000021, 'input_cached' => 0.000021, 'output' => 0.000168],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.3-chat-latest',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.3-codex',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4',
            'context_length' => 1050000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-2026-03-05',
            'context_length' => 1050000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-mini',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.0000006, 'input_cached' => 0.00000006, 'output' => 0.0000024],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-mini-2026-03-17',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.0000006, 'input_cached' => 0.00000006, 'output' => 0.0000024],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-nano',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.00000001, 'output' => 0.0000004],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-nano-2026-03-17',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.00000001, 'output' => 0.0000004],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-pro',
            'context_length' => 1050000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.000021, 'input_cached' => 0.000021, 'output' => 0.000168],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-pro-2026-03-05',
            'context_length' => 1050000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.000021, 'input_cached' => 0.000021, 'output' => 0.000168],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.5',
            'context_length' => 1050000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.0000005, 'output' => 0.00003],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.5-2026-04-23',
            'context_length' => 1050000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.0000005, 'output' => 0.00003],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.5-pro',
            'context_length' => 1050000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00003, 'input_cached' => 0.00003, 'output' => 0.00018],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.5-pro-2026-04-23',
            'context_length' => 1050000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00003, 'input_cached' => 0.00003, 'output' => 0.00018],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o1',
            'context_length' => 200000,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.0000075, 'output' => 0.00006],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o1-2024-12-17',
            'context_length' => 200000,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.0000075, 'output' => 0.00006],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o1-pro',
            'context_length' => 200000,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.00015, 'input_cached' => 0.00015, 'output' => 0.0006],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o1-pro-2025-03-19',
            'context_length' => 200000,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.00015, 'input_cached' => 0.00015, 'output' => 0.0006],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3',
            'context_length' => 200000,
            'max_output_tokens' => 100000,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000005, 'output' => 0.000008],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3-2025-04-16',
            'context_length' => 200000,
            'max_output_tokens' => 100000,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000005, 'output' => 0.000008],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3-mini',
            'context_length' => 200000,
            'max_output_tokens' => 100000,
            'costs' => ['input' => 0.0000011, 'input_cached' => 0.00000055, 'output' => 0.0000044],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3-mini-2025-01-31',
            'context_length' => 200000,
            'max_output_tokens' => 100000,
            'costs' => ['input' => 0.0000011, 'input_cached' => 0.00000055, 'output' => 0.0000044],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3-pro',
            'context_length' => 200000,
            'max_output_tokens' => 100000,
            'costs' => ['input' => 0.00002, 'input_cached' => 0.00002, 'output' => 0.00008],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3-pro-2025-06-10',
            'context_length' => 200000,
            'max_output_tokens' => 100000,
            'costs' => ['input' => 0.00002, 'input_cached' => 0.00002, 'output' => 0.00008],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o4-mini',
            'context_length' => 200000,
            'max_output_tokens' => 100000,
            'costs' => ['input' => 0.0000011, 'input_cached' => 0.000000275, 'output' => 0.0000044],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o4-mini-2025-04-16',
            'context_length' => 200000,
            'max_output_tokens' => 100000,
            'costs' => ['input' => 0.0000011, 'input_cached' => 0.000000275, 'output' => 0.0000044],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        // image generation — costs per image (1024x1024 medium quality where applicable)
        [
            'name' => 'gpt-image-1',
            'supports_tools' => false,
            'costs' => ['image' => 0.042],
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-image-1-mini',
            'supports_tools' => false,
            'costs' => ['image' => 0.011],
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-image-1.5',
            'supports_tools' => false,
            'costs' => ['image' => 0.04],
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-image-2',
            'supports_tools' => false,
            'costs' => ['image' => 0.04],
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'chatgpt-image-latest',
            'supports_tools' => false,
            'costs' => ['image' => 0.04],
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        // audio (TTS) — costs per input character
        [
            'name' => 'tts-1',
            'supports_tools' => false,
            'costs' => ['audio' => 0.000015],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => true,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'tts-1-hd',
            'supports_tools' => false,
            'costs' => ['audio' => 0.00003],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => true,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-mini-tts',
            'supports_tools' => false,
            'costs' => ['audio' => 0.000015],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => true,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ]
    ];

    public function fetchModels(): array
    {
        $models = [];
        $response = __::curl(
            url: $this->url . '/models',
            method: 'GET',
            headers: [
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            timeout: $this->timeout
        );
        $this->log($response);
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->data ?? null) &&
            is_array($response->result->data)
        ) {
            foreach ($response->result->data as $models__value) {
                if (__::x($models__value?->id ?? null)) {
                    $name = $models__value->id;
                    if (strpos($name, '-preview') !== false) {
                        continue;
                    }
                    // exclude dated gpt-image-2 variants (e.g. gpt-image-2-2026-04-21)
                    if (strpos($name, 'gpt-image-2-') !== false) {
                        continue;
                    }
                    if (
                        in_array($name, [
                            'chat-latest',
                            'gpt-5-search-api',
                            'gpt-5-search-api-2025-10-14',
                            'o3-deep-research',
                            'o3-deep-research-2025-06-26',
                            'o4-mini-deep-research',
                            'o4-mini-deep-research-2025-06-26',
                            'gpt-realtime',
                            'gpt-realtime-2',
                            'gpt-realtime-2025-08-28',
                            'gpt-realtime-1.5',
                            'gpt-realtime-mini',
                            'gpt-realtime-mini-2025-10-06',
                            'gpt-realtime-mini-2025-12-15',
                            'gpt-realtime-translate',
                            'gpt-realtime-whisper',
                            'gpt-audio',
                            'gpt-audio-2025-08-28',
                            'gpt-audio-1.5',
                            'gpt-audio-mini',
                            'gpt-audio-mini-2025-10-06',
                            'gpt-audio-mini-2025-12-15',
                            'gpt-4o-transcribe',
                            'gpt-4o-transcribe-diarize',
                            'gpt-4o-mini-transcribe',
                            'gpt-4o-mini-transcribe-2025-03-20',
                            'gpt-4o-mini-transcribe-2025-12-15',
                            'gpt-4o-mini-tts-2025-03-20',
                            'gpt-4o-mini-tts-2025-12-15',
                            'gpt-3.5-turbo-instruct',
                            'gpt-3.5-turbo-instruct-0914',
                            'gpt-3.5-turbo-16k',
                            'davinci-002',
                            'babbage-002',
                            'dall-e-3',
                            'dall-e-2',
                            'sora-2',
                            'sora-2-pro',
                            'text-embedding-3-small',
                            'text-embedding-3-large',
                            'text-embedding-ada-002',
                            'omni-moderation-latest',
                            'omni-moderation-2024-09-26',
                            'tts-1-1106',
                            'tts-1-hd-1106',
                            'whisper-1'
                        ])
                    ) {
                        continue;
                    }
                    $entry = ['name' => $name, 'context_length' => 128000];
                    foreach ($this->models as $definedModel) {
                        if ($definedModel['name'] === $name) {
                            // merge static caps (supports_*/costs/…) into the
                            // dynamic entry so capability metadata survives fetchModels()
                            $entry = array_merge($definedModel, ['name' => $name]);
                            if (!isset($entry['context_length'])) {
                                $entry['context_length'] = 128000;
                            }
                            break;
                        }
                    }
                    $models[] = $entry;
                }
            }
        }
        return $models;
    }

    protected function bringPromptInFormat(string $prompt, mixed $files = null): array
    {
        $content = [];

        // add text content
        $content[] = [
            'type' => 'input_text',
            'text' => $prompt
        ];

        // add files
        if (__::x($files ?? null)) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $files__value) {
                if (!file_exists($files__value)) {
                    continue;
                }
                $mime = mime_content_type($files__value);
                $b64 = base64_encode(file_get_contents($files__value));

                if (stripos($mime, 'pdf') !== false || $mime === 'application/pdf') {
                    $content[] = [
                        'type' => 'input_file',
                        'filename' => 'attachment.pdf',
                        'file_data' => 'data:' . $mime . ';base64,' . $b64
                    ];
                } elseif (strpos($mime, 'image/') === 0) {
                    $content[] = [
                        'type' => 'input_image',
                        'image_url' => 'data:' . $mime . ';base64,' . $b64
                    ];
                } else {
                    $content[] = [
                        'type' => 'input_file',
                        'filename' => 'attachment.bin',
                        'file_data' => 'data:' . $mime . ';base64,' . $b64
                    ];
                }
            }
        }

        return [
            'role' => 'user',
            'content' => $content
        ];
    }

    protected function addResponseToSession(mixed $response): void
    {
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->output ?? null)
        ) {
            foreach ($response->result->output as $output__value) {
                if (!__::x($output__value?->type ?? null)) {
                    continue;
                }

                if ($output__value->type === 'message' && __::x($output__value?->content ?? null)) {
                    $content = $output__value->content;

                    // Strip <think>...</think> blocks before storing in history
                    // for single-turn reasoning models (e.g. QwQ). Skip the
                    // strip for the Qwen thinking lineage (3.5+ and 4+) which
                    // is trained to consume prior-turn traces for agentic
                    // multi-step workflows.
                    $_qwen_preserve =
                        preg_match('/qwen(\d+)\.(\d+)/', strtolower((string) ($this->model ?? '')), $_qm) === 1 &&
                        ((int) $_qm[1] >= 4 || ((int) $_qm[1] === 3 && (int) $_qm[2] >= 5));
                    if (!$_qwen_preserve) {
                        foreach ($content as $content_item) {
                            if (is_object($content_item) && isset($content_item->text)) {
                                $content_item->text = $this->stripThinkingBlocks($content_item->text);
                            }
                        }
                    }

                    $content = $this->truncateMcpToolResultContent($content);

                    self::$sessions[$this->session_id][] = [
                        'role' => 'assistant',
                        'content' => $content
                    ];
                } elseif (
                    !in_array($output__value->type, ['mcp_call', 'mcp_list_tools']) &&
                    // reasoning must be kept for local tool loop (GPT-5 requires it alongside function_call),
                    // but excluded for remote because the API requires it to be followed by a message item —
                    // if that message is missing or empty, storing reasoning alone causes an API error
                    !($output__value->type === 'reasoning' && $this->mcp_servers_call_type !== 'local')
                ) {
                    self::$sessions[$this->session_id][] = json_decode(json_encode($output__value), true);
                }
            }
        }
    }

    protected function askThis(
        ?string $prompt = null,
        mixed $files = null,
        bool $add_prompt_to_session = true,
        ?string $prev_output_text = null,
        float $prev_costs = 0.0,
        int $length_continuation_count = 0
    ): array {
        $return = ['response' => null, 'success' => false, 'costs' => $prev_costs];

        if (__::nx($this->model) || __::nx($this->session_id) || ($add_prompt_to_session && __::nx($prompt))) {
            $return['response'] = 'data missing.';
            return $return;
        }

        if ($add_prompt_to_session === true) {
            $this->appendPromptToSession($prompt, $files);
        }

        $args = [
            'model' => $this->model,
            'input' => self::$sessions[$this->session_id]
        ];

        $args = $this->applyTemperatureParameter($args);

        if (!empty($this->mcp_servers)) {
            $args['tools'] = [];
            if ($this->mcp_servers_call_type === 'local') {
                $args['tools'] = $this->buildLocalToolsArgs('parameters', true);
            } else {
                foreach ($this->mcp_servers as $mcp__key => $mcp__value) {
                    if (!isset($mcp__value['type'])) {
                        $mcp__value['type'] = 'mcp';
                    }
                    if (!isset($mcp__value['require_approval'])) {
                        $mcp__value['require_approval'] = 'never';
                    }
                    if (isset($mcp__value['name']) && !isset($mcp__value['server_label'])) {
                        $mcp__value['server_label'] = $mcp__value['name'];
                        unset($mcp__value['name']);
                    }
                    if (isset($mcp__value['authorization_token']) && !isset($mcp__value['authorization'])) {
                        $mcp__value['authorization'] = $mcp__value['authorization_token'];
                        unset($mcp__value['authorization_token']);
                    }
                    // lmstudio needs this
                    if ($this->name === 'lmstudio') {
                        if (isset($mcp__value['authorization']) && !isset($mcp__value['headers'])) {
                            $mcp__value['headers'] = [
                                'Authorization' => 'Bearer ' . $mcp__value['authorization']
                            ];
                            unset($mcp__value['authorization']);
                        }
                    }
                    if (isset($mcp__value['url']) && !isset($mcp__value['server_url'])) {
                        $mcp__value['server_url'] = $mcp__value['url'];
                        unset($mcp__value['url']);
                    }
                    if (!isset($mcp__value['server_label'])) {
                        $mcp__value['server_label'] = 'mcp-server-' . ($mcp__key + 1);
                    }
                    // sanitize server_label to match pattern ^[A-Za-z][A-Za-z0-9_-]*$
                    $mcp__value['server_label'] = preg_replace('/[^A-Za-z0-9_-]/', '_', $mcp__value['server_label']);
                    if (isset($mcp__value['server_url'])) {
                        $mcp__value['server_url'] = rtrim($mcp__value['server_url'], '/') . '/';
                    }
                    $args['tools'][] = $mcp__value;
                }
            }
        }

        if ($this->stream === true) {
            $args['stream'] = true;
        }

        if (method_exists($this, 'modifyArgs')) {
            $args = $this->modifyArgs($args);
        }
        $this->log((int) round(strlen(json_encode($args)) / 3.5), 'ask with input token length');
        $this->log($args, 'ask');
        $response = $this->makeApiCall($args);
        if ($this->stream === true) {
            $response = $this->stream_response;
        }
        $this->log($response?->result ?? null, 'response');
        $this->addCosts($response, $return);

        $output_text = $prev_output_text !== null ? $prev_output_text : '';
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->output ?? null)
        ) {
            foreach ($response->result->output as $output__value) {
                if (__::x($output__value?->type ?? null) && $output__value->type === 'message') {
                    if (__::x($output__value?->content ?? null)) {
                        foreach ($output__value->content as $content__value) {
                            if (__::x($content__value?->text ?? null)) {
                                if (__::x($output_text ?? null)) {
                                    $output_text .= PHP_EOL . PHP_EOL;
                                }
                                $output_text .= __::trim_whitespace($this->stripThinkingBlocks($content__value->text));
                            }
                        }
                    }
                }
            }
        }

        // handle function_call output for local tool loop:
        // responses api returns function_call items without text — treat as success so the tool loop can take over
        if (
            $this->mcp_servers_call_type === 'local' &&
            __::nx($output_text ?? null) &&
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->output ?? null)
        ) {
            $has_function_calls = false;
            foreach ($response->result->output as $output__value) {
                if (isset($output__value->type) && $output__value->type === 'function_call') {
                    $has_function_calls = true;
                    break;
                }
            }
            if ($has_function_calls) {
                $this->addResponseToSession($response);
                $return['response'] = '';
                $return['success'] = true;
                return $return;
            }
        }

        if (__::nx($output_text ?? null)) {
            $this->log($response, 'failed');
            $error_msg = $this->extractErrorMessage($response);
            $return['response'] = $error_msg ?? 'No response from provider.';
            return $return;
        }

        // auto-continue when the model was cut off by the length limit
        $continued = $this->continueIfNotFinished(
            $response,
            $output_text,
            $return['costs'],
            $length_continuation_count
        );
        if ($continued !== null) {
            return $continued;
        }

        $return['response'] = $output_text;
        $return['success'] = true;

        $this->addResponseToSession($response);

        // parse json
        $return['response'] = $this->parseJson($return['response']);

        return $return;
    }

    protected function modifyArgs(?array $args): ?array
    {
        $model_name = strtolower($this->model ?? '');
        $is_o_model = preg_match('/^(o1|o3|o4)(-|$)/', $model_name) === 1;
        $is_o1_pro = preg_match('/^o1-pro/', $model_name) === 1;
        if ($is_o_model && !$is_o1_pro) {
            // reasoning models always reason; enable_thinking=false maps to the
            // lowest effort ("minimal"), null/true keeps the default "medium".
            $effort = $this->enable_thinking === false ? 'minimal' : 'medium';
            $args['reasoning'] = ['effort' => $effort, 'summary' => 'detailed'];
        } else {
            unset($args['reasoning']);
        }
        return $args;
    }

    protected function makeApiCall(?array $args = null): mixed
    {
        return __::curl(
            url: $this->url . '/responses',
            data: $args,
            method: 'POST',
            headers: [
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            timeout: $this->timeout,
            stream_callback: $this->getStreamCallback()
        );
    }
}

class ai_anthropic extends aihelper
{
    public ?string $provider = 'Anthropic';

    public ?string $title = 'Anthropic';

    public ?string $name = 'anthropic';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M17.304 3.541h-3.672l6.696 16.918H24Zm-10.608 0L0 20.459h3.744l1.37-3.553h7.005l1.369 3.553h3.744L10.536 3.541Zm-.371 10.223L8.616 7.82l2.291 5.945Z"/></svg>
    SVG;

    protected ?string $url = 'https://api.anthropic.com/v1';

    public ?bool $supports_mcp_remote = true;

    public ?bool $supports_stream = true;

    public array $models = [
        [
            'name' => 'claude-haiku-4-5',
            'context_length' => 200000,
            'max_output_tokens' => 64000,
            'costs' => ['input' => 0.000001, 'input_cached' => 0.0000001, 'output' => 0.000005],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'claude-fable-5',
            'context_length' => 1000000,
            'max_output_tokens' => 64000,
            'costs' => ['input' => 0.00001, 'input_cached' => 0.000001, 'output' => 0.00005],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-0',
            'context_length' => 200000,
            'max_output_tokens' => 32000,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.0000015, 'output' => 0.000075],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-1',
            'context_length' => 200000,
            'max_output_tokens' => 32000,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.0000015, 'output' => 0.000075],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-5',
            'context_length' => 200000,
            'max_output_tokens' => 32000,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.0000005, 'output' => 0.000025],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-6',
            'context_length' => 1000000,
            'max_output_tokens' => 32000,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.0000005, 'output' => 0.000025],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-7',
            'context_length' => 1000000,
            'max_output_tokens' => 32000,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.0000005, 'output' => 0.000025],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-8',
            'context_length' => 1000000,
            'max_output_tokens' => 32000,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.0000005, 'output' => 0.000025],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-sonnet-4-0',
            'context_length' => 200000,
            'max_output_tokens' => 16384,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.0000003, 'output' => 0.000015],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-sonnet-4-5',
            'context_length' => 200000,
            'max_output_tokens' => 16384,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.0000003, 'output' => 0.000015],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-sonnet-4-6',
            'context_length' => 1000000,
            'max_output_tokens' => 16384,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.0000003, 'output' => 0.000015],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => true,
            'test' => false
        ]
    ];

    public function fetchModels(): array
    {
        $models = [];
        $response = __::curl(
            url: $this->url . '/models?beta=true&limit=1000',
            method: 'GET',
            headers: [
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01'
            ],
            timeout: $this->timeout
        );
        $this->log($response);
        if (__::x($response ?? null) && __::x($response?->result ?? null) && __::x($response?->result?->data ?? null)) {
            foreach ($response->result->data as $data__value) {
                if (__::x($data__value?->id ?? null)) {
                    $name = $data__value->id;
                    // replace [a-zA-Z]+-[0-9]-[0-9]{3,}$ with [a-zA-Z]+-[0-9]-0
                    $name = preg_replace('/([a-zA-Z]+)-([0-9]+)-[0-9]{3,}$/', '$1-$2-0', $name);
                    // replace [a-zA-Z]+-[0-9]-[0-9]-[0-9]+$ with [a-zA-Z]+-[0-9]-[0-9]$
                    $name = preg_replace('/([a-zA-Z]+)-([0-9]+)-([0-9]+)-[0-9]+$/', '$1-$2-$3', $name);
                    if (strpos($name, '-beta') !== false) {
                        continue;
                    }
                    if (
                        in_array($name, [
                            'grok-4-0',
                            'grok-2-image-1212',
                            'grok-imagine-image-pro',
                            'grok-imagine-video',
                            'grok-imagine-video-1.5-preview',
                            'grok-imagine-video-1.5-2026-05-30',
                            'grok-2-vision-1212'
                        ])
                    ) {
                        continue;
                    }
                    $entry = ['name' => $name, 'context_length' => 128000];
                    foreach ($this->models as $definedModel) {
                        if ($definedModel['name'] === $name) {
                            // merge static caps (supports_*/costs/…) into the
                            // dynamic entry so capability metadata survives fetchModels()
                            $entry = array_merge($definedModel, ['name' => $name]);
                            if (!isset($entry['context_length'])) {
                                $entry['context_length'] = 128000;
                            }
                            break;
                        }
                    }
                    $models[] = $entry;
                }
            }
        }
        return $models;
    }

    protected function bringPromptInFormat(string $prompt, mixed $files = null): array
    {
        $content = [];

        // add text content
        $content[] = [
            'type' => 'text',
            'text' => $prompt
        ];

        // add files
        if (__::x($files ?? null)) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $files__value) {
                if (!file_exists($files__value)) {
                    continue;
                }
                $mime = mime_content_type($files__value);
                $b64 = base64_encode(file_get_contents($files__value));
                $type = stripos($mime, 'pdf') !== false || $mime === 'application/pdf' ? 'document' : 'image';

                $content[] = [
                    'type' => $type,
                    'source' => [
                        'type' => 'base64',
                        'media_type' => $mime,
                        'data' => $b64
                    ]
                ];
            }
        }

        return [
            'role' => 'user',
            'content' => $content
        ];
    }

    protected function addResponseToSession(mixed $response): void
    {
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->content ?? null)
        ) {
            $content = $response->result->content;

            // remove orphaned mcp_tool_use assistant messages from the session before appending the new one:
            // a previous mcp_tool_use block is orphaned when the new response being added is also an assistant message,
            // meaning the mcp server returned results and the old pending block is no longer needed standalone
            if (!empty(self::$sessions[$this->session_id])) {
                $last = end(self::$sessions[$this->session_id]);
                $lastRole = $last['role'] ?? null;
                $lastContent = $last['content'] ?? null;
                if ($lastRole === 'assistant' && is_array($lastContent)) {
                    foreach ($lastContent as $block) {
                        $type = is_object($block) ? $block->type ?? null : $block['type'] ?? null;
                        if ($type === 'mcp_tool_use') {
                            $this->log(
                                'addResponseToSession: removed orphaned mcp_tool_use block before appending new response'
                            );
                            array_pop(self::$sessions[$this->session_id]);
                            break;
                        }
                    }
                }
            }

            // fix tool_use / mcp_tool_use blocks with empty array or string inputs: anthropic API requires .input to be a dict (JSON object) — empty PHP arrays serialize as "[]" which fails validation
            if (is_array($content)) {
                for ($i = 0; $i < count($content); $i++) {
                    if (
                        isset($content[$i]->type) &&
                        ($content[$i]->type === 'mcp_tool_use' || $content[$i]->type === 'tool_use') &&
                        isset($content[$i]->input)
                    ) {
                        if (is_array($content[$i]->input) && count($content[$i]->input) === 0) {
                            $content[$i]->input = new \stdClass();
                        }
                        if (is_string($content[$i]->input)) {
                            $decoded = json_decode($content[$i]->input);
                            if (is_object($decoded)) {
                                $content[$i]->input = $decoded;
                            } elseif ($content[$i]->input === '' || $content[$i]->input === '[]') {
                                $content[$i]->input = new \stdClass();
                            }
                        }
                    }
                }
            }

            // truncate long mcp_tool_result content to avoid token limits
            $content = $this->truncateMcpToolResultContent($content);

            // remove trailing whitespace from last text content block to avoid API errors
            if (is_array($content) && count($content) > 0) {
                // find last text block (not last block overall)
                for ($i = count($content) - 1; $i >= 0; $i--) {
                    if (isset($content[$i]->type) && $content[$i]->type === 'text' && isset($content[$i]->text)) {
                        $content[$i]->text = rtrim($content[$i]->text);
                        break;
                    }
                }
            }

            self::$sessions[$this->session_id][] = [
                'role' => 'assistant',
                'content' => $content
            ];
        }
    }

    protected function askThis(
        ?string $prompt = null,
        mixed $files = null,
        bool $add_prompt_to_session = true,
        ?string $prev_output_text = null,
        float $prev_costs = 0.0,
        int $length_continuation_count = 0
    ): array {
        $return = ['response' => null, 'success' => false, 'costs' => $prev_costs];

        if (__::nx($this->model) || __::nx($this->session_id) || ($add_prompt_to_session && __::nx($prompt))) {
            $return['response'] = 'data missing.';
            return $return;
        }

        if ($add_prompt_to_session === true) {
            $this->appendPromptToSession($prompt, $files);
        }

        $args = [
            'model' => $this->model,
            'max_tokens' => $this->getMaxOutputTokensForModel(),
            'messages' => self::$sessions[$this->session_id]
        ];

        $args = $this->applyTemperatureParameter($args);

        if (!empty($this->mcp_servers)) {
            if ($this->mcp_servers_call_type === 'local') {
                $args['tools'] = $this->buildLocalToolsArgs('input_schema', false);
            } else {
                $args['mcp_servers'] = [];
                foreach ($this->mcp_servers as $mcp__key => $mcp__value) {
                    if (!isset($mcp__value['type'])) {
                        $mcp__value['type'] = 'url';
                    }
                    if (!isset($mcp__value['name'])) {
                        $mcp__value['name'] = 'mcp-server-' . ($mcp__key + 1);
                    }
                    if (isset($mcp__value['url'])) {
                        $mcp__value['url'] = rtrim($mcp__value['url'], '/') . '/';
                    }
                    $args['mcp_servers'][] = $mcp__value;
                }
            }
        }

        if ($this->stream === true) {
            $args['stream'] = true;
        }

        if (method_exists($this, 'modifyArgs')) {
            $args = $this->modifyArgs($args);
        }
        $this->log((int) round(strlen(json_encode($args)) / 3.5), 'ask with input token length');
        $this->log($args, 'ask');
        $response = $this->makeApiCall($args);
        if ($this->stream === true) {
            $response = $this->stream_response;
        }
        $this->log($response?->result ?? null, 'response');
        $this->addCosts($response, $return);

        $output_text = $prev_output_text !== null ? $prev_output_text : '';
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->content ?? null)
        ) {
            foreach ($response->result->content as $content__value) {
                if (__::x($content__value?->text ?? null)) {
                    if (__::x($output_text ?? null)) {
                        $output_text .= PHP_EOL . PHP_EOL;
                    }
                    $output_text .= __::trim_whitespace($content__value->text);
                }
            }
        }

        // handle stop_reason "tool_use" for local tool loop:
        // anthropic returns tool_use blocks without text — treat as success so the tool loop can take over
        if (
            $this->mcp_servers_call_type === 'local' &&
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->stop_reason ?? null) &&
            $response->result->stop_reason === 'tool_use'
        ) {
            $this->addResponseToSession($response);
            $return['response'] = $output_text ?: '';
            $return['success'] = true;
            return $return;
        }

        // handle stop reason
        // normally anthropic sends pause_turn as a stop reason
        // but sometimes it also sends no stop reason with partial content
        // we detect both cases
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            ((__::x($response?->result?->stop_reason ?? null) && $response->result->stop_reason === 'pause_turn') ||
                (__::nx($response?->result?->stop_reason ?? null) && __::x($response?->result?->content ?? null)))
        ) {
            $this->log('pause_turn / empty stop_reason detected');

            // throttle
            /*
            if (__::x(($response?->result?->usage??null)) && __::x(($response?->result?->usage?->input_tokens??null))) {
                $pause_turn_input_tokens = $response->result->usage->input_tokens;
                if ($pause_turn_input_tokens > 400000) {
                    $pause_turn_sleep = (int) (ceil($pause_turn_input_tokens / 400000) * 60);
                    $this->log(
                        'high input tokens detected (' .
                            $pause_turn_input_tokens .
                            '). sleeping for ' .
                            $pause_turn_sleep .
                            ' seconds to avoid rate limits...'
                    );
                    sleep($pause_turn_sleep);
                    $this->log('continuing...');
                }
            }
            */

            $this->addResponseToSession($response);

            // recursively call with updated session
            return $this->askThis(
                prompt: $prompt,
                files: $files,
                add_prompt_to_session: false,
                prev_output_text: $output_text,
                prev_costs: $return['costs'],
                length_continuation_count: $length_continuation_count
            );
        }

        if (__::nx($output_text ?? null)) {
            $this->log($response, 'failed');
            if (
                __::x($response ?? null) &&
                __::x($response?->result ?? null) &&
                __::x($response?->result?->type ?? null) &&
                ($response?->result?->type ?? null) === 'error' &&
                __::x($response?->result?->error ?? null) &&
                __::x($response?->result?->error?->type ?? null) &&
                ($response?->result?->error?->type ?? null) === 'overloaded_error'
            ) {
                $this->log('overload detected. pausing...');
                sleep(5);
            }
            $error_msg = $this->extractErrorMessage($response);
            $return['response'] = $error_msg ?? 'No response from provider.';
            return $return;
        }

        // auto-continue when the model was cut off by the length limit
        $continued = $this->continueIfNotFinished(
            $response,
            $output_text,
            $return['costs'],
            $length_continuation_count
        );
        if ($continued !== null) {
            return $continued;
        }

        $return['response'] = $output_text;
        $return['success'] = true;

        $this->addResponseToSession($response);

        // parse json
        $return['response'] = $this->parseJson($return['response']);

        return $return;
    }

    protected function modifyArgs(?array $args): ?array
    {
        $model_name = strtolower($this->model ?? '');
        $supports_thinking =
            str_contains($model_name, 'sonnet') ||
            str_contains($model_name, 'opus') ||
            (preg_match('/haiku-(\d+)/', $model_name, $_hm) === 1 && (int) $_hm[1] >= 4);
        $adaptive_thinking_models = ['claude-opus-4-7', 'claude-opus-4-8'];
        $adaptive_thinking = in_array($model_name, $adaptive_thinking_models, true);
        // explicit enable_thinking=false overrides the default-on behavior for
        // sonnet/opus models; null keeps the existing default (thinking on where
        // supported); true enables it even if a future model doesn't default to it.
        $want_thinking = $this->enable_thinking !== false && ($this->enable_thinking === true || $supports_thinking);

        if ($supports_thinking && $want_thinking) {
            if ($adaptive_thinking) {
                // new API: use adaptive thinking + effort level instead of enabled/budget_tokens
                $args['thinking'] = ['type' => 'adaptive'];
                $args['output_config'] = ['effort' => 'high'];
            } else {
                $args['thinking'] = ['type' => 'enabled', 'budget_tokens' => 10000];
            }
            // temperature must be 1 when thinking is enabled
            $args['temperature'] = 1.0;
        }

        return $args;
    }

    protected function makeApiCall(?array $args = null): mixed
    {
        return __::curl(
            url: $this->url . '/messages',
            data: $args,
            method: 'POST',
            headers: [
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01',
                'anthropic-beta' => 'mcp-client-2025-04-04,interleaved-thinking-2025-05-14'
            ],
            timeout: $this->timeout,
            stream_callback: $this->getStreamCallback()
        );
    }
}

class ai_google extends aihelper
{
    public ?string $provider = 'Google';

    public ?string $title = 'Google';

    public ?string $name = 'google';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 262"><path fill="#4285f4" d="M255.878 133.451c0-10.734-.871-18.567-2.756-26.69H130.55v48.448h71.947c-1.45 12.04-9.283 30.172-26.69 42.356l-.244 1.622l38.755 30.023l2.685.268c24.659-22.774 38.875-56.282 38.875-96.027"/><path fill="#34a853" d="M130.55 261.1c35.248 0 64.839-11.605 86.453-31.622l-41.196-31.913c-11.024 7.688-25.82 13.055-45.257 13.055c-34.523 0-63.824-22.773-74.269-54.25l-1.531.13l-40.298 31.187l-.527 1.465C35.393 231.798 79.49 261.1 130.55 261.1"/><path fill="#fbbc05" d="M56.281 156.37c-2.756-8.123-4.351-16.827-4.351-25.82c0-8.994 1.595-17.697 4.206-25.82l-.073-1.73L15.26 71.312l-1.335.635C5.077 89.644 0 109.517 0 130.55s5.077 40.905 13.925 58.602z"/><path fill="#eb4335" d="M130.55 50.479c24.514 0 41.05 10.589 50.479 19.438l36.844-35.974C195.245 12.91 165.798 0 130.55 0C79.49 0 35.393 29.301 13.925 71.947l42.211 32.783c10.59-31.477 39.891-54.251 74.414-54.251"/></svg>
    SVG;

    protected ?string $url = 'https://generativelanguage.googleapis.com/v1beta';

    public ?bool $supports_mcp_remote = false;

    public ?bool $supports_stream = true;

    public array $models = [
        [
            'name' => 'gemini-2.5-flash',
            'context_length' => 1048576,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.0000003, 'input_cached' => 0.00000003, 'output' => 0.0000025],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => true,
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'gemini-2.5-flash-image',
            'context_length' => 1048576,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.0000003, 'input_cached' => 0.00000003, 'output' => 0.0000025],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-2.5-flash-lite',
            'context_length' => 1048576,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.00000001, 'output' => 0.0000004],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-2.5-pro',
            'context_length' => 1048576,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => true,
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'gemini-flash-latest',
            'context_length' => 1048576,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.0000003, 'input_cached' => 0.00000003, 'output' => 0.0000025],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-flash-lite-latest',
            'context_length' => 1048576,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.00000001, 'output' => 0.0000004],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-pro-latest',
            'context_length' => 1048576,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-3.1-flash-lite',
            'context_length' => 1048576,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.00000025, 'input_cached' => 0.000000025, 'output' => 0.0000015],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-3.1-flash-image',
            'context_length' => 1048576,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.0000005, 'input_cached' => 0.00000005, 'output' => 0.000003, 'image' => 0.067],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-3-pro-image',
            'context_length' => 1048576,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000002, 'output' => 0.000012, 'image' => 0.134],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-3.5-flash',
            'context_length' => 1048576,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 0.0000015, 'input_cached' => 0.00000015, 'output' => 0.000009],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemma-4-26b-a4b-it',
            'context_length' => 256000,
            'max_output_tokens' => 8192,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemma-4-31b-it',
            'context_length' => 256000,
            'max_output_tokens' => 8192,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'imagen-4.0-generate-001',
            'supports_tools' => false,
            'costs' => ['image' => 0.04],
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'imagen-4.0-fast-generate-001',
            'supports_tools' => false,
            'costs' => ['image' => 0.02],
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'imagen-4.0-ultra-generate-001',
            'supports_tools' => false,
            'costs' => ['image' => 0.06],
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ]
    ];

    public function fetchModels(): array
    {
        $models = [];
        $response = __::curl(
            url: $this->url . '/models?key=' . $this->api_key,
            method: 'GET',
            headers: null,
            timeout: $this->timeout
        );
        $this->log($response);
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->models ?? null) &&
            is_array($response->result->models)
        ) {
            foreach ($response->result->models as $models__value) {
                if (__::x($models__value?->name ?? null)) {
                    $name = $models__value->name;
                    $name = str_replace('models/', '', $name);
                    if (strpos($name, '-exp') !== false) {
                        continue;
                    }
                    if (strpos($name, '-preview') !== false) {
                        continue;
                    }
                    if (
                        in_array($name, [
                            'gemini-2.5-flash-native-audio-latest',
                            'gemini-2.0-flash',
                            'gemini-2.0-flash-lite',
                            'gemini-2.0-flash-001',
                            'gemini-2.0-flash-lite-001',
                            'gemini-embedding-001',
                            'gemini-embedding-2',
                            'aqa',
                            'veo-2.0-generate-001',
                            'veo-3.0-generate-001',
                            'veo-3.0-fast-generate-001'
                        ])
                    ) {
                        continue;
                    }
                    $entry = ['name' => $name, 'context_length' => 128000];
                    foreach ($this->models as $definedModel) {
                        if ($definedModel['name'] === $name) {
                            $entry = array_merge($definedModel, ['name' => $name]);
                            if (!isset($entry['context_length'])) {
                                $entry['context_length'] = 128000;
                            }
                            break;
                        }
                    }
                    if (!empty($models__value->inputTokenLimit)) {
                        $entry['context_length'] = (int) $models__value->inputTokenLimit;
                    }
                    $models[] = $entry;
                }
            }
        }
        return $models;
    }

    protected function bringPromptInFormat(string $prompt, mixed $files = null): array
    {
        $parts = [];

        // add text content
        $parts[] = [
            'text' => $prompt
        ];

        // add files
        if (__::x($files ?? null)) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $files__value) {
                if (!file_exists($files__value)) {
                    continue;
                }
                $mime = mime_content_type($files__value);
                $b64 = base64_encode(file_get_contents($files__value));

                $parts[] = [
                    'inline_data' => [
                        'mime_type' => $mime,
                        'data' => $b64
                    ]
                ];
            }
        }

        return [
            'role' => 'user',
            'parts' => $parts
        ];
    }

    protected function addResponseToSession(mixed $response): void
    {
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->candidates ?? null)
        ) {
            foreach ($response->result->candidates as $candidates__value) {
                if (
                    __::x($candidates__value?->content ?? null) &&
                    __::x($candidates__value?->content?->parts ?? null)
                ) {
                    $content = $candidates__value->content->parts;

                    $content = $this->truncateMcpToolResultContent($content);

                    self::$sessions[$this->session_id][] = [
                        'role' => 'model',
                        'parts' => $content
                    ];
                }
            }
        }
    }

    protected function askThis(
        ?string $prompt = null,
        mixed $files = null,
        bool $add_prompt_to_session = true,
        ?string $prev_output_text = null,
        float $prev_costs = 0.0,
        int $length_continuation_count = 0
    ): array {
        $return = ['response' => null, 'success' => false, 'costs' => $prev_costs];

        if (__::nx($this->model) || __::nx($this->session_id) || ($add_prompt_to_session && __::nx($prompt))) {
            $return['response'] = 'data missing.';
            return $return;
        }

        if ($add_prompt_to_session === true) {
            $this->appendPromptToSession($prompt, $files);
        }

        $args = [
            'contents' => self::$sessions[$this->session_id]
        ];
        $args = $this->applyTemperatureParameter($args, 'generationConfig');
        // Gemini 2.5 thinking budget. null = default (1024), true = default (1024),
        // false = explicitly off (0). No-op on models without thinking support.
        if (in_array($this->model, ['gemini-2.5-pro', 'gemini-2.5-flash'], true)) {
            $budget = $this->enable_thinking === false ? 0 : 1024;
            $args['generationConfig']['thinkingConfig'] = ['thinkingBudget' => $budget];
        }
        if (preg_match('/^gemma-4-/', $this->model) === 1) {
            if (!isset($args['generationConfig']) || !is_array($args['generationConfig'])) {
                $args['generationConfig'] = [];
            }
            $args['generationConfig']['temperature'] = 1.0;
            $args['generationConfig']['topP'] = 0.95;
            $args['generationConfig']['topK'] = 64;
            $thinking_level = $this->enable_thinking === false ? 'low' : 'high';
            $args['generationConfig']['thinkingConfig'] = ['thinkingLevel' => $thinking_level];
        }

        if (!empty($this->mcp_servers) && $this->mcp_servers_call_type === 'local') {
            $tools = $this->buildLocalToolsArgs('parameters', false, [
                'additionalProperties',
                '$schema',
                'definition',
                'default'
            ]);
            if (!empty($tools)) {
                $args['tools'] = [['functionDeclarations' => $tools]];
            }
        }

        if (method_exists($this, 'modifyArgs')) {
            $args = $this->modifyArgs($args);
        }
        $this->log((int) round(strlen(json_encode($args)) / 3.5), 'ask with input token length');
        $this->log($args, 'ask');
        $response = $this->makeApiCall($args);
        if ($this->stream === true) {
            $response = $this->stream_response;
        }
        $this->log($response?->result ?? null, 'response');
        $this->addCosts($response, $return);

        $output_text = $prev_output_text !== null ? $prev_output_text : '';
        $has_function_calls = false;
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->candidates ?? null)
        ) {
            foreach ($response->result->candidates as $candidates__value) {
                if (
                    __::x($candidates__value?->content ?? null) &&
                    __::x($candidates__value?->content?->parts ?? null)
                ) {
                    foreach ($candidates__value->content->parts as $parts__value) {
                        if (__::x($parts__value?->text ?? null)) {
                            if (__::x($output_text ?? null)) {
                                $output_text .= PHP_EOL . PHP_EOL;
                            }
                            $output_text .= __::trim_whitespace($parts__value->text);
                        }
                        if (isset($parts__value->functionCall)) {
                            $has_function_calls = true;
                        }
                    }
                }
            }
        }

        // handle functionCall for local tool loop
        if ($this->mcp_servers_call_type === 'local' && $has_function_calls) {
            $this->addResponseToSession($response);
            $return['response'] = $output_text ?: '';
            $return['success'] = true;
            return $return;
        }

        if (__::nx($output_text)) {
            $this->log($response, 'failed');
            $error_msg = $this->extractErrorMessage($response);
            $return['response'] = $error_msg ?? 'No response from provider.';
            return $return;
        }

        // auto-continue when the model was cut off by the length limit
        $continued = $this->continueIfNotFinished(
            $response,
            $output_text,
            $return['costs'],
            $length_continuation_count
        );
        if ($continued !== null) {
            return $continued;
        }

        $return['response'] = $output_text;
        $return['success'] = true;

        $this->addResponseToSession($response);

        // parse json
        $return['response'] = $this->parseJson($return['response']);

        return $return;
    }

    protected function makeApiCall(?array $args = null): mixed
    {
        $action = $this->stream ? 'streamGenerateContent?alt=sse&' : 'generateContent?';
        return __::curl(
            url: $this->url . '/models/' . $this->model . ':' . $action . 'key=' . $this->api_key,
            data: $args,
            method: 'POST',
            headers: null,
            timeout: $this->timeout,
            stream_callback: $this->getStreamCallback()
        );
    }
}

/* compatible with the anthropic api */
class ai_xai extends ai_anthropic
{
    public ?string $provider = 'xAI';

    public ?string $title = 'xAI';

    public ?string $name = 'xai';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 291"><path d="m.073 102.553l128.541 187.58h57.137L57.195 102.553zm57.078 104.183L0 290.133h57.18l28.553-41.69zM198.82 0l-98.788 144.154l28.582 41.721L256 0zm10.347 89.2v200.933H256V20.861z"/></svg>
    SVG;

    protected ?string $url = 'https://api.x.ai/v1';

    public ?bool $supports_mcp_remote = false;

    public ?bool $supports_stream = false;

    public array $models = [
        [
            'name' => 'grok-4.20-0309-non-reasoning',
            'context_length' => 256000,
            'max_output_tokens' => 16000,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000002, 'output' => 0.000006],
            'supports_temperature' => true,
            'supports_tools' => false,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-4.20-0309-reasoning',
            'context_length' => 256000,
            'max_output_tokens' => 16000,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000002, 'output' => 0.000006],
            'supports_temperature' => true,
            'supports_tools' => false,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-4.20-multi-agent-0309',
            'context_length' => 256000,
            'max_output_tokens' => 16000,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000002, 'output' => 0.000006],
            'supports_temperature' => true,
            'supports_tools' => false,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-build-0.1',
            'context_length' => 256000,
            'max_output_tokens' => 16000,
            'costs' => ['input' => 0.000001, 'input_cached' => 0.0000002, 'output' => 0.000002],
            'supports_temperature' => true,
            'supports_tools' => false,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-4.3',
            'context_length' => 1000000,
            'max_output_tokens' => 16000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.00000125, 'output' => 0.0000025],
            'supports_temperature' => true,
            'supports_tools' => false,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => true,
            'test' => true
        ],
        // image generation — costs per image
        [
            'name' => 'grok-imagine-image',
            'supports_tools' => false,
            'costs' => ['image' => 0.02],
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-imagine-image-quality',
            'supports_tools' => false,
            'costs' => ['image' => 0.07],
            'supports_text_to_image' => true,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ]
    ];
}

/* compatible with the anthropic api */
class ai_deepseek extends ai_anthropic
{
    public ?string $provider = 'DeepSeek';

    public ?string $title = 'DeepSeek';

    public ?string $name = 'deepseek';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 189"><path fill="#4d6bfe" d="M253.314 15.812c-2.711-1.329-3.88 1.203-5.465 2.49c-.542.414-1.001.954-1.46 1.452c-3.963 4.232-8.594 7.013-14.643 6.68c-8.845-.497-16.396 2.284-23.071 9.048c-1.419-8.341-6.133-13.322-13.309-16.517c-3.754-1.66-7.55-3.32-10.179-6.931c-1.836-2.572-2.336-5.436-3.254-8.258c-.584-1.702-1.168-3.445-3.13-3.736c-2.127-.331-2.961 1.452-3.796 2.947c-3.337 6.101-4.63 12.824-4.506 19.63c.292 15.315 6.759 27.515 19.608 36.19c1.46.995 1.836 1.99 1.377 3.444c-.876 2.988-1.92 5.893-2.837 8.88c-.584 1.91-1.46 2.325-3.504 1.494c-7.051-2.945-13.142-7.304-18.524-12.574c-9.136-8.84-17.397-18.592-27.701-26.228a121 121 0 0 0-7.343-5.022c-10.513-10.209 1.377-18.593 4.13-19.588c2.879-1.038 1.002-4.607-8.302-4.565s-17.814 3.154-28.66 7.304c-1.586.623-3.255 1.079-4.966 1.452c-9.845-1.867-20.066-2.283-30.747-1.079c-20.108 2.241-36.17 11.745-47.976 27.972C.872 59.802-2.466 81.963 1.623 105.079c4.297 24.36 16.729 44.53 35.837 60.301c19.816 16.35 42.637 24.36 68.67 22.825c15.81-.913 33.416-3.029 53.275-19.837c5.005 2.49 10.262 3.486 18.982 4.233c6.717.623 13.183-.332 18.19-1.369c7.842-1.66 7.3-8.923 4.464-10.25c-22.988-10.708-17.94-6.35-22.529-9.878c11.681-13.82 29.287-28.18 36.17-74.702c.543-3.693.084-6.018 0-9.006c-.041-1.825.376-2.53 2.462-2.739c5.757-.664 11.348-2.24 16.48-5.062c14.893-8.134 20.9-21.498 22.318-37.517c.21-2.449-.041-4.98-2.628-6.266M123.526 159.985c-22.278-17.513-33.083-23.282-37.547-23.033c-4.172.25-3.42 5.022-2.503 8.135c.96 3.07 2.211 5.187 3.963 7.884c1.21 1.785 2.045 4.44-1.21 6.433c-7.175 4.44-19.65-1.494-20.234-1.784c-14.518-8.55-26.658-19.839-35.21-35.276c-8.261-14.858-13.058-30.794-13.851-47.81c-.21-4.107 1-5.56 5.09-6.307c5.38-.996 10.93-1.204 16.311-.416C61.073 71.131 80.43 81.3 96.66 97.401c9.261 9.172 16.27 20.129 23.488 30.836c7.676 11.37 15.936 22.203 26.45 31.084c3.712 3.112 6.674 5.478 9.511 7.221c-8.552.955-22.82 1.163-32.582-6.557m10.68-68.684a3.27 3.27 0 0 1 3.296-3.278c.418 0 .793.082 1.127.206c.46.167.876.416 1.21.789c.584.581.918 1.41.918 2.283a3.267 3.267 0 0 1-3.296 3.278c-1.835 0-3.254-1.452-3.254-3.278m33.167 17.016c-2.128.872-4.255 1.618-6.3 1.701c-3.17.166-6.633-1.121-8.51-2.698c-2.92-2.449-5.006-3.817-5.882-8.092c-.376-1.826-.167-4.649.167-6.267c.75-3.486-.084-5.726-2.545-7.76c-2.003-1.661-4.548-2.117-7.343-2.117c-1.043 0-2.002-.457-2.712-.83c-1.168-.581-2.127-2.034-1.21-3.818c.293-.58 1.711-1.992 2.045-2.24c3.797-2.16 8.177-1.453 12.224.165c3.755 1.535 6.592 4.358 10.68 8.341c4.172 4.814 4.923 6.143 7.3 9.753c1.879 2.822 3.59 5.727 4.757 9.048c.71 2.075-.209 3.776-2.67 4.814z"/></svg>
    SVG;

    protected ?string $url = 'https://api.deepseek.com/anthropic';

    public ?bool $supports_mcp_remote = false;

    public ?bool $supports_stream = false;

    public array $models = [
        [
            'name' => 'deepseek-v4-flash',
            'context_length' => 1000000,
            'max_output_tokens' => 64000,
            'costs' => ['input' => 0.00000004, 'input_cached' => 0.000000004, 'output' => 0.0000001],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => true,
            'test' => true
        ],
        [
            'name' => 'deepseek-v4-pro',
            'context_length' => 1000000,
            'max_output_tokens' => 64000,
            'costs' => ['input' => 0.00000027, 'input_cached' => 0.000000027, 'output' => 0.00000042],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ]
    ];

    public function fetchModels(): array
    {
        $models = [];
        $response = __::curl(
            url: str_replace('/anthropic', '', $this->url) . '/models?beta=true&limit=1000',
            method: 'GET',
            headers: [
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01'
            ],
            timeout: $this->timeout
        );
        $this->log($response);
        if (__::x($response ?? null) && __::x($response?->result ?? null) && __::x($response?->result?->data ?? null)) {
            foreach ($response->result->data as $data__value) {
                if (__::x($data__value?->id ?? null)) {
                    $name = $data__value->id;
                    $entry = ['name' => $name, 'context_length' => 128000];
                    foreach ($this->models as $definedModel) {
                        if ($definedModel['name'] === $name) {
                            // merge static caps (supports_*/costs/…) into the
                            // dynamic entry so capability metadata survives fetchModels()
                            $entry = array_merge($definedModel, ['name' => $name]);
                            if (!isset($entry['context_length'])) {
                                $entry['context_length'] = 128000;
                            }
                            break;
                        }
                    }
                    $models[] = $entry;
                }
            }
        }
        return $models;
    }
}

/* compatible with the openai api */
class ai_openrouter extends aihelper
{
    public ?string $provider = 'OpenRouter';

    public ?string $title = 'OpenRouter';

    public ?string $name = 'openrouter';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M16.778 1.844v1.919q-.569-.026-1.138-.032q-.708-.008-1.415.037c-1.93.126-4.023.728-6.149 2.237c-2.911 2.066-2.731 1.95-4.14 2.75c-.396.223-1.342.574-2.185.798c-.841.225-1.753.333-1.751.333v4.229s.768.108 1.61.333c.842.224 1.789.575 2.185.799c1.41.798 1.228.683 4.14 2.75c2.126 1.509 4.22 2.11 6.148 2.236c.88.058 1.716.041 2.555.005v1.918l7.222-4.168l-7.222-4.17v2.176c-.86.038-1.611.065-2.278.021c-1.364-.09-2.417-.357-3.979-1.465c-2.244-1.593-2.866-2.027-3.68-2.508c.889-.518 1.449-.906 3.822-2.59c1.56-1.109 2.614-1.377 3.978-1.466c.667-.044 1.418-.017 2.278.02v2.176L24 6.014Z"/></svg>
    SVG;

    protected ?string $url = 'https://openrouter.ai/api/v1';

    public ?bool $supports_mcp_remote = false;

    public ?bool $supports_stream = true;

    public array $models = [];

    public function fetchModels(): array
    {
        $models = [];
        $response = __::curl(
            url: $this->url . '/models',
            method: 'GET',
            headers: [
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            timeout: $this->timeout
        );
        $this->log($response);
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->data ?? null) &&
            is_array($response->result->data)
        ) {
            foreach ($response->result->data as $models__value) {
                if (__::x($models__value?->id ?? null)) {
                    $input_cost = (float) ($models__value->pricing->prompt ?? 0);
                    $output_cost = (float) ($models__value->pricing->completion ?? 0);
                    $supported_params =
                        isset($models__value->supported_parameters) && is_array($models__value->supported_parameters)
                            ? $models__value->supported_parameters
                            : [];
                    $models[] = [
                        'name' => $models__value->id,
                        'context_length' => (int) ($models__value->context_length ?? 128000),
                        'costs' => ['input' => $input_cost, 'input_cached' => $input_cost, 'output' => $output_cost],
                        'supports_temperature' => in_array('temperature', $supported_params, true),
                        'supports_tools' => in_array('tools', $supported_params, true),
                        'default' => $models__value->id === 'anthropic/claude-haiku-4.5',
                        'test' => $models__value->id === 'anthropic/claude-haiku-4.5'
                    ];
                }
            }
        }
        if (!empty($models)) {
            // sort by name
            usort($models, function ($a, $b) {
                return $a['name'] <=> $b['name'];
            });
        }
        return $models;
    }

    public function ping(): bool
    {
        try {
            $response = __::curl(
                url: $this->url . '/auth/key',
                method: 'GET',
                headers: ['Authorization' => 'Bearer ' . $this->api_key],
                timeout: 30
            );
            return ($response->status ?? 0) >= 200 && ($response->status ?? 0) < 300;
        } catch (\Exception) {
            return false;
        }
    }

    protected function bringPromptInFormat(string $prompt, mixed $files = null): array
    {
        if (!__::x($files ?? null)) {
            return ['role' => 'user', 'content' => $prompt];
        }
        $content = [['type' => 'text', 'text' => $prompt]];
        if (!is_array($files)) {
            $files = [$files];
        }
        foreach ($files as $files__value) {
            if (!file_exists($files__value)) {
                continue;
            }
            $mime = mime_content_type($files__value);
            $b64 = base64_encode(file_get_contents($files__value));
            if (stripos($mime, 'pdf') !== false || $mime === 'application/pdf') {
                $content[] = [
                    'type' => 'file',
                    'file' => [
                        'filename' => basename($files__value),
                        'file_data' => 'data:' . $mime . ';base64,' . $b64
                    ]
                ];
            } elseif (strpos($mime, 'image/') === 0) {
                $content[] = [
                    'type' => 'image_url',
                    'image_url' => ['url' => 'data:' . $mime . ';base64,' . $b64]
                ];
            }
        }
        return ['role' => 'user', 'content' => $content];
    }

    protected function addResponseToSession(mixed $response): void
    {
        if (
            !__::x($response ?? null) ||
            !__::x($response?->result ?? null) ||
            !__::x($response?->result?->choices ?? null) ||
            !is_array($response->result->choices) ||
            empty($response->result->choices)
        ) {
            return;
        }
        $message = $response->result->choices[0]->message ?? null;
        if ($message === null) {
            return;
        }
        $entry = ['role' => 'assistant', 'content' => $message->content ?? ''];
        $tool_calls = isset($message->tool_calls) ? json_decode(json_encode($message->tool_calls), true) : null;
        if (!empty($tool_calls)) {
            $entry['tool_calls'] = $tool_calls;
        }
        self::$sessions[$this->session_id][] = $entry;
    }

    protected function askThis(
        ?string $prompt = null,
        mixed $files = null,
        bool $add_prompt_to_session = true,
        ?string $prev_output_text = null,
        float $prev_costs = 0.0,
        int $length_continuation_count = 0
    ): array {
        $return = ['response' => null, 'success' => false, 'costs' => $prev_costs];

        if (__::nx($this->model) || __::nx($this->session_id) || ($add_prompt_to_session && __::nx($prompt))) {
            $return['response'] = 'data missing.';
            return $return;
        }

        if ($add_prompt_to_session === true) {
            $this->appendPromptToSession($prompt, $files);
        }

        $args = [
            'model' => $this->model,
            'messages' => self::$sessions[$this->session_id]
        ];

        $args = $this->applyTemperatureParameter($args);

        if (!empty($this->mcp_servers) && $this->mcp_servers_call_type === 'local') {
            $raw_tools = $this->buildLocalToolsArgs('parameters', false);
            $args['tools'] = [];
            foreach ($raw_tools as $tool) {
                $args['tools'][] = [
                    'type' => 'function',
                    'function' => $tool
                ];
            }
        }

        if ($this->stream === true) {
            $args['stream'] = true;
        }

        if (method_exists($this, 'modifyArgs')) {
            $args = $this->modifyArgs($args);
        }

        $this->log((int) round(strlen(json_encode($args)) / 3.5), 'ask with input token length');
        $this->log($args, 'ask');
        $response = $this->makeApiCall($args);
        if ($this->stream === true) {
            $response = $this->stream_response;
            // extract tool calls from reasoning_content OR content (llama.cpp/OpenRouter models emit
            // tool calls as XML in the reasoning field or content instead of tool_calls).
            // supports both qwen3 format (<tool_call>...<function=name>...<parameter=key>)
            // and minimax format (<minimax:tool_call>...<invoke name="name">...<parameter name="key">)
            $content_text = $response->result->choices[0]->message->content ?? '';
            $search_text = $this->stream_reasoning_buffer;
            if (str_contains($content_text, '<tool_call>') || str_contains($content_text, '<minimax:tool_call>')) {
                $search_text .= "\n" . $content_text;
            }
            if ($search_text !== '' && empty($response->result->choices[0]->message->tool_calls ?? [])) {
                $tool_calls = [];
                // match both standard and minimax tool_call blocks (closed and unclosed)
                if (
                    preg_match_all(
                        '/<(?:minimax:)?tool_call>\s*(.*?)(?:<\/(?:minimax:)?tool_call>|\z)/s',
                        $search_text,
                        $matches
                    )
                ) {
                    foreach ($matches[1] as $tc_xml) {
                        $name = null;
                        $arguments = '{}';
                        // extract function name:
                        // minimax: <invoke name="tool-name">
                        // qwen3:   <function=name>
                        // json:    "name": "..."
                        if (preg_match('/<invoke\s+name="([^"]+)"/', $tc_xml, $nm)) {
                            $name = $nm[1];
                        } elseif (preg_match('/<function=(\S+?)>/', $tc_xml, $nm)) {
                            $name = $nm[1];
                        } elseif (preg_match('/"name"\s*:\s*"([^"]+)"/', $tc_xml, $nm)) {
                            $name = $nm[1];
                        }
                        // extract arguments:
                        // minimax: <parameter name="key">value</parameter>
                        // qwen3:   <parameter=key>value</parameter>
                        // json:    {...}
                        if (
                            preg_match_all(
                                '/<parameter\s+name="(\S+?)">\s*([\s\S]*?)(?:\s*<\/parameter>|\s*<\/invoke|\s*<\/(?:minimax:)?tool_call|\z)/s',
                                $tc_xml,
                                $pm,
                                PREG_SET_ORDER
                            )
                        ) {
                            $args_map = [];
                            foreach ($pm as $p) {
                                $val = trim($p[2]);
                                $decoded = json_decode($val, true);
                                $args_map[$p[1]] = $decoded !== null ? $decoded : $val;
                            }
                            $arguments = json_encode($args_map, JSON_UNESCAPED_UNICODE);
                        } elseif (
                            preg_match_all(
                                '/<parameter=(\S+?)>\s*([\s\S]*?)(?:\s*<\/parameter>|\s*<\/function|\s*<\/tool_call|\z)/s',
                                $tc_xml,
                                $pm,
                                PREG_SET_ORDER
                            )
                        ) {
                            $args_map = [];
                            foreach ($pm as $p) {
                                $val = trim($p[2]);
                                $decoded = json_decode($val, true);
                                $args_map[$p[1]] = $decoded !== null ? $decoded : $val;
                            }
                            $arguments = json_encode($args_map, JSON_UNESCAPED_UNICODE);
                        } elseif (preg_match('/\{[\s\S]*\}/s', $tc_xml, $am)) {
                            $arguments = $am[0];
                        }
                        if ($name !== null) {
                            $tool_calls[] = (object) [
                                'id' => 'call_' . substr(md5($name . $arguments), 0, 8),
                                'type' => 'function',
                                'function' => (object) [
                                    'name' => $name,
                                    'arguments' => $arguments
                                ]
                            ];
                        }
                    }
                }
                if (!empty($tool_calls)) {
                    $response->result->choices[0]->message->tool_calls = $tool_calls;
                    $response->result->choices[0]->finish_reason = 'tool_calls';
                    // strip <tool_call> and <minimax:tool_call> blocks from content if they were there
                    if (isset($response->result->choices[0]->message->content)) {
                        $response->result->choices[0]->message->content = trim(
                            preg_replace(
                                '/<(?:minimax:)?tool_call>[\s\S]*?(?:<\/(?:minimax:)?tool_call>|$)/s',
                                '',
                                $response->result->choices[0]->message->content
                            )
                        );
                    }
                    $this->log(
                        count($tool_calls) . ' tool call(s) extracted from reasoning/content',
                        'reasoning_tool_calls'
                    );
                } elseif (empty($response->result->choices[0]->message->content ?? '')) {
                    // no tool calls and content empty: model put final answer into reasoning field
                    // strip any <think>...</think> wrappers and use reasoning as content
                    $final_text = $this->stream_reasoning_buffer;
                    $final_text = preg_replace('/<think>[\s\S]*?<\/think>\s*/', '', $final_text);
                    $final_text = trim($final_text);
                    if ($final_text !== '') {
                        $response->result->choices[0]->message->content = $final_text;
                        $this->log(
                            strlen($final_text) . ' chars promoted from reasoning to content',
                            'reasoning_content_promoted'
                        );
                    }
                }
            }
        }
        $this->log($response?->result ?? null, 'response');
        $this->addCosts($response, $return);

        $output_text = $prev_output_text !== null ? $prev_output_text : '';
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->choices ?? null) &&
            is_array($response->result->choices) &&
            !empty($response->result->choices)
        ) {
            $message = $response->result->choices[0]->message ?? null;
            if ($message !== null && __::x($message->content ?? null)) {
                $content_text = $message->content;
                if (is_string($content_text)) {
                    if (__::x($output_text ?? null)) {
                        $output_text .= PHP_EOL . PHP_EOL;
                    }
                    $output_text .= __::trim_whitespace($this->stripThinkingBlocks($content_text));
                }
            }
        }

        // handle finish_reason tool_calls for local tool loop
        if (
            $this->mcp_servers_call_type === 'local' &&
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->choices ?? null) &&
            is_array($response->result->choices) &&
            !empty($response->result->choices)
        ) {
            $finish_reason = $response->result->choices[0]->finish_reason ?? null;
            // accept when the provider sent the canonical "tool_calls" finish_reason,
            // OR (fallback for codex/proxy edge case where the terminal finish_reason
            // chunk is missing before [DONE]) when finish_reason is null but the
            // message already carries fully-parseable tool_call arguments. This
            // avoids retry-storms on deterministic stream-close bugs while still
            // failing closed on truly truncated streams (broken JSON → no accept).
            $message_tool_calls = $response->result->choices[0]->message->tool_calls ?? null;
            $tool_calls_complete = false;
            if ($finish_reason === null && is_array($message_tool_calls) && !empty($message_tool_calls)) {
                $tool_calls_complete = true;
                foreach ($message_tool_calls as $tc) {
                    $args = $tc->function->arguments ?? null;
                    if (!is_string($args)) {
                        $tool_calls_complete = false;
                        break;
                    }
                    // empty string is a valid no-arg call; otherwise must parse as JSON
                    if ($args !== '' && json_decode($args) === null && json_last_error() !== JSON_ERROR_NONE) {
                        $tool_calls_complete = false;
                        break;
                    }
                }
                if ($tool_calls_complete) {
                    $this->log('finish_reason=null but tool_calls have valid JSON — accepting', 'tool_calls_salvage');
                }
            }
            if ($finish_reason === 'tool_calls' || $tool_calls_complete) {
                $this->addResponseToSession($response);
                $return['response'] = $output_text ?: '';
                $return['success'] = true;
                return $return;
            }
        }

        if (__::nx($output_text ?? null)) {
            $this->log($response, 'failed');
            $error_msg = $this->extractErrorMessage($response);
            $return['response'] = $error_msg ?? 'No response from provider.';
            return $return;
        }

        // auto-continue when the model was cut off by the length limit
        $continued = $this->continueIfNotFinished(
            $response,
            $output_text,
            $return['costs'],
            $length_continuation_count
        );
        if ($continued !== null) {
            return $continued;
        }

        $return['response'] = $output_text;
        $return['success'] = true;

        $this->addResponseToSession($response);

        $return['response'] = $this->parseJson($return['response']);

        return $return;
    }

    protected function makeApiCall(?array $args = null): mixed
    {
        return __::curl(
            url: $this->url . '/chat/completions',
            data: $args,
            method: 'POST',
            headers: [
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            timeout: $this->timeout,
            stream_callback: $this->getStreamCallback()
        );
    }

    protected function modifyArgs(?array $args): ?array
    {
        return $this->modifyArgsLocal($args);
    }
}

/* compatible with the openai chat completions api */
class ai_llamacpp extends ai_openrouter
{
    public ?string $provider = 'llama.cpp';

    public ?string $title = 'llama.cpp';

    public ?string $name = 'llamacpp';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128"><path d="M17.94 125.975c-1.328-4.78-.267-14.335 2.11-18.992c1.062-2.084 1.062-2.084-.519-5.002c-4.036-7.453-4.31-18.279-.692-27.318c1.37-3.424 1.37-3.424.003-5.959c-5.619-10.41-3.848-22.86 4.502-31.66c3.62-3.815 3.62-3.815 3.176-6.515c-1.438-8.734.373-20.617 3.943-25.87c6.444-9.484 16.241-3.814 18.853 10.91c.353 1.99.704 3.704.78 3.807s1.778-.468 3.78-1.269c7.248-2.9 14.003-2.8 20.681.304c1.9.884 3.495 1.542 3.542 1.462c.048-.08.368-1.863.711-3.964C81.25 1 91.191-4.921 97.7 4.659c3.573 5.258 5.387 17.18 3.937 25.87c-.451 2.7-.451 2.7 2.894 6.21c8.381 8.793 10.265 21.377 4.73 31.595c-1.49 2.753-1.49 2.753-.197 5.694c3.853 8.763 3.673 20.372-.432 27.953c-1.581 2.918-1.581 2.918-.518 5.002c2.376 4.657 3.437 14.211 2.11 18.992c-.563 2.025-.563 2.025-4.115 2.025s-3.552 0-3.08-1.755c1.276-4.736.571-12.311-1.56-16.796c-2.466-5.184-2.465-5.131-.1-8.964c4.587-7.43 4.684-17.018.26-25.616c-2.064-4.01-2.05-4.469.254-7.891c8.675-12.891.254-30.435-14.981-31.21c-4.72-.24-4.72-.24-5.883-2.569c-6.442-12.9-25.928-13.5-33.46-1.031c-1.945 3.221-1.945 3.221-6.482 3.517C25.41 36.71 16.086 57.975 26.865 68.1c1.712 1.609 1.648 2.924-.33 6.769c-4.425 8.598-4.328 18.186.258 25.616c2.366 3.833 2.366 3.78-.098 8.964c-2.133 4.485-2.837 12.06-1.561 16.796c.472 1.755.472 1.755-3.08 1.755s-3.552 0-4.115-2.025zm20.79-97.129c4.504-.46 4.821-.985 4.378-7.23c-.634-8.923-3.835-15.995-6.251-13.808c-3.296 2.982-5.556 22.898-2.465 21.711c.447-.171 2.4-.474 4.338-.673m56.11-5.926c-.04-10.714-3.021-18.058-5.95-14.654c-2.634 3.062-5.13 16.32-3.592 19.072c.521.932 7.096 2.471 9.024 2.112c.325-.06.53-2.652.517-6.53zM56.213 83.552c-19.558-5.52-13.155-29.651 7.868-29.651c18.95 0 27.119 20.158 11.417 28.175c-4.221 2.156-14.179 2.918-19.285 1.476M73.087 78.2c11.73-5.37 5.16-19.86-9.006-19.86c-16.164 0-20.925 17.235-5.813 21.047c3.772.951 11.5.332 14.82-1.187zm-10.896-6.23c0-1.564-.343-2.395-1.35-3.27c-2.518-2.19-1.387-3.196 3.51-3.123c3.993.058 5.126 1.477 2.596 3.25c-.922.647-1.246 1.397-1.246 2.888c0 1.877-.119 2.025-1.755 2.183c-1.707.164-1.755.112-1.755-1.928m-26.214-9.017c-1.516-1.516-1.687-3.43-.51-5.706c2.28-4.409 8.364-3.014 8.364 1.918c0 4.24-4.994 6.648-7.854 3.788M85.91 62.7c-2.063-2.063-2.168-4.901-.253-6.816c2.224-2.224 5.513-1.587 7.04 1.363c2.651 5.13-2.761 9.478-6.787 5.453"/></svg>
    SVG;

    protected ?string $url = 'http://localhost:8080/v1';

    public ?bool $supports_mcp_remote = false;

    public ?bool $supports_stream = true;

    public array $models = [];

    public function fetchModels(): array
    {
        $models = [];
        $response = __::curl(
            url: $this->url . '/models',
            method: 'GET',
            headers: [
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            timeout: $this->timeout
        );
        $this->log($response);
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->data ?? null) &&
            is_array($response->result->data)
        ) {
            foreach ($response->result->data as $models__value) {
                if (__::x($models__value?->id ?? null)) {
                    // n_ctx_train is the model's training ctx; at runtime
                    // llama.cpp divides the deployed n_ctx across n_parallel
                    // slots, so per-request budget is n_ctx/n_slots. halve as
                    // a conservative fallback when slot info is not exposed.
                    $context_length = (int) (((int) ($models__value->meta->n_ctx_train ?? 32768)) / 2);
                    $name = $models__value->id;
                    // strip split-shard suffix: "Model-0001-of-0004.gguf" → "Model"
                    $name = preg_replace('/-\d{1,10}-of-\d{1,10}(\.gguf)$/i', '', $name);
                    // completely remove .gguf
                    $name = preg_replace('/\.gguf$/i', '', $name);
                    $models[] = [
                        'name' => $name,
                        'context_length' => $context_length,
                        'supports_tools' => true
                    ];
                }
            }
        }
        if (!empty($models)) {
            usort($models, function ($a, $b) {
                return $a['name'] <=> $b['name'];
            });
            $models[0]['default'] = true;
            $models[0]['test'] = true;
        }
        return $models;
    }

    public function ping(): bool
    {
        try {
            $response = __::curl(
                url: rtrim($this->url, '/') . '/models',
                method: 'GET',
                headers: ['Authorization' => 'Bearer ' . $this->api_key],
                timeout: 30
            );
            return ($response->status ?? 0) >= 200 &&
                ($response->status ?? 0) < 300 &&
                __::x($response?->result?->data ?? null);
        } catch (\Exception) {
            return false;
        }
    }

    protected function modifyArgs(?array $args): ?array
    {
        return $this->modifyArgsLocal($args);
    }
}

/* compatible with the openai api */
class ai_lmstudio extends ai_openai
{
    public ?string $provider = 'Element Labs';

    public ?string $title = 'LM Studio';

    public ?string $name = 'lmstudio';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 293"><path fill="#409eff" d="M255.947 209.282c-.073 12.126-6.371 14.83-6.371 14.83s-108.694 62.807-115.76 66.743c-7.003 3.005-11.683 0-11.683 0S8.401 224.894 4.25 221.995C.098 219.094 0 214.577 0 214.577S.115 83.965 0 77.917c-.114-6.05 7.434-10.595 7.434-10.595L121.071 1.641c6.996-3.692 13.807 0 13.807 0s100.386 58.351 111.511 64.623c10.904 5.184 9.558 15.89 9.558 15.89s.068 115.858 0 127.128m-45.37-131.09c-23.267-13.391-78.08-45.15-78.08-45.15s-5.347-2.89-10.84 0L32.44 84.443s-5.927 3.558-5.837 8.292c.09 4.733 0 106.952 0 106.952s.076 3.535 3.336 5.804c3.26 2.268 92.553 53.89 92.553 53.89s3.675 2.353 9.172 0c5.548-3.08 90.886-52.232 90.886-52.232s4.946-2.118 5.003-11.608c.016-2.736.022-13.36.023-26.706l-100.472 60.881v-23.29c0-9.567 7.406-15.88 7.406-15.88l88.869-53.551c3.353-3.502 4.045-9.112 4.188-11.234c-.003-9.728-.007-18.226-.01-23.61L127.104 163.02v-24.35c0-9.566 6.348-13.762 6.348-13.762z"/></svg>
    SVG;

    protected ?string $url = 'http://localhost:1234/v1';

    public ?bool $supports_mcp_remote = true;

    public ?bool $supports_stream = true;

    public array $models = [];

    public function fetchModels(): array
    {
        $models = [];
        $response = __::curl(
            url: rtrim(str_replace('/v1', '/api/v1', $this->url), '/') . '/models',
            method: 'GET',
            headers: [
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            timeout: $this->timeout
        );
        $this->log($response);
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->models ?? null) &&
            is_array($response->result->models)
        ) {
            foreach ($response->result->models as $models__value) {
                // only include llm models, skip embeddings and other types
                if (!isset($models__value->type) || $models__value->type !== 'llm') {
                    continue;
                }
                if (__::x($models__value?->key ?? null)) {
                    $context_length = (int) ($models__value->max_context_length ?? 32768);
                    $models[] = [
                        'name' => $models__value->key,
                        'context_length' => $context_length,
                        'supports_tools' => true
                    ];
                }
            }
        }
        // fallback: OpenAI-compatible format (llama.cpp, etc.)
        if (
            empty($models) &&
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->data ?? null) &&
            is_array($response->result->data)
        ) {
            foreach ($response->result->data as $models__value) {
                if (__::x($models__value?->id ?? null)) {
                    // n_ctx_train is the model's training ctx; at runtime
                    // llama.cpp divides the deployed n_ctx across n_parallel
                    // slots, so per-request budget is n_ctx/n_slots. halve as
                    // a conservative fallback when slot info is not exposed.
                    $context_length = (int) (((int) ($models__value->meta->n_ctx_train ?? 32768)) / 2);
                    $models[] = [
                        'name' => $models__value->id,
                        'context_length' => $context_length,
                        'supports_tools' => true
                    ];
                }
            }
        }
        if (!empty($models)) {
            // sort by name
            usort($models, function ($a, $b) {
                return $a['name'] <=> $b['name'];
            });
            $models[0]['default'] = true;
            $models[0]['test'] = true;
        }
        return $models;
    }

    protected function loadModel(?string $model): void
    {
        if (empty($model)) {
            return;
        }
        // check via API whether the model is already loaded
        $response = __::curl(
            url: rtrim(str_replace('/v1', '/api/v1', $this->url), '/') . '/models',
            method: 'GET',
            headers: [
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            timeout: $this->timeout
        );
        $this->log($response);
        // default context length; overridden by max_context_length from API if available
        $context_length = 32768;
        if (
            __::x($response ?? null) &&
            __::x($response?->result ?? null) &&
            __::x($response?->result?->models ?? null) &&
            is_array($response->result->models)
        ) {
            foreach ($response->result->models as $models__value) {
                if (isset($models__value->key) && $models__value->key === $model) {
                    if (!empty($models__value->loaded_instances)) {
                        // model is already loaded, nothing to do
                        return;
                    }
                    // use max_context_length from API, capped at 65536 to limit memory usage
                    if (!empty($models__value->max_context_length)) {
                        $context_length = min((int) $models__value->max_context_length, 65536);
                    }
                }
            }
        }
        $response = __::curl(
            url: rtrim(str_replace('/v1', '/api/v1', $this->url), '/') . '/models/load',
            data: [
                'model' => $model,
                'context_length' => $context_length
            ],
            method: 'POST',
            headers: [
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            timeout: $this->timeout
        );
        $this->log($response);
    }

    protected function modifyArgs(?array $args): ?array
    {
        return $this->modifyArgsLocal($args);
    }
}

/* compatible with the openai api */
class ai_nvidia extends ai_openrouter
{
    public ?string $provider = 'NVIDIA';

    public ?string $title = 'NVIDIA';

    public ?string $name = 'nvidia';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="#76b900" d="M8.948 8.798v-1.43a7 7 0 0 1 .424-.018c3.922-.124 6.493 3.374 6.493 3.374s-2.774 3.851-5.75 3.851a3.7 3.7 0 0 1-1.158-.185v-4.346c1.528.185 1.837.857 2.747 2.385l2.04-1.714s-1.492-1.952-4-1.952a6 6 0 0 0-.796.035m0-4.735v2.138l.424-.027c5.45-.185 9.01 4.47 9.01 4.47s-4.08 4.964-8.33 4.964a6.5 6.5 0 0 1-1.095-.097v1.325c.3.035.61.062.91.062c3.957 0 6.82-2.023 9.593-4.408c.459.371 2.34 1.263 2.73 1.652c-2.633 2.208-8.772 3.984-12.253 3.984c-.335 0-.653-.018-.971-.053v1.864H24V4.063zm0 10.326v1.131c-3.657-.654-4.673-4.46-4.673-4.46s1.758-1.944 4.673-2.262v1.237H8.94c-1.528-.186-2.73 1.245-2.73 1.245s.68 2.412 2.739 3.11M2.456 10.9s2.164-3.197 6.5-3.533V6.201C4.153 6.59 0 10.653 0 10.653s2.35 6.802 8.948 7.42v-1.237c-4.84-.6-6.492-5.936-6.492-5.936"/></svg>
    SVG;

    protected ?string $url = 'https://integrate.api.nvidia.com/v1';

    public ?bool $supports_mcp_remote = false;

    public ?bool $supports_stream = true;

    public array $models = [
        [
            'name' => 'minimaxai/minimax-m2.5',
            'context_length' => 192000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => true,
            'test' => true
        ],
        [
            'name' => 'qwen/qwen3-next-80b-a3b-instruct',
            'context_length' => 256000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'qwen/qwen3-next-80b-a3b-thinking',
            'context_length' => 256000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'qwen/qwen3.5-122b-a10b',
            'context_length' => 256000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'qwen/qwen3.5-397b-a17b',
            'context_length' => 256000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'qwen/qwen2.5-coder-32b-instruct',
            'context_length' => 32000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => false,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'google/gemma-3-27b-it',
            'context_length' => 128000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'google/gemma-3-4b-it',
            'context_length' => 4000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => false,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'google/gemma-3n-e2b-it',
            'context_length' => 32000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'google/gemma-3n-e4b-it',
            'context_length' => 4000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'google/gemma-2-2b-it',
            'context_length' => 4000,
            'max_output_tokens' => 4096,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => false,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ]
    ];

    public function fetchModels(): array
    {
        // NIM catalog discovery is opaque (no pricing, no consistent context
        // metadata across model families). Return the static list above so
        // callers always get usable model entries.
        return $this->models;
    }

    public function ping(): bool
    {
        try {
            $response = $this->ask('Test');
            return $response['success'];
        } catch (\Exception) {
            return false;
        }
    }
}

class ai_elevenlabs extends ai_openai
{
    public ?string $provider = 'ElevenLabs';

    public ?string $title = 'ElevenLabs';

    public ?string $name = 'elevenlabs';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M7 4h3v16H7zm7 0h3v16h-3z"/></svg>
    SVG;

    protected ?string $url = 'https://api.elevenlabs.io/v1';

    public ?bool $supports_mcp_remote = false;

    public ?bool $supports_stream = false;

    public array $models = [
        [
            'name' => 'eleven_v3',
            'supports_tools' => false,
            'costs' => ['audio' => 0.0003],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => true,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'eleven_turbo_v2_5',
            'supports_tools' => false,
            'costs' => ['audio' => 0.00005],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => true,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => true,
            'test' => true
        ],
        [
            'name' => 'eleven_turbo_v2',
            'supports_tools' => false,
            'costs' => ['audio' => 0.00005],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => true,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'eleven_flash_v2_5',
            'supports_tools' => false,
            'costs' => ['audio' => 0.000033],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => true,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'eleven_flash_v2',
            'supports_tools' => false,
            'costs' => ['audio' => 0.000033],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => true,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'eleven_multilingual_v2',
            'supports_tools' => false,
            'costs' => ['audio' => 0.00018],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => true,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'eleven_multilingual_v1',
            'supports_tools' => false,
            'costs' => ['audio' => 0.000165],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => true,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'eleven_monolingual_v1',
            'supports_tools' => false,
            'costs' => ['audio' => 0.000165],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => true,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => false,
            'test' => false
        ],
        [
            // speech-to-text (Scribe) — used via ask() with an audio attachment
            'name' => 'scribe_v1',
            'supports_tools' => false,
            'costs' => ['audio' => 0],
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => true,
            'default' => false,
            'test' => true
        ]
    ];

    public function fetchModels(): array
    {
        $models = [];
        $response = __::curl(
            url: $this->url . '/models',
            method: 'GET',
            headers: ['xi-api-key' => $this->api_key],
            timeout: $this->timeout
        );
        $this->log($response);
        $list = $response?->result ?? null;
        if (!is_array($list)) {
            $code = $response?->status ?? null;
            $this->log(
                '⚠️ elevenlabs fetchModels returned empty — HTTP ' .
                    var_export($code, true) .
                    ' (check api key / quota / connectivity)'
            );
            return $models;
        }
        foreach ($list as $m) {
            $name = $m->model_id ?? null;
            if (!is_string($name)) {
                continue;
            }
            if (!($m->can_do_text_to_speech ?? false)) {
                continue;
            }
            $entry = ['name' => $name, 'context_length' => 128000];
            foreach ($this->models as $defined) {
                if (($defined['name'] ?? null) === $name) {
                    $entry = array_merge($defined, ['name' => $name]);
                    if (!isset($entry['context_length'])) {
                        $entry['context_length'] = 128000;
                    }
                    break;
                }
            }
            $models[] = $entry;
        }
        // the /models endpoint only lists text-to-speech models — append
        // statically-defined speech-to-text models (Scribe) so they stay
        // discoverable and consistent with the static catalog.
        foreach ($this->models as $defined) {
            if (($defined['supports_audio_to_text'] ?? false) !== true) {
                continue;
            }
            $name = $defined['name'] ?? null;
            if (!is_string($name)) {
                continue;
            }
            $already = false;
            foreach ($models as $existing) {
                if (($existing['name'] ?? null) === $name) {
                    $already = true;
                    break;
                }
            }
            if ($already) {
                continue;
            }
            $entry = $defined;
            if (!isset($entry['context_length'])) {
                $entry['context_length'] = 128000;
            }
            $models[] = $entry;
        }
        return $models;
    }

    public function ask(?string $prompt = null, mixed $files = null): array
    {
        $list = is_array($files) ? $files : ($files !== null ? [$files] : []);
        $audio = null;
        foreach ($list as $f) {
            if (is_string($f) && file_exists($f) && strpos((string) mime_content_type($f), 'audio/') === 0) {
                $audio = $f;
                break;
            }
        }
        if ($audio === null) {
            return [
                'response' => 'elevenlabs ask() error: provider is speech-to-text only — pass an audio file via the $files argument.',
                'success' => false,
                'costs' => 0.0
            ];
        }
        $ch = curl_init($this->url . '/speech-to-text');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'model_id' => $this->model,
                'file' => new \CURLFile($audio, (string) mime_content_type($audio), basename($audio))
            ],
            CURLOPT_HTTPHEADER => ['xi-api-key: ' . $this->api_key],
            CURLOPT_TIMEOUT => $this->timeout ?? 300
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($raw === false || $http >= 400) {
            $msg = 'elevenlabs stt HTTP ' . $http . ' err=' . ($err ?: '') . ' body=' . substr((string) $raw, 0, 500);
            $this->log('⛔ ' . $msg);
            return ['response' => $msg, 'success' => false, 'costs' => 0.0];
        }
        $json = json_decode((string) $raw, true);
        $text = is_array($json) ? (string) ($json['text'] ?? '') : '';
        return ['response' => $text, 'success' => true, 'costs' => 0.0];
    }

    protected function audioThis(
        ?string $prompt = null,
        ?string $voice = null,
        ?float $speed = null,
        ?string $output_file = null
    ): array {
        // default voice: Rachel
        $voice_id = $voice !== null && $voice !== '' ? $voice : '21m00Tcm4TlvDq8ikWAM';
        $format = 'mp3_44100_128';
        if ($output_file !== null) {
            $ext = strtolower((string) pathinfo($output_file, PATHINFO_EXTENSION));
            // wav/flac intentionally not mapped — elevenlabs only emits raw pcm without container
            $format_map = [
                'mp3' => 'mp3_44100_128',
                'opus' => 'opus_48000_128',
                'pcm' => 'pcm_44100'
            ];
            if (isset($format_map[$ext])) {
                $format = $format_map[$ext];
            }
        }
        $endpoint = $this->url . '/text-to-speech/' . rawurlencode($voice_id) . '?output_format=' . urlencode($format);
        $payload = ['text' => (string) $prompt, 'model_id' => $this->model];
        if ($speed !== null) {
            $payload['voice_settings'] = ['speed' => $speed];
        }
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'xi-api-key: ' . $this->api_key,
                'Content-Type: application/json',
                'Accept: audio/mpeg'
            ],
            CURLOPT_TIMEOUT => $this->timeout ?? 300
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($raw === false || $http >= 400) {
            $msg = 'elevenlabs audio HTTP ' . $http . ' err=' . ($err ?: '') . ' body=' . substr((string) $raw, 0, 500);
            $this->log('⛔ ' . $msg);
            return ['response' => $msg, 'success' => false, 'costs' => 0.0];
        }
        $costs = 0.0;
        foreach ($this->models as $m) {
            if (($m['name'] ?? null) === $this->model) {
                $costs = (float) ($m['costs']['audio'] ?? 0 ?: 0) * mb_strlen((string) $prompt);
                break;
            }
        }
        if ($output_file !== null) {
            if (file_put_contents($output_file, $raw) === false) {
                $this->log('⛔ elevenlabs audio: failed to write output_file ' . $output_file);
                return ['response' => null, 'success' => false, 'costs' => 0.0];
            }
            return ['response' => $output_file, 'success' => true, 'costs' => $costs];
        }
        return ['response' => base64_encode((string) $raw), 'success' => true, 'costs' => $costs];
    }
}

class ai_test extends ai_anthropic
{
    public ?string $provider = 'aihelper';

    public ?string $title = 'Test';

    public ?string $name = 'test';

    public ?string $icon = null;

    protected ?string $url = null;

    public ?bool $supports_mcp_remote = false;

    public ?bool $supports_stream = true;

    public array $models = [
        [
            'name' => 'test-model-1',
            'context_length' => 128000,
            'max_output_tokens' => 16384,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'supports_tools' => false,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'default' => true,
            'test' => true
        ]
    ];

    public function fetchModels(): array
    {
        return array_map(function ($model) {
            return ['name' => $model['name'], 'context_length' => $model['context_length']];
        }, $this->models);
    }

    protected function makeApiCall(?array $args = null): mixed
    {
        static $call_count = 0;
        if ($call_count > 0) {
            $delay = rand(3, 7);
            $this->log('simulating pause_turn delay: ' . $delay . ' seconds');
            sleep($delay);
        }
        $call_count++;

        // determine pause_turn behavior based on session history
        $history = self::$sessions[$this->session_id] ?? [];
        $pause_turn_count = 0;
        foreach ($history as $history__value) {
            $role = is_array($history__value) ? $history__value['role'] ?? null : $history__value->role ?? null;
            if ($role === 'assistant') {
                $pause_turn_count++;
            }
        }

        // generate multiple sentences
        $faker = \Faker\Factory::create();
        $sentences = [];
        $num_sentences = rand(2, 4);
        for ($sentence__key = 0; $sentence__key < $num_sentences; $sentence__key++) {
            $sentences[] = $faker->sentence(rand(10, 25));
        }

        // ensure between 3 and 5 pause_turns are simulated before final end_turn
        $min_required_pause_turns = 3;
        $max_required_pause_turns = 5;
        $max_pause_turns = rand($min_required_pause_turns, $max_required_pause_turns);
        $use_pause_turn = $pause_turn_count < $max_pause_turns;

        // decide which sentence to stop at
        if ($use_pause_turn) {
            $sentences_to_send = [array_shift($sentences)];
            $stop_reason = 'pause_turn';
        } else {
            $sentences_to_send = $sentences;
            $stop_reason = 'end_turn';
        }

        $mock_text = implode(' ', $sentences_to_send);

        // mock non-streaming response
        if ($this->stream === false) {
            return (object) [
                'result' => (object) [
                    'id' => 'msg_' . uniqid(),
                    'type' => 'message',
                    'role' => 'assistant',
                    'model' => $this->model,
                    'content' => [
                        (object) [
                            'type' => 'text',
                            'text' => $mock_text
                        ]
                    ],
                    'stop_reason' => $stop_reason,
                    'stop_sequence' => null,
                    'usage' => (object) [
                        'input_tokens' => 150,
                        'cache_creation_input_tokens' => 0,
                        'cache_read_input_tokens' => 0,
                        'output_tokens' => 50
                    ]
                ]
            ];
        }

        // mock streaming response by calling stream callback
        $stream_callback = $this->getStreamCallback();
        if ($stream_callback !== null) {
            // split mock text into word chunks for streaming simulation
            $words = explode(' ', $mock_text);
            $text_chunks = [];
            for ($words__key = 0; $words__key < count($words); $words__key++) {
                if ($words__key === 0) {
                    $text_chunks[] = $words[$words__key];
                } else {
                    $text_chunks[] = ' ' . $words[$words__key];
                }
            }

            // simulate streaming chunks from anthropic
            $mock_chunks = [
                "event: message_start\ndata: " .
                json_encode([
                    'type' => 'message_start',
                    'message' => [
                        'id' => 'msg_' . uniqid(),
                        'type' => 'message',
                        'role' => 'assistant',
                        'model' => $this->model,
                        'content' => [],
                        'stop_reason' => null,
                        'stop_sequence' => null,
                        'usage' => [
                            'input_tokens' => 150,
                            'cache_creation_input_tokens' => 0,
                            'cache_read_input_tokens' => 0,
                            'output_tokens' => 1
                        ]
                    ]
                ]) .
                "\n\n",
                "event: content_block_start\ndata: " .
                json_encode([
                    'type' => 'content_block_start',
                    'index' => 0,
                    'content_block' => ['type' => 'text', 'text' => '']
                ]) .
                "\n\n"
            ];

            // add dynamic content_block_delta events for each word chunk with jitter
            foreach ($text_chunks as $text_chunk__value) {
                $mock_chunks[] =
                    "event: content_block_delta\ndata: " .
                    json_encode([
                        'type' => 'content_block_delta',
                        'index' => 0,
                        'delta' => ['type' => 'text_delta', 'text' => $text_chunk__value]
                    ]) .
                    "\n\n";
                usleep(rand(20000, 80000));
            }

            // add closing events
            $mock_chunks[] =
                "event: message_delta\ndata: " .
                json_encode([
                    'type' => 'message_delta',
                    'delta' => ['stop_reason' => $stop_reason, 'stop_sequence' => null],
                    'usage' => ['output_tokens' => 50]
                ]) .
                "\n\n";
            $mock_chunks[] =
                "event: content_block_stop\ndata: " .
                json_encode([
                    'type' => 'content_block_stop',
                    'index' => 0
                ]) .
                "\n\n";
            $mock_chunks[] = "event: message_stop\ndata: " . json_encode(['type' => 'message_stop']) . "\n\n";

            foreach ($mock_chunks as $mock_chunk__value) {
                $stream_callback($mock_chunk__value);
            }
        }

        return (object) ['result' => (object) []];
    }
}

class ai_codex extends ai_openrouter
{
    public ?string $provider = 'Codex';
    public ?string $title = 'Codex';
    public ?string $name = 'codex';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 260"><path d="M239.184 106.203a64.72 64.72 0 0 0-5.576-53.103C219.452 28.459 191 15.784 163.213 21.74A65.586 65.586 0 0 0 52.096 45.22a64.72 64.72 0 0 0-43.23 31.36c-14.31 24.602-11.061 55.634 8.033 76.74a64.67 64.67 0 0 0 5.525 53.102c14.174 24.65 42.644 37.324 70.446 31.36a64.72 64.72 0 0 0 48.754 21.744c28.481.025 53.714-18.361 62.414-45.481a64.77 64.77 0 0 0 43.229-31.36c14.137-24.558 10.875-55.423-8.083-76.483m-97.56 136.338a48.4 48.4 0 0 1-31.105-11.255l1.535-.87l51.67-29.825a8.6 8.6 0 0 0 4.247-7.367v-72.85l21.845 12.636c.218.111.37.32.409.563v60.367c-.056 26.818-21.783 48.545-48.601 48.601M37.158 197.93a48.35 48.35 0 0 1-5.781-32.589l1.534.921l51.722 29.826a8.34 8.34 0 0 0 8.441 0l63.181-36.425v25.221a.87.87 0 0 1-.358.665l-52.335 30.184c-23.257 13.398-52.97 5.431-66.404-17.803M23.549 85.38a48.5 48.5 0 0 1 25.58-21.333v61.39a8.29 8.29 0 0 0 4.195 7.316l62.874 36.272l-21.845 12.636a.82.82 0 0 1-.767 0L41.353 151.53c-23.211-13.454-31.171-43.144-17.804-66.405zm179.466 41.695l-63.08-36.63L161.73 77.86a.82.82 0 0 1 .768 0l52.233 30.184a48.6 48.6 0 0 1-7.316 87.635v-61.391a8.54 8.54 0 0 0-4.4-7.213m21.742-32.69l-1.535-.922l-51.619-30.081a8.39 8.39 0 0 0-8.492 0L99.98 99.808V74.587a.72.72 0 0 1 .307-.665l52.233-30.133a48.652 48.652 0 0 1 72.236 50.391zM88.061 139.097l-21.845-12.585a.87.87 0 0 1-.41-.614V65.685a48.652 48.652 0 0 1 79.757-37.346l-1.535.87l-51.67 29.825a8.6 8.6 0 0 0-4.246 7.367zm11.868-25.58L128.067 97.3l28.188 16.218v32.434l-28.086 16.218l-28.188-16.218z"/></svg>
    SVG;
    protected ?string $url = 'http://127.0.0.1:8317/v1';

    public ?bool $supports_mcp_remote = false;

    public ?bool $supports_stream = true;

    public function fetchModels(): array
    {
        $models = parent::fetchModels();
        $efforts = ['low', 'medium', 'high', 'xhigh', 'auto', 'none'];
        $expanded = [];
        foreach ($models as $model) {
            $model['supports_tools'] = true;
            $model['supports_temperature'] = true;
            $expanded[] = $model;
            if (!str_starts_with((string) $model['name'], 'gpt-5')) {
                continue;
            }
            foreach ($efforts as $effort) {
                $variant = $model;
                $variant['name'] = $model['name'] . '(' . $effort . ')';
                $expanded[] = $variant;
            }
        }
        return $expanded;
    }

    public function ping(): bool
    {
        try {
            $response = __::curl(
                url: $this->url . '/models',
                method: 'GET',
                headers: ['Authorization' => 'Bearer ' . $this->api_key],
                timeout: 30
            );
            return ($response->status ?? 0) >= 200 && ($response->status ?? 0) < 300;
        } catch (\Exception) {
            return false;
        }
    }
}
