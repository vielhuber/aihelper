<?php
declare(strict_types=1);

use vielhuber\aihelper\aihelper;
use vielhuber\stringhelper\__;

class Test extends \PHPUnit\Framework\TestCase
{
    protected $run_count = 3;

    private function retryAihelper(array $outcomes): object
    {
        return new class ($outcomes) extends aihelper {
            public int $attempts = 0;
            public array $promptAdditions = [];

            public function __construct(private array $outcomes)
            {
                $this->model = 'test';
                $this->session_id = 'retry-test';
                $this->max_tries = 1;
            }

            protected function askThis(
                ?string $prompt = null,
                mixed $files = null,
                bool $add_prompt_to_session = true,
                ?string $prev_output_text = null,
                float $prev_costs = 0.0,
                int $length_continuation_count = 0
            ): array {
                $this->attempts++;
                $this->promptAdditions[] = $add_prompt_to_session;
                $outcome = array_shift($this->outcomes);
                if ($outcome === 'stream_after_text') {
                    $this->stream_text_emitted_since_tool = true;
                    throw new \RuntimeException('stream disconnected before completion');
                }
                if ($outcome === 'mcp_startup_after_text') {
                    $this->stream_text_emitted_since_tool = true;
                    throw new \RuntimeException(
                        'required MCP servers failed to initialize: charly: empty sse stream, when process initialize response'
                    );
                }
                if (is_string($outcome)) {
                    throw new \RuntimeException($outcome);
                }
                if (is_array($outcome)) {
                    return $outcome;
                }
                return ['response' => 'ok', 'success' => true, 'costs' => $prev_costs];
            }

            protected function makeApiCall(?array $args = null): mixed
            {
                return null;
            }

            protected function bringPromptInFormat(string $prompt, mixed $files = null): array
            {
                return [];
            }

            protected function addResponseToSession(mixed $response): void {}

            protected function retryBackoffSeconds(int $attempt, bool $transient, bool $authUnavailable = false): int
            {
                return 0;
            }

            public function authenticationIsExpired(array $auth): bool
            {
                return $this->isCliAuthenticationExpired($auth);
            }

            public function cliUsageCacheKey(string $provider, string $tool): string
            {
                $this->name = $provider;
                return $this->getCliUsageCacheKey($tool);
            }

            public function localTools(string $url): array
            {
                $this->mcp_servers = [['url' => $url]];
                return $this->buildLocalToolsArgs();
            }
        };
    }

    private function toolImageAihelper(string $provider, array $mcpResponse): object
    {
        return new class ($provider, $mcpResponse) extends aihelper {
            public static array $mcpResponse = [];

            public function __construct(string $provider, array $mcpResponse)
            {
                self::$mcpResponse = $mcpResponse;
                $this->name = $provider;
                $this->model = 'test';
                $this->session_id = 'tool-image-test-' . $provider;
                $this->max_tries = 1;
                $this->mcp_servers_tools_map = [
                    'render_image' => [
                        'url' => 'https://example.test/mcp',
                        'authorization_token' => null
                    ]
                ];
                if ($provider === 'google') {
                    self::$sessions[$this->session_id] = [
                        [
                            'role' => 'model',
                            'parts' => [['functionCall' => ['name' => 'render_image', 'args' => []]]]
                        ]
                    ];
                    return;
                }
                if (in_array($provider, ['anthropic', 'xai', 'deepseek'], true)) {
                    self::$sessions[$this->session_id] = [
                        [
                            'role' => 'assistant',
                            'content' => [
                                ['type' => 'tool_use', 'id' => 'call_1', 'name' => 'render_image', 'input' => []]
                            ]
                        ]
                    ];
                    return;
                }
                if (in_array($provider, ['openrouter', 'llamacpp', 'nvidia', 'cliproxyapi'], true)) {
                    self::$sessions[$this->session_id] = [
                        [
                            'role' => 'assistant',
                            'tool_calls' => [
                                [
                                    'id' => 'call_1',
                                    'function' => ['name' => 'render_image', 'arguments' => '{}']
                                ]
                            ]
                        ]
                    ];
                    return;
                }
                self::$sessions[$this->session_id] = [
                    ['type' => 'function_call', 'call_id' => 'call_1', 'name' => 'render_image', 'arguments' => '{}']
                ];
            }

            public static function callMcpTool(
                ?string $name = null,
                ?array $args = [],
                ?string $url = null,
                ?string $authorization_token = null
            ): ?array {
                return self::$mcpResponse;
            }

            public function runToolLoop(): array
            {
                return $this->runLocalToolLoop(['response' => '', 'success' => true, 'costs' => 0.0]);
            }

            public function session(): array
            {
                return self::$sessions[$this->session_id];
            }

            protected function askThis(
                ?string $prompt = null,
                mixed $files = null,
                bool $add_prompt_to_session = true,
                ?string $prev_output_text = null,
                float $prev_costs = 0.0,
                int $length_continuation_count = 0
            ): array {
                if ($this->name === 'google') {
                    self::$sessions[$this->session_id][] = ['role' => 'model', 'parts' => [['text' => 'done']]];
                }
                if (in_array($this->name, ['anthropic', 'xai', 'deepseek'], true)) {
                    self::$sessions[$this->session_id][] = [
                        'role' => 'assistant',
                        'content' => [['type' => 'text', 'text' => 'done']]
                    ];
                }
                if (in_array($this->name, ['openrouter', 'llamacpp', 'nvidia', 'cliproxyapi'], true)) {
                    self::$sessions[$this->session_id][] = ['role' => 'assistant', 'content' => 'done'];
                }
                if (
                    !in_array(
                        $this->name,
                        ['google', 'anthropic', 'xai', 'deepseek', 'openrouter', 'llamacpp', 'nvidia', 'cliproxyapi'],
                        true
                    )
                ) {
                    self::$sessions[$this->session_id][] = ['type' => 'message', 'role' => 'assistant', 'content' => []];
                }
                return ['response' => 'done', 'success' => true, 'costs' => $prev_costs];
            }

            protected function makeApiCall(?array $args = null): mixed
            {
                return null;
            }

            protected function bringPromptInFormat(string $prompt, mixed $files = null): array
            {
                return [];
            }

            protected function addResponseToSession(mixed $response): void {}
        };
    }

    public static function setUpBeforeClass(): void
    {
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
        }
    }

    function log(mixed $msg): void
    {
        if (!is_string($msg)) {
            $msg = serialize($msg);
        }
        fwrite(STDERR, print_r($msg . PHP_EOL, true));
    }

    function isCi(): bool
    {
        return ($_SERVER['CI'] ?? '') == 'true' ||
            ($_ENV['CI'] ?? '') == 'true' ||
            getenv('CI') == 'true' ||
            getenv('ACT_TOOLSDIRECTORY') != '';
    }

    // the cli harnesses are installed on the host machine, never on a ci runner
    function skipOnCi(): void
    {
        if ($this->isCi()) {
            $this->markTestSkipped('Skipped.');
        }
    }

    function skipIfMissingEnv(string $key, bool $force): bool
    {
        if (($_SERVER[$key] ?? '') !== '') {
            return false;
        }
        if ($force === true) {
            return true;
        }
        $this->markTestSkipped('Skipped.');
        return true;
    }

    private function abortAihelper(?callable $abort): object
    {
        return new class ($abort) extends aihelper {
            public int $attempts = 0;

            public function __construct(?callable $abort)
            {
                $this->model = 'test';
                $this->session_id = 'abort-test';
                $this->max_tries = 3;
                $this->setAbortCallback($abort);
            }

            public function probeAbort(): bool
            {
                return $this->shouldAbort();
            }

            protected function askThis(
                ?string $prompt = null,
                mixed $files = null,
                bool $add_prompt_to_session = true,
                ?string $prev_output_text = null,
                float $prev_costs = 0.0,
                int $length_continuation_count = 0
            ): array {
                $this->attempts++;
                if ($this->shouldAbort()) {
                    $this->aborted = true;
                    return ['response' => null, 'success' => false, 'costs' => 0.0];
                }
                return ['response' => 'ok', 'success' => true, 'costs' => 0.0];
            }

            protected function makeApiCall(?array $args = null): mixed
            {
                return null;
            }

            protected function bringPromptInFormat(string $prompt, mixed $files = null): array
            {
                return [];
            }

            protected function addResponseToSession(mixed $response): void {}
        };
    }

    function test__abort_callback_stops_the_request_without_retrying(): void
    {
        $ai = $this->abortAihelper(fn(): bool => true);

        $result = $ai->ask('test');

        $this->assertTrue($result['aborted']);
        $this->assertFalse($result['success']);
        $this->assertNull($result['response']);
        // three tries are configured, but a stopped request must not restart
        $this->assertSame(1, $ai->attempts);
    }

    function test__without_an_abort_callback_nothing_changes(): void
    {
        $ai = $this->abortAihelper(null);

        $result = $ai->ask('test');

        $this->assertTrue($result['success']);
        $this->assertSame('ok', $result['response']);
        $this->assertFalse($result['aborted']);
    }

    function test__cli_request_limit_returns_newest_requests(): void
    {
        $testDirectory = sys_get_temp_dir() . '/aihelper-cli-limit-' . bin2hex(random_bytes(8));
        $dataDirectory = $testDirectory . '/opencode';
        mkdir($dataDirectory, recursive: true);
        $databasePath = $dataDirectory . '/opencode.db';
        $connection = new \PDO('sqlite:' . $databasePath);
        $connection->exec('CREATE TABLE session (id TEXT PRIMARY KEY, directory TEXT)');
        $connection->exec('CREATE TABLE message (id TEXT PRIMARY KEY, session_id TEXT, time_created INTEGER, data TEXT)');
        $connection->exec('CREATE TABLE part (message_id TEXT, time_created INTEGER, data TEXT)');
        $connection->exec("INSERT INTO session (id, directory) VALUES ('session-1', '/tmp/project')");
        $statement = $connection->prepare(
            'INSERT INTO message (id, session_id, time_created, data) VALUES (:id, :session_id, :time_created, :data)'
        );
        foreach ([1, 2, 3] as $requestNumber) {
            $timeCreated = 2208988800000 + $requestNumber * 1000;
            $statement->execute([
                'id' => 'message-' . $requestNumber,
                'session_id' => 'session-1',
                'time_created' => $timeCreated,
                'data' => json_encode(
                    [
                        'role' => 'assistant',
                        'providerID' => 'opencode-go',
                        'modelID' => 'model-' . $requestNumber,
                        'tokens' => ['input' => $requestNumber, 'output' => $requestNumber],
                        'time' => ['created' => $timeCreated, 'completed' => $timeCreated + 100]
                    ],
                    JSON_THROW_ON_ERROR
                )
            ]);
        }
        $originalDataHome = getenv('XDG_DATA_HOME');
        putenv('XDG_DATA_HOME=' . $testDirectory);

        try {
            $requests = aihelper::getCliApiRequests(
                limit: 2,
                date_from: '2040-01-01 00:00:00',
                date_until: '2040-12-31 23:59:59',
                group_by: false
            );

            $this->assertCount(2, $requests);
            $this->assertSame(['model-3', 'model-2'], array_column($requests, 'model'));

            $groupedRequests = aihelper::getCliApiRequests(
                limit: 2,
                date_from: '2040-01-01 00:00:00',
                date_until: '2040-12-31 23:59:59',
                group_by: true
            );

            $this->assertCount(1, $groupedRequests);
            $this->assertSame(3, $groupedRequests[0]['calls']);
            $this->assertSame(6, $groupedRequests[0]['usage']['input_tokens']);
        } finally {
            if ($originalDataHome === false) {
                putenv('XDG_DATA_HOME');
            }
            if ($originalDataHome !== false) {
                putenv('XDG_DATA_HOME=' . $originalDataHome);
            }
            $statement = null;
            $connection = null;
            unlink($databasePath);
            rmdir($dataDirectory);
            rmdir($testDirectory);
        }
    }

    function test__a_throwing_abort_callback_lets_the_request_continue(): void
    {
        $ai = $this->abortAihelper(function (): bool {
            throw new \RuntimeException('cancel signal unreachable');
        });

        $this->assertFalse($ai->probeAbort());
        $this->assertTrue($ai->ask('test')['success']);
    }

    function test__transient_request_errors_are_retried(): void
    {
        $ai = $this->retryAihelper([
            'AI Request fehlgeschlagen: auth_unavailable: no auth available',
            ['response' => ['error' => ['code' => 'auth_unavailable']], 'success' => false, 'costs' => 0.0]
        ]);

        $result = $ai->ask('test');

        $this->assertTrue($result['success']);
        $this->assertSame('ok', $result['response']);
        $this->assertSame(3, $ai->attempts);
        $this->assertSame([true, false, false], $ai->promptAdditions);
    }

    function test__provider_overload_is_retried(): void
    {
        $ai = $this->retryAihelper([
            'AI Request fehlgeschlagen: Overloaded',
            'claude executor: upstream returned error event: Overloaded'
        ]);

        $result = $ai->ask('test');

        $this->assertTrue($result['success']);
        $this->assertSame('ok', $result['response']);
        $this->assertSame(3, $ai->attempts);
        $this->assertSame([true, false, false], $ai->promptAdditions);
    }

    // php's built-in server needs the router as a file on disk; it only exists
    // for this one test, so it is written out instead of being checked in
    private const MCP_RETRY_ROUTER = <<<'ROUTER'
    <?php
    declare(strict_types=1);

    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
        http_response_code(204);
        return;
    }

    $counterFile = (string) getenv('MCP_RETRY_COUNTER');
    $attempt = is_file($counterFile) ? (int) file_get_contents($counterFile) + 1 : 1;
    file_put_contents($counterFile, (string) $attempt);

    if ($attempt < 3) {
        http_response_code(502);
        echo 'temporary gateway failure';
        return;
    }

    header('Content-Type: text/event-stream');
    echo 'event: message' . "\n";
    echo 'data: ' . json_encode([
        'jsonrpc' => '2.0',
        'id' => 1,
        'result' => [
            'tools' => [
                [
                    'name' => 'test_tool',
                    'description' => 'Test tool',
                    'inputSchema' => ['type' => 'object', 'properties' => []]
                ]
            ]
        ]
    ]) . "\n\n";
    ROUTER;

    function test__transient_mcp_tool_discovery_errors_are_retried(): void
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
        $this->assertNotFalse($socket, $errorMessage);
        $address = stream_socket_get_name($socket, false);
        $this->assertIsString($address);
        fclose($socket);
        $port = (int) mb_substr($address, (int) mb_strrpos($address, ':') + 1);
        $counterFile = tempnam(sys_get_temp_dir(), 'aihelper-mcp-retry-');
        $this->assertNotFalse($counterFile);
        $routerFile = sys_get_temp_dir() . '/aihelper-mcp-retry-router-' . getmypid() . '.php';
        $this->assertNotFalse(file_put_contents($routerFile, self::MCP_RETRY_ROUTER));
        // the null device is named differently on windows, where /dev/null does not exist
        $nullDevice = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
        $process = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:' . $port, $routerFile],
            [
                0 => ['file', $nullDevice, 'r'],
                1 => ['file', $nullDevice, 'a'],
                2 => ['file', $nullDevice, 'a']
            ],
            $pipes,
            __DIR__,
            array_merge(getenv(), ['MCP_RETRY_COUNTER' => $counterFile])
        );
        $this->assertIsResource($process);

        try {
            $ready = false;
            for ($attempt = 0; $attempt < 150; $attempt++) {
                $connection = curl_init('http://127.0.0.1:' . $port);
                curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($connection, CURLOPT_CONNECTTIMEOUT_MS, 100);
                curl_setopt($connection, CURLOPT_TIMEOUT_MS, 100);
                curl_exec($connection);
                $httpCode = curl_getinfo($connection, CURLINFO_HTTP_CODE);
                if ($httpCode === 204) {
                    $ready = true;
                    break;
                }
                usleep(100000);
            }
            $this->assertTrue($ready);

            $tools = $this->retryAihelper([])->localTools('http://127.0.0.1:' . $port);

            $this->assertCount(1, $tools);
            $this->assertSame('test_tool', $tools[0]['name']);
            $this->assertSame('3', trim((string) file_get_contents($counterFile)));
        } finally {
            proc_terminate($process);
            proc_close($process);
            unlink($counterFile);
            unlink($routerFile);
        }
    }

    function test__auth_unavailable_stops_after_three_attempts(): void
    {
        $ai = $this->retryAihelper(array_fill(0, 8, 'AI Request fehlgeschlagen: auth_unavailable: no auth available'));

        $result = $ai->ask('test');

        $this->assertFalse($result['success']);
        $this->assertSame('AI Request fehlgeschlagen: auth_unavailable: no auth available', $result['response']);
        $this->assertSame(3, $ai->attempts);
        $this->assertSame([true, false, false], $ai->promptAdditions);
    }

    function test__availability_backoff_covers_five_minutes(): void
    {
        $method = new \ReflectionMethod(aihelper::class, 'retryBackoffSeconds');
        $ai = $this->retryAihelper([]);
        $backoffs = [];
        for ($attempt = 1; $attempt <= 8; $attempt++) {
            $backoffs[] = $method->invoke($ai, $attempt, true, true);
        }

        $this->assertSame([5, 10, 20, 40, 60, 60, 60, 60], $backoffs);
        $this->assertSame(315, array_sum($backoffs));
    }

    function test__transient_stream_transport_errors_are_retried(): void
    {
        $ai = $this->retryAihelper([
            'AI Request fehlgeschlagen: Post "https://chatgpt.com/backend-api/codex/responses": EOF',
            'AI Request fehlgeschlagen: stream error: stream ID 1; PROTOCOL_ERROR; received from peer'
        ]);

        $result = $ai->ask('test');

        $this->assertTrue($result['success']);
        $this->assertSame('ok', $result['response']);
        $this->assertSame(3, $ai->attempts);
        $this->assertSame([true, false, false], $ai->promptAdditions);
    }

    function test__harness_mcp_startup_errors_are_retried_once(): void
    {
        $error =
            'AI Request fehlgeschlagen: required MCP servers failed to initialize: charly: ' .
            'handshaking with MCP server failed: empty sse stream, when process initialize response';
        $ai = $this->retryAihelper([$error, $error, $error]);
        $ai->is_harness = true;

        $result = $ai->ask('test');

        $this->assertFalse($result['success']);
        $this->assertSame($error, $result['response']);
        $this->assertSame(2, $ai->attempts);
        $this->assertSame([true, false], $ai->promptAdditions);
    }

    function test__harness_mcp_startup_errors_are_not_retried_after_streaming(): void
    {
        $ai = $this->retryAihelper(['mcp_startup_after_text']);
        $ai->is_harness = true;

        try {
            $ai->ask('test');
            $this->fail('Expected the interrupted MCP startup to be surfaced.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('required MCP servers failed to initialize', $exception->getMessage());
        }
        $this->assertSame(1, $ai->attempts);
    }

    function test__local_tool_output_keeps_compact_results_within_budget(): void
    {
        $records = array_fill(0, 1800, [
            'id' => str_repeat('a', 65),
            'from' => '4915112345678',
            'to' => '4915158754691',
            'timestamp' => '2026-07-27T17:00:00+02:00'
        ]);
        $output = json_encode($records, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $method = new \ReflectionMethod(aihelper::class, 'truncateLocalToolOutput');

        $this->assertGreaterThan(256000, strlen($output));
        $this->assertLessThan(288000, strlen($output));
        $this->assertSame($output, $method->invoke($this->retryAihelper([]), $output, 288000));
    }

    function test__local_tool_output_compacts_json_without_losing_records(): void
    {
        $records = [['id' => 'one', 'body' => 'First message'], ['id' => 'two', 'body' => 'Second message']];
        $output = json_encode($records, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        $method = new \ReflectionMethod(aihelper::class, 'truncateLocalToolOutput');
        $compacted = $method->invoke($this->retryAihelper([]), $output, 10000);

        $this->assertLessThan(strlen($output), strlen($compacted));
        $this->assertSame($records, json_decode($compacted, true, 512, JSON_THROW_ON_ERROR));
    }

    function test__local_tool_output_compacts_results_above_budget(): void
    {
        $records = array_fill(0, 1800, [
            'id' => str_repeat('a', 200),
            'from' => '4915112345678',
            'to' => '4915158754691',
            'timestamp' => '2026-07-27T17:00:00+02:00'
        ]);
        $output = json_encode($records, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $method = new \ReflectionMethod(aihelper::class, 'truncateLocalToolOutput');
        $truncated = $method->invoke($this->retryAihelper([]), $output, 100000);

        $this->assertLessThanOrEqual(100000, strlen($truncated));
        $this->assertStringContainsString('1795 more items, 1800 total', $truncated);
        $this->assertStringContainsString('truncated from', $truncated);
        // the temp directory differs per platform, so only the marker is matched
        $persistedPattern = '#complete structured result persisted at ([^;\]]*aihelper-tool-results[^;\]]+\.json)#';
        $this->assertMatchesRegularExpression($persistedPattern, $truncated);
        preg_match($persistedPattern, $truncated, $matches);
        $this->assertFileExists($matches[1]);
        $this->assertSame(
            $records,
            json_decode((string) file_get_contents($matches[1]), true, 512, JSON_THROW_ON_ERROR)
        );
        $retruncated = $method->invoke($this->retryAihelper([]), $truncated, 50000);
        $this->assertLessThanOrEqual(50000, strlen($retruncated));
        $this->assertStringContainsString('complete structured result persisted at ' . $matches[1], $retruncated);
        unlink($matches[1]);
    }

    function test__local_tool_output_includes_marker_within_small_budget(): void
    {
        $method = new \ReflectionMethod(aihelper::class, 'truncateLocalToolOutput');
        $truncated = $method->invoke($this->retryAihelper([]), str_repeat('a', 1000), 100);

        $this->assertSame(100, strlen($truncated));
        $this->assertStringContainsString('truncated from 1000 chars', $truncated);
    }

    function test__mcp_images_are_forwarded_as_multimodal_tool_results(): void
    {
        $imageData = base64_encode('test-image');
        $mcpResponse = [
            'result' => [
                'content' => [
                    ['type' => 'text', 'text' => 'Rendered page 1.'],
                    ['type' => 'image', 'mimeType' => 'image/png', 'data' => $imageData]
                ]
            ]
        ];

        $chatCompletions = $this->toolImageAihelper('cliproxyapi', $mcpResponse);
        $this->assertTrue($chatCompletions->runToolLoop()['success']);
        $chatSession = $chatCompletions->session();
        $this->assertSame('Rendered page 1.', $chatSession[1]['content']);
        $this->assertSame('image_url', $chatSession[2]['content'][1]['type']);
        $this->assertSame('data:image/png;base64,' . $imageData, $chatSession[2]['content'][1]['image_url']['url']);

        $anthropic = $this->toolImageAihelper('anthropic', $mcpResponse);
        $this->assertTrue($anthropic->runToolLoop()['success']);
        $anthropicSession = $anthropic->session();
        $this->assertSame('Rendered page 1.', $anthropicSession[1]['content'][0]['content'][0]['text']);
        $this->assertSame('image', $anthropicSession[1]['content'][0]['content'][1]['type']);
        $this->assertSame($imageData, $anthropicSession[1]['content'][0]['content'][1]['source']['data']);

        $google = $this->toolImageAihelper('google', $mcpResponse);
        $this->assertTrue($google->runToolLoop()['success']);
        $googleSession = $google->session();
        $this->assertSame('Rendered page 1.', $googleSession[1]['parts'][0]['functionResponse']['response']['result']);
        $this->assertSame($imageData, $googleSession[1]['parts'][1]['inlineData']['data']);

        $responses = $this->toolImageAihelper('openai', $mcpResponse);
        $this->assertTrue($responses->runToolLoop()['success']);
        $responsesSession = $responses->session();
        $this->assertSame('Rendered page 1.', $responsesSession[1]['output'][0]['text']);
        $this->assertSame('input_image', $responsesSession[1]['output'][1]['type']);
        $this->assertSame('data:image/png;base64,' . $imageData, $responsesSession[1]['output'][1]['image_url']);
    }

    function test__claude_code_uses_explicit_stream_json_input(): void
    {
        $this->skipOnCi();
        $harness = (new \ReflectionClass(\vielhuber\aihelper\ai_claudecode::class))->newInstanceWithoutConstructor();
        $args = (new \ReflectionMethod(\vielhuber\aihelper\ai_claudecode::class, 'buildArgs'))->invoke($harness);
        $input = (new \ReflectionMethod(\vielhuber\aihelper\ai_claudecode::class, 'harnessInput'))->invoke($harness, 'Hello');

        $this->assertContains('--input-format', $args);
        $this->assertContains('stream-json', $args);
        $this->assertSame(
            [
                'type' => 'user',
                'message' => [
                    'role' => 'user',
                    'content' => [['type' => 'text', 'text' => 'Hello']]
                ]
            ],
            json_decode(trim($input), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    function test__harness_session_selection_is_explicit_when_requested(): void
    {
        $cases = [
            'claudecode' => [
                'legacy' => ['--continue'],
                'fresh_absent' => ['--continue', '--resume'],
                'resume' => ['--resume', 'native-session']
            ],
            'codex' => [
                'legacy' => ['exec', 'resume', '--last'],
                'fresh_absent' => ['resume', '--last'],
                'resume' => ['exec', 'resume', 'native-session']
            ],
            'opencode' => [
                'legacy' => ['--continue'],
                'fresh_absent' => ['--continue', '--session'],
                'resume' => ['--session', 'native-session']
            ]
        ];

        foreach ($cases as $provider => $expectations) {
            $harness = aihelper::create(provider: $provider);
            $args = (new \ReflectionMethod($harness, 'buildArgs'))->invoke($harness);
            foreach ($expectations['legacy'] as $argument) {
                $this->assertContains($argument, $args);
            }

            $harness = aihelper::create(provider: $provider, cli_resume_latest: false);
            $args = (new \ReflectionMethod($harness, 'buildArgs'))->invoke($harness);
            foreach ($expectations['fresh_absent'] as $argument) {
                $this->assertNotContains($argument, $args);
            }

            $harness = aihelper::create(provider: $provider, cli_session_id: 'native-session');
            $args = (new \ReflectionMethod($harness, 'buildArgs'))->invoke($harness);
            $this->assertSame('native-session', $harness->getCliSessionId());
            $positions = [];
            foreach ($expectations['resume'] as $argument) {
                $position = array_search($argument, $args, true);
                $this->assertNotFalse($position);
                $positions[] = $position;
            }
            $this->assertSame($positions, array_values(array_unique($positions)));
        }
    }

    function test__native_harness_memory_can_be_disabled(): void
    {
        $claude = aihelper::create(provider: 'claudecode', cli_native_memory: false);
        $claudeEnvironment = (new \ReflectionMethod($claude, 'harnessEnvironmentOverrides'))->invoke($claude);
        $this->assertSame('1', $claudeEnvironment['CLAUDE_CODE_DISABLE_AUTO_MEMORY']);

        $codex = aihelper::create(provider: 'codex', cli_native_memory: false);
        $codexArguments = (new \ReflectionMethod($codex, 'buildArgs'))->invoke($codex);
        $this->assertContains('memories.generate_memories=false', $codexArguments);
        $this->assertContains('memories.use_memories=false', $codexArguments);

        $codexWithMemory = aihelper::create(provider: 'codex');
        $codexWithMemoryArguments = (new \ReflectionMethod($codexWithMemory, 'buildArgs'))->invoke($codexWithMemory);
        $this->assertNotContains('memories.generate_memories=false', $codexWithMemoryArguments);
        $this->assertNotContains('memories.use_memories=false', $codexWithMemoryArguments);
    }

    function test__harnesses_emit_their_native_session_id(): void
    {
        $cases = [
            'claudecode' => ['type' => 'system', 'subtype' => 'init', 'session_id' => 'claude-session'],
            'codex' => ['type' => 'thread.started', 'thread_id' => 'codex-session'],
            'opencode' => ['type' => 'step_start', 'sessionID' => 'opencode-session']
        ];

        foreach ($cases as $provider => $event) {
            $harness = aihelper::create(provider: $provider);
            (new \ReflectionProperty(aihelper::class, 'stream'))->setValue($harness, true);
            $result = (object) [
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
            ob_start();
            ob_start();
            $emit = (new \ReflectionMethod($harness, 'getStreamCallback'))->invoke($harness);
            (new \ReflectionMethod($harness, 'handleEvent'))->invoke($harness, $event, $result, $emit);
            ob_end_flush();
            $output = (string) ob_get_clean();
            $nativeSessionId = match ($provider) {
                'claudecode' => 'claude-session',
                'codex' => 'codex-session',
                default => 'opencode-session'
            };

            $this->assertSame($nativeSessionId, $harness->getCliSessionId());
            $this->assertStringContainsString('event: harness_session', $output);
            $this->assertStringContainsString('"session_id":"' . $nativeSessionId . '"', $output);
        }
    }

    function test__opencode_uses_json_mode_and_maps_variant(): void
    {
        $this->skipOnCi();
        $harness = (new \ReflectionClass(\vielhuber\aihelper\ai_opencode::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(aihelper::class, 'model'))->setValue($harness, 'opencode-go/glm-5.2');
        (new \ReflectionProperty(aihelper::class, 'effort'))->setValue($harness, 'high');
        $args = (new \ReflectionMethod(\vielhuber\aihelper\ai_opencode::class, 'buildArgs'))->invoke($harness);
        $environment = (new \ReflectionMethod(\vielhuber\aihelper\ai_opencode::class, 'harnessEnvironmentOverrides'))->invoke($harness);

        $this->assertSame(
            ['run', '--continue', '--format', 'json', '--auto', '--model', 'opencode-go/glm-5.2', '--variant', 'high'],
            $args
        );
        $this->assertSame('true', $environment['OPENCODE_DISABLE_CLAUDE_CODE']);
        $this->assertSame('true', $environment['OPENCODE_DISABLE_EXTERNAL_SKILLS']);
    }

    function test__opencode_maps_json_events_to_harness_result(): void
    {
        $this->skipOnCi();
        $harness = (new \ReflectionClass(\vielhuber\aihelper\ai_opencode::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(aihelper::class, 'model'))->setValue($harness, 'opencode-go/glm-5.2');
        $result = (object) [
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
        $handler = new \ReflectionMethod(\vielhuber\aihelper\ai_opencode::class, 'handleEvent');
        $handler->invoke($harness, ['type' => 'step_start', 'sessionID' => 'session-1'], $result, null);
        $handler->invoke($harness, ['type' => 'text', 'part' => ['text' => "  Done\n"]], $result, null);
        $handler->invoke(
            $harness,
            [
                'type' => 'step_finish',
                'part' => [
                    'reason' => 'stop',
                    'tokens' => [
                        'input' => 100,
                        'output' => 20,
                        'cache' => ['read' => 30, 'write' => 10]
                    ],
                    'cost' => 0.25
                ]
            ],
            $result,
            null
        );

        $this->assertSame("  Done\n", $result->result->content[0]->text);
        $this->assertSame('end_turn', $result->result->stop_reason);
        $this->assertSame(100, $result->result->usage->input_tokens);
        $this->assertSame(20, $result->result->usage->output_tokens);
        $this->assertSame(30, $result->result->usage->cache_read_input_tokens);
        $this->assertSame(10, $result->result->usage->cache_creation_input_tokens);
        $this->assertSame(0.25, (new \ReflectionProperty($harness, 'harness_costs'))->getValue($harness));
    }

    function test__opencode_records_tool_calls_in_the_session(): void
    {
        $this->skipOnCi();
        $harness = (new \ReflectionClass(\vielhuber\aihelper\ai_opencode::class))->newInstanceWithoutConstructor();
        $result = (object) ['result' => (object) ['content' => []]];
        $handler = new \ReflectionMethod(\vielhuber\aihelper\ai_opencode::class, 'handleEvent');
        $part = [
            'callID' => 'call_9b12',
            'tool' => 'charly_charly_create_sub_chat',
            'state' => ['status' => 'running', 'input' => ['prompt' => 'test']]
        ];
        $handler->invoke($harness, ['type' => 'tool', 'part' => $part], $result, null);
        $this->assertCount(0, $result->result->content);

        $part['state'] = ['status' => 'pending', 'input' => ['prompt' => 'test']];
        $handler->invoke($harness, ['type' => 'tool', 'part' => $part], $result, null);
        $this->assertCount(0, $result->result->content);

        $part['state'] = ['status' => 'completed', 'input' => ['prompt' => 'test'], 'output' => '{"count":1}'];
        $handler->invoke($harness, ['type' => 'tool', 'part' => $part], $result, null);

        $this->assertCount(2, $result->result->content);
        $this->assertSame('tool_use', $result->result->content[0]->type);
        $this->assertSame('call_9b12', $result->result->content[0]->id);
        $this->assertSame('charly_charly_create_sub_chat', $result->result->content[0]->name);
        $this->assertSame(['prompt' => 'test'], $result->result->content[0]->input);
        $this->assertSame('tool_result', $result->result->content[1]->type);
        $this->assertSame('call_9b12', $result->result->content[1]->tool_use_id);
        $this->assertSame('{"count":1}', $result->result->content[1]->content);
        $this->assertFalse($result->result->content[1]->is_error);
    }

    function test__claude_code_records_tool_calls_in_the_session(): void
    {
        $this->skipOnCi();
        $harness = (new \ReflectionClass(\vielhuber\aihelper\ai_claudecode::class))->newInstanceWithoutConstructor();
        $result = (object) ['result' => (object) ['content' => []]];
        $handler = new \ReflectionMethod(\vielhuber\aihelper\ai_claudecode::class, 'handleEvent');
        $handler->invoke(
            $harness,
            [
                'type' => 'assistant',
                'message' => [
                    'content' => [
                        ['type' => 'text', 'text' => 'Ich lege den Sub-Chat an.'],
                        ['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'charly_create_sub_chat', 'input' => ['prompt' => 'test']]
                    ]
                ]
            ],
            $result,
            null
        );
        $handler->invoke(
            $harness,
            [
                'type' => 'user',
                'message' => [
                    'content' => [
                        ['type' => 'text', 'text' => 'wird nicht uebernommen'],
                        ['type' => 'tool_result', 'tool_use_id' => 'toolu_1', 'content' => 'ok']
                    ]
                ]
            ],
            $result,
            null
        );

        $this->assertCount(3, $result->result->content);
        $this->assertSame('Ich lege den Sub-Chat an.', $result->result->content[0]->text);
        $this->assertSame('charly_create_sub_chat', $result->result->content[1]->name);
        $this->assertSame('tool_result', $result->result->content[2]->type);
        $this->assertSame('toolu_1', $result->result->content[2]->tool_use_id);
    }

    function test__codex_records_tool_calls_in_the_session(): void
    {
        $this->skipOnCi();
        class_exists(aihelper::class);
        $harness = (new \ReflectionClass(\vielhuber\aihelper\ai_codex::class))->newInstanceWithoutConstructor();
        $result = (object) ['result' => (object) ['content' => []]];
        $handler = new \ReflectionMethod(\vielhuber\aihelper\ai_codex::class, 'handleEvent');
        $handler->invoke(
            $harness,
            [
                'type' => 'item.completed',
                'item' => [
                    'id' => 'skill_1',
                    'type' => 'command_execution',
                    'command' =>
                        "/bin/bash -lc 'cat /tmp/aihelper-payload/test/codex/skills/filesystem/SKILL.md'",
                    'aggregated_output' => "---\nname: filesystem\n---\n",
                    'exit_code' => 0,
                    'status' => 'completed'
                ]
            ],
            $result,
            null
        );
        $handler->invoke(
            $harness,
            [
                'type' => 'item.completed',
                'item' => [
                    'id' => 'item_1',
                    'type' => 'mcp_tool_call',
                    'server' => 'charly',
                    'tool' => 'charly_create_sub_chat',
                    'arguments' => ['message' => 'test'],
                    'result' => ['content' => [['type' => 'text', 'text' => 'PONG-4711']], 'structured_content' => null],
                    'error' => null,
                    'status' => 'completed'
                ]
            ],
            $result,
            null
        );
        $handler->invoke(
            $harness,
            [
                'type' => 'item.completed',
                'item' => [
                    'id' => 'item_2',
                    'type' => 'command_execution',
                    'command' => "/bin/bash -lc 'echo HALLO123'",
                    'aggregated_output' => "HALLO123\n",
                    'exit_code' => 0,
                    'status' => 'completed'
                ]
            ],
            $result,
            null
        );

        $this->assertCount(6, $result->result->content);
        $this->assertSame('shell', $result->result->content[0]->name);
        $this->assertStringContainsString('SKILL.md', $result->result->content[0]->input['command']);
        $this->assertStringContainsString('name: filesystem', $result->result->content[1]->content);
        $this->assertSame('charly__charly_create_sub_chat', $result->result->content[2]->name);
        $this->assertSame(['message' => 'test'], $result->result->content[2]->input);
        $this->assertSame('PONG-4711', $result->result->content[3]->content);
        $this->assertFalse($result->result->content[3]->is_error);
        $this->assertSame('shell', $result->result->content[4]->name);
        $this->assertSame("HALLO123\n", $result->result->content[5]->content);
    }

    function test__codex_maps_native_goal_continuations(): void
    {
        class_exists(aihelper::class);
        $harness = (new \ReflectionClass(\vielhuber\aihelper\ai_codex::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(aihelper::class, 'stream'))->setValue($harness, true);
        $result = (object) [
            'result' => (object) [
                'content' => [],
                'stop_reason' => null,
                'usage' => (object) []
            ]
        ];
        $handler = new \ReflectionMethod(\vielhuber\aihelper\ai_codex::class, 'handleNativeEvent');
        $primaryHandler = new \ReflectionMethod(\vielhuber\aihelper\ai_codex::class, 'handleEvent');

        ob_start();
        ob_start();
        $primaryHandler->invoke(
            $harness,
            [
                'type' => 'turn.completed',
                'usage' => [
                    'input_tokens' => 10,
                    'cache_write_input_tokens' => 0,
                    'cached_input_tokens' => 0,
                    'output_tokens' => 5
                ]
            ],
            $result,
            null
        );
        $handler->invoke(
            $harness,
            ['type' => 'event_msg', 'payload' => ['type' => 'task_started', 'turn_id' => 'goal-turn']],
            $result,
            null
        );
        $handler->invoke(
            $harness,
            [
                'type' => 'event_msg',
                'payload' => [
                    'type' => 'mcp_tool_call_end',
                    'call_id' => 'native-tool',
                    'invocation' => [
                        'server' => 'filesystem',
                        'tool' => 'read_file',
                        'arguments' => ['path' => '/tmp/example.txt']
                    ],
                    'result' => ['Ok' => ['content' => [['type' => 'text', 'text' => 'contents']], 'isError' => false]]
                ]
            ],
            $result,
            null
        );
        $agentEvent = [
            'type' => 'event_msg',
            'timestamp' => '2026-08-18T12:00:00.000Z',
            'payload' => ['type' => 'agent_message', 'message' => 'Goal continuation result']
        ];
        $handler->invoke($harness, $agentEvent, $result, null);
        $agentEvent['timestamp'] = '2026-08-18T12:00:01.000Z';
        $handler->invoke($harness, $agentEvent, $result, null);
        $handler->invoke(
            $harness,
            [
                'type' => 'response_item',
                'payload' => [
                    'type' => 'custom_tool_call',
                    'call_id' => 'native-action',
                    'name' => 'exec',
                    'input' => 'run command'
                ]
            ],
            $result,
            null
        );
        $handler->invoke(
            $harness,
            [
                'type' => 'response_item',
                'payload' => [
                    'type' => 'custom_tool_call_output',
                    'call_id' => 'native-action',
                    'output' => [['type' => 'input_text', 'text' => 'command output']]
                ]
            ],
            $result,
            null
        );
        $handler->invoke(
            $harness,
            ['type' => 'event_msg', 'payload' => ['type' => 'task_complete', 'turn_id' => 'goal-turn']],
            $result,
            null
        );
        ob_end_flush();
        $output = (string) ob_get_clean();

        $this->assertStringContainsString('Goal continuation started', $output);
        $this->assertStringContainsString('Read /tmp/example.txt', $output);
        $this->assertCount(6, $result->result->content);
        $this->assertSame('filesystem__read_file', $result->result->content[0]->name);
        $this->assertStringContainsString('contents', $result->result->content[1]->content);
        $this->assertSame('Goal continuation result', $result->result->content[2]->text);
        $this->assertSame('Goal continuation result', $result->result->content[3]->text);
        $this->assertSame('codex__exec', $result->result->content[4]->name);
        $this->assertSame(['input' => 'run command'], $result->result->content[4]->input);
        $this->assertStringContainsString('command output', $result->result->content[5]->content);
        $this->assertSame('end_turn', $result->result->stop_reason);
    }

    function test__tool_activity_is_streamed_as_a_reasoning_transcript(): void
    {
        $mcpResponse = [
            'result' => [
                'isError' => false,
                'content' => [['type' => 'text', 'text' => 'ok']]
            ]
        ];
        $provider = $this->toolImageAihelper('openai', $mcpResponse);
        (new \ReflectionProperty(aihelper::class, 'stream'))->setValue($provider, true);
        ob_start();
        ob_start();
        $provider->runToolLoop();
        ob_end_flush();
        $providerOutput = (string) ob_get_clean();
        $this->assertStringContainsString('event: reasoning', $providerOutput);
        $this->assertStringContainsString('"kind":"transcript"', $providerOutput);
        $this->assertStringContainsString('Used Render image', $providerOutput);
        $this->assertStringContainsString('ok', $providerOutput);

        $transcript = new \ReflectionMethod(aihelper::class, 'emitTranscript');
        ob_start();
        ob_start();
        $transcript->invoke(
            $provider,
            null,
            'Used protected tool',
            'completed',
            [
                'api_key' => 'secret-value',
                'output' => 'AIHELPER_MCP_TOKEN_TEST=token-value',
                'image' => ['type' => 'image', 'data' => str_repeat('A', 1000)]
            ]
        );
        ob_end_flush();
        $protectedOutput = (string) ob_get_clean();
        $this->assertStringNotContainsString('secret-value', $protectedOutput);
        $this->assertStringNotContainsString('token-value', $protectedOutput);
        $this->assertStringNotContainsString(str_repeat('A', 100), $protectedOutput);
        $this->assertStringContainsString('[binary data omitted]', $protectedOutput);

        $result = (object) ['result' => (object) ['content' => []]];
        $codex = aihelper::create(provider: 'codex');
        (new \ReflectionProperty(aihelper::class, 'stream'))->setValue($codex, true);
        $codexHandler = new \ReflectionMethod(\vielhuber\aihelper\ai_codex::class, 'handleEvent');
        $codexEvent = [
            'item' => [
                'id' => 'codex-tool-1',
                'type' => 'mcp_tool_call',
                'server' => 'trello',
                'tool' => 'get_card',
                'arguments' => [],
                'status' => 'completed',
                'result' => [
                    'content' => [
                        ['type' => 'text', 'text' => 'ok'],
                        ['type' => 'image', 'data' => str_repeat('A', 1000), 'mimeType' => 'image/png']
                    ],
                    'structuredContent' => ['ticket' => ['id' => '4711']]
                ]
            ]
        ];
        ob_start();
        ob_start();
        $codexHandler->invoke(
            $codex,
            ['type' => 'turn.started', 'turn_id' => 'turn-1'],
            $result,
            null
        );
        $codexHandler->invoke($codex, ['type' => 'item.started'] + $codexEvent, $result, null);
        $codexHandler->invoke($codex, ['type' => 'item.completed'] + $codexEvent, $result, null);
        $codexCommandEvent = [
            'item' => [
                'id' => 'codex-command-1',
                'type' => 'command_execution',
                'command' => 'false',
                'aggregated_output' => '',
                'exit_code' => 1,
                'status' => 'completed'
            ]
        ];
        $codexHandler->invoke($codex, ['type' => 'item.started'] + $codexCommandEvent, $result, null);
        $codexHandler->invoke($codex, ['type' => 'item.completed'] + $codexCommandEvent, $result, null);
        foreach (
            [
                [
                    'id' => 'codex-files-1',
                    'type' => 'file_change',
                    'changes' => [['path' => '/tmp/example.php', 'kind' => 'update']],
                    'status' => 'completed'
                ],
                [
                    'id' => 'codex-search-1',
                    'type' => 'web_search',
                    'query' => 'current documentation',
                    'status' => 'completed'
                ],
                [
                    'id' => 'codex-plan-1',
                    'type' => 'todo_list',
                    'items' => [['text' => 'Run tests', 'completed' => false]],
                    'status' => 'completed'
                ]
            ] as $codexItem
        ) {
            $codexHandler->invoke(
                $codex,
                ['type' => 'item.completed', 'item' => $codexItem],
                $result,
                null
            );
        }
        $codexHandler->invoke(
            $codex,
            ['type' => 'item.started', 'item' => ['id' => 'codex-skill-1', 'type' => 'command_execution']],
            $result,
            null
        );
        $codexHandler->invoke(
            $codex,
            [
                'type' => 'item.completed',
                'item' => [
                    'id' => 'codex-skill-1',
                    'type' => 'command_execution',
                    'command' => 'cat /tmp/aihelper-payload/test/codex/skills/filesystem/SKILL.md',
                    'aggregated_output' => 'internal',
                    'exit_code' => 0,
                    'status' => 'completed'
                ]
            ],
            $result,
            null
        );
        ob_end_flush();
        $codexOutput = (string) ob_get_clean();
        $this->assertStringContainsString('Used Trello · get card', $codexOutput);
        $this->assertStringContainsString('Ran false', $codexOutput);
        $this->assertStringContainsString('Failed.', $codexOutput);
        $this->assertStringContainsString('Changed /tmp/example.php', $codexOutput);
        $this->assertStringContainsString('Searched current documentation', $codexOutput);
        $this->assertStringContainsString('Updated plan', $codexOutput);
        $this->assertStringContainsString('Loaded skill filesystem', $codexOutput);
        $this->assertStringContainsString('Turn started', $codexOutput);
        $this->assertStringContainsString('turn-1', $codexOutput);
        $this->assertStringNotContainsString('internal', $codexOutput);
        $codexMcpResult = array_values(
            array_filter(
                $result->result->content,
                fn(object $entry): bool => $entry->type === 'tool_result' &&
                    ($entry->tool_use_id ?? null) === 'codex-tool-1'
            )
        )[0];
        $decodedCodexMcpResult = json_decode($codexMcpResult->content, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('4711', $decodedCodexMcpResult['structuredContent']['ticket']['id']);
        $this->assertSame(str_repeat('A', 1000), $decodedCodexMcpResult['content'][1]['data']);
        $this->assertStringNotContainsString(str_repeat('A', 100), $codexOutput);
        $this->assertSame(
            ['trello__get_card', 'shell', 'file_change', 'web_search', 'todo_list', 'shell'],
            array_values(
                array_map(
                    fn(object $entry): string => (string) $entry->name,
                    array_filter($result->result->content, fn(object $entry): bool => $entry->type === 'tool_use')
                )
            )
        );

        $claude = aihelper::create(provider: 'claudecode');
        (new \ReflectionProperty(aihelper::class, 'stream'))->setValue($claude, true);
        $claudeHandler = new \ReflectionMethod(\vielhuber\aihelper\ai_claudecode::class, 'handleEvent');
        ob_start();
        ob_start();
        $claudeHandler->invoke(
            $claude,
            [
                'type' => 'system',
                'subtype' => 'hook_started',
                'hook_name' => 'SessionStart:startup',
                'session_id' => 'session-1'
            ],
            $result,
            null
        );
        $claudeHandler->invoke(
            $claude,
            [
                'type' => 'system',
                'subtype' => 'status',
                'status' => 'requesting',
                'session_id' => 'session-1'
            ],
            $result,
            null
        );
        $claudeHandler->invoke(
            $claude,
            [
                'type' => 'system',
                'subtype' => 'init',
                'session_id' => 'session-1',
                'mcp_servers' => [['name' => 'filesystem', 'status' => 'connected']]
            ],
            $result,
            null
        );
        $claudeHandler->invoke(
            $claude,
            [
                'type' => 'assistant',
                'message' => [
                    'content' => [
                        [
                            'type' => 'tool_use',
                            'id' => 'claude-tool-1',
                            'name' => 'mcp__trello__get_card',
                            'input' => []
                        ]
                    ]
                ]
            ],
            $result,
            null
        );
        $claudeHandler->invoke(
            $claude,
            [
                'type' => 'user',
                'message' => [
                    'content' => [
                        ['type' => 'tool_result', 'tool_use_id' => 'claude-tool-1', 'content' => 'ok']
                    ]
                ]
            ],
            $result,
            null
        );
        $claudeHandler->invoke(
            $claude,
            [
                'type' => 'result',
                'subtype' => 'success',
                'is_error' => false,
                'stop_reason' => 'end_turn',
                'result' => 'OK',
                'usage' => [
                    'input_tokens' => 1,
                    'cache_creation_input_tokens' => 2,
                    'cache_read_input_tokens' => 3,
                    'output_tokens' => 4
                ],
                'total_cost_usd' => 0.01
            ],
            $result,
            null
        );
        ob_end_flush();
        $claudeOutput = (string) ob_get_clean();
        $this->assertStringContainsString('Used Trello · get card', $claudeOutput);
        $this->assertStringContainsString('ok', $claudeOutput);
        $this->assertStringContainsString('System init · Mcp servers', $claudeOutput);
        $this->assertStringContainsString('connected', $claudeOutput);
        $this->assertStringContainsString('System hook started', $claudeOutput);
        $this->assertStringContainsString('System status', $claudeOutput);
        $this->assertStringContainsString('requesting', $claudeOutput);
        $this->assertStringContainsString('├', $claudeOutput);
        $this->assertStringContainsString('Result success', $claudeOutput);
        $this->assertStringContainsString('"captures_content":false', $claudeOutput);

        $opencode = aihelper::create(provider: 'opencode');
        (new \ReflectionProperty(aihelper::class, 'stream'))->setValue($opencode, true);
        $opencodeHandler = new \ReflectionMethod(\vielhuber\aihelper\ai_opencode::class, 'handleEvent');
        $opencodePart = [
            'callID' => 'opencode-tool-1',
            'tool' => 'trello_get_card',
            'state' => ['status' => 'running', 'input' => []]
        ];
        ob_start();
        ob_start();
        $opencodeHandler->invoke(
            $opencode,
            [
                'type' => 'step_start',
                'sessionID' => 'session-1'
            ],
            $result,
            null
        );
        $opencodeHandler->invoke($opencode, ['type' => 'tool', 'part' => $opencodePart], $result, null);
        $opencodePart['state'] = [
            'status' => 'error',
            'input' => [],
            'output' => ['message' => 'failed', 'code' => 4711]
        ];
        $opencodeHandler->invoke($opencode, ['type' => 'tool', 'part' => $opencodePart], $result, null);
        $opencodeHandler->invoke(
            $opencode,
            [
                'type' => 'tool',
                'part' => [
                    'callID' => 'opencode-skill-1',
                    'tool' => 'bash',
                    'state' => ['status' => 'pending']
                ]
            ],
            $result,
            null
        );
        $opencodeHandler->invoke(
            $opencode,
            [
                'type' => 'tool',
                'part' => [
                    'callID' => 'opencode-skill-1',
                    'tool' => 'bash',
                    'state' => [
                        'status' => 'completed',
                        'input' => [
                            'command' => 'cat /tmp/aihelper-payload/test/opencode/skills/filesystem/SKILL.md'
                        ],
                        'output' => 'internal'
                    ]
                ]
            ],
            $result,
            null
        );
        ob_end_flush();
        $opencodeOutput = (string) ob_get_clean();
        $this->assertStringContainsString('Used Trello get card', $opencodeOutput);
        $this->assertStringContainsString('failed', $opencodeOutput);
        $this->assertStringContainsString('Loaded skill filesystem', $opencodeOutput);
        $this->assertStringContainsString('Step start', $opencodeOutput);
        $this->assertStringContainsString('session-1', $opencodeOutput);
        $this->assertStringNotContainsString('internal', $opencodeOutput);
        $opencodeResult = array_values(
            array_filter(
                $result->result->content,
                fn(object $entry): bool => $entry->type === 'tool_result' &&
                    ($entry->tool_use_id ?? null) === 'opencode-tool-1'
            )
        )[0];
        $this->assertSame(
            ['message' => 'failed', 'code' => 4711],
            json_decode($opencodeResult->content, true, 512, JSON_THROW_ON_ERROR)
        );
    }

    function test__large_payloads_are_passed_as_files_not_arguments(): void
    {
        $this->skipOnCi();
        class_exists(aihelper::class);
        // linux refuses an exec whose single argument exceeds MAX_ARG_STRLEN
        $prompt = str_repeat("Skill-Zeile mit Umlaut und Emoji: berücksichtigt 🤖\n", 5000);
        $this->assertGreaterThan(128 * 1024, strlen($prompt));

        $claude = (new \ReflectionClass(\vielhuber\aihelper\ai_claudecode::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(aihelper::class, 'session_id'))->setValue($claude, 'payload-args-test');
        (new \ReflectionProperty(aihelper::class, 'system_prompt'))->setValue($claude, $prompt);
        $args = (new \ReflectionMethod(\vielhuber\aihelper\ai_claudecode::class, 'buildArgs'))->invoke($claude);

        $this->assertContains('--append-system-prompt-file', $args);
        $this->assertNotContains($prompt, $args);
        $path = $args[array_search('--append-system-prompt-file', $args, true) + 1];
        $this->assertSame($prompt, file_get_contents($path));
        foreach ($args as $argument) {
            $this->assertLessThan(128 * 1024, strlen((string) $argument));
        }

        $previousHome = getenv('HOME');
        $testHome = sys_get_temp_dir() . '/aihelper-test-home-' . uniqid();
        putenv('HOME=' . $testHome);
        try {
            $codex = (new \ReflectionClass(\vielhuber\aihelper\ai_codex::class))->newInstanceWithoutConstructor();
            (new \ReflectionProperty(aihelper::class, 'session_id'))->setValue($codex, 'payload-args-test');
            (new \ReflectionProperty(aihelper::class, 'system_prompt'))->setValue($codex, $prompt);
            $environment = (new \ReflectionMethod(\vielhuber\aihelper\ai_codex::class, 'harnessEnvironmentOverrides'))->invoke(
                $codex
            );
            $codexArgs = (new \ReflectionMethod(\vielhuber\aihelper\ai_codex::class, 'buildArgs'))->invoke($codex);

            $this->assertArrayHasKey('CODEX_HOME', $environment);
            $this->assertStringContainsString('/.codex/charly/payload-args-test', $environment['CODEX_HOME']);
            $this->assertSame(0700, fileperms($environment['CODEX_HOME']) & 0777);
            $this->assertTrue(is_link($environment['CODEX_HOME'] . '/config.toml'));
            $this->assertFalse(is_link($environment['CODEX_HOME'] . '/skills'));
            $config = (string) file_get_contents($environment['CODEX_HOME'] . '/config.toml');
            $this->assertStringContainsString('🤖', $config);
            $this->assertStringNotContainsString('\\ud83e', $config);
            $this->assertStringContainsString(
                'sqlite_home = ' . json_encode(
                    $environment['CODEX_HOME'],
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES
                ),
                $config
            );
            $this->assertSame(
                $prompt,
                json_decode(
                    trim(substr(strtok($config, "\n"), strlen('instructions = '))),
                    false,
                    512,
                    JSON_THROW_ON_ERROR
                )
            );
            $this->assertNotContains('--ignore-user-config', $codexArgs);
            foreach ($codexArgs as $argument) {
                $this->assertLessThan(128 * 1024, strlen((string) $argument));
            }
        } finally {
            putenv($previousHome === false ? 'HOME' : 'HOME=' . $previousHome);
            __::rrmdir($testHome);
        }

        unlink($path);
    }

    function test__codex_allows_remote_mcp_startup_to_complete(): void
    {
        $codex = aihelper::create(provider: 'codex');
        (new \ReflectionProperty(aihelper::class, 'mcp_servers'))->setValue($codex, [
            [
                'id' => 'github',
                'url' => 'https://example.test/api/github/mcp/',
                'authorization_token' => 'secret',
                'required' => true
            ]
        ]);
        $args = (new \ReflectionMethod(\vielhuber\aihelper\ai_codex::class, 'buildArgs'))->invoke($codex);

        $this->assertContains('mcp_servers.github.startup_timeout_sec=300', $args);
        $this->assertContains('mcp_servers.github.required=true', $args);
        $this->assertSame('shell_snapshot', $args[array_search('--disable', $args, true) + 1]);
    }

    /**
     * Keep model metadata aligned with each harness CLI's accepted effort values.
     */
    function test__harness_models_define_supported_efforts(): void
    {
        $expectedEfforts = [
            'codex' => ['minimal', 'low', 'medium', 'high', 'xhigh'],
            'claudecode' => ['low', 'medium', 'high', 'xhigh', 'max'],
            'opencode' => ['minimal', 'low', 'medium', 'high', 'max']
        ];

        foreach ($expectedEfforts as $provider => $efforts) {
            $harness = aihelper::create(provider: $provider);
            foreach ($harness->models as $model) {
                $this->assertTrue($model['supports_effort'] ?? false, (string) ($model['name'] ?? 'unknown'));
                $this->assertSame($efforts, $model['efforts'] ?? [], (string) ($model['name'] ?? 'unknown'));
            }
        }
    }

    function test__harness_logs_redact_mcp_tokens(): void
    {
        $log = tempnam(sys_get_temp_dir(), 'aihelper-log-');
        $this->assertIsString($log);
        $codex = aihelper::create(provider: 'codex', log: $log);
        (new \ReflectionProperty(aihelper::class, 'mcp_servers'))->setValue($codex, [
            [
                'id' => 'github',
                'url' => 'https://example.test/api/github/mcp/',
                'authorization_token' => 'mcp-secret-value'
            ]
        ]);

        $codex->log("AIHELPER_MCP_TOKEN_GITHUB='mcp-secret-value'", 'test');
        $contents = (string) file_get_contents($log);

        $this->assertStringNotContainsString('mcp-secret-value', $contents);
        $this->assertStringContainsString('AIHELPER_MCP_TOKEN_GITHUB=***', $contents);
        unlink($log);
    }

    /**
     * Keep binary payloads out of logs while retaining diagnostic metadata.
     */
    function test__logs_replace_embedded_binary_data_with_metadata(): void
    {
        $log = tempnam(sys_get_temp_dir(), 'aihelper-log-');
        $this->assertIsString($log);
        $codex = aihelper::create(provider: 'codex', log: $log);
        $codex->log('data:image/png;base64,' . base64_encode('binary-image'), 'test');
        $contents = (string) file_get_contents($log);

        $this->assertStringNotContainsString(base64_encode('binary-image'), $contents);
        $this->assertStringContainsString(
            '[binary data omitted: mime=image/png bytes=12 sha256=' . hash('sha256', 'binary-image') . ']',
            $contents
        );

        $codex->log([
            'content' => json_encode([
                'content' => [[
                    'type' => 'image',
                    'data' => base64_encode('mcp-image'),
                    'mimeType' => 'image/png'
                ]]
            ], JSON_THROW_ON_ERROR)
        ], 'test');
        $contents = (string) file_get_contents($log);

        $this->assertStringNotContainsString(base64_encode('mcp-image'), $contents);
        $this->assertStringContainsString(
            '[binary data omitted: mime=image/png bytes=9 sha256=' . hash('sha256', 'mcp-image') . ']',
            $contents
        );

        $codex->log([
            'content' => [[
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => 'image/png',
                    'data' => base64_encode('anthropic-image')
                ]
            ]]
        ], 'test');
        $contents = (string) file_get_contents($log);

        $this->assertStringNotContainsString(base64_encode('anthropic-image'), $contents);
        $this->assertStringContainsString(
            '[binary data omitted: mime=image/png bytes=15 sha256=' . hash('sha256', 'anthropic-image') . ']',
            $contents
        );
        unlink($log);
    }

    function test__harnesses_allow_slow_remote_mcp_initialization(): void
    {
        foreach (['claudecode', 'opencode'] as $provider) {
            $harness = aihelper::create(provider: $provider);
            (new \ReflectionProperty(aihelper::class, 'mcp_servers'))->setValue($harness, [
                [
                    'id' => 'spotify',
                    'url' => 'https://example.test/api/spotify/mcp/',
                    'authorization_token' => 'secret'
                ]
            ]);
            $environment = (new \ReflectionMethod($harness, 'harnessEnvironmentOverrides'))->invoke($harness);
            if ($provider === 'claudecode') {
                $this->assertSame('300000', $environment['MCP_TIMEOUT']);
            }
            $args = (new \ReflectionMethod($harness, 'buildArgs'))->invoke($harness);
            $configPath = $provider === 'claudecode'
                ? $args[array_search('--mcp-config', $args, true) + 1]
                : $environment['OPENCODE_CONFIG'];
            $config = json_decode((string) file_get_contents($configPath), true, 512, JSON_THROW_ON_ERROR);
            $server = $provider === 'claudecode'
                ? $config['mcpServers']['spotify']
                : $config['mcp']['spotify'];

            $this->assertSame(300000, $server['timeout']);
        }
    }

    function test__opencode_exposes_only_its_harness_models(): void
    {
        $this->skipOnCi();
        $harness = aihelper::create(provider: 'opencode');

        $this->assertNotNull($harness);
        $this->assertNotEmpty($harness->models);
        foreach ($harness->models as $model) {
            $this->assertStringStartsWith('opencode-go/', $model['name']);
        }
        $this->assertSame(
            1,
            count(array_filter($harness->models, fn(array $model): bool => ($model['default'] ?? false) === true))
        );
    }

    function test__harness_waits_for_process_group_leader(): void
    {
        $source = file_get_contents(__DIR__ . '/../src/aihelper.php');

        $this->assertIsString($source);
        $source = str_replace("\r\n", "\n", $source);
        $this->assertStringContainsString("['setsid', '--wait', \$binary]", $source);
        $this->assertStringContainsString("'setsid --wait bash -c '", $source);
        $this->assertStringContainsString("' && ' .\n                        \$this->remoteShell(\$script)", $source);
        $this->assertStringNotContainsString("' && exec ' .\n                        \$this->remoteShell(\$script)", $source);
        $this->assertStringContainsString('/tmp/aihelper-runs/', $source);
        $this->assertStringContainsString('"/proc/$pid/environ"', $source);
        $this->assertStringNotContainsString("'pkill -' . \$signal", $source);
    }

    function test__remote_harness_reuses_preflight_connection(): void
    {
        $this->skipOnCi();
        $harness = aihelper::create(provider: 'claudecode');
        $this->assertNotNull($harness);
        foreach (
            [
                'ssh_host' => 'host.docker.internal',
                'ssh_user' => 'root',
                'ssh_port' => 22,
                'ssh_key' => '/tmp/harness-key',
                'session_id' => 'chat-a'
            ]
            as $property => $value
        ) {
            (new \ReflectionProperty($harness, $property))->setValue($harness, $value);
        }
        $command = (new \ReflectionMethod($harness, 'sshCommand'))->invoke($harness);

        $this->assertContains('ControlMaster=auto', $command);
        $this->assertContains('ControlPersist=60', $command);
        $controlPath = current(
            array_filter($command, fn(string $argument): bool => str_starts_with($argument, 'ControlPath='))
        );
        $this->assertIsString($controlPath);
        $this->assertMatchesRegularExpression('#^ControlPath=/tmp/aihelper-ssh-[a-f0-9]{32}$#', $controlPath);
        $reusedCommand = (new \ReflectionMethod($harness, 'sshCommand'))->invoke($harness);
        $reusedControlPath = current(
            array_filter($reusedCommand, fn(string $argument): bool => str_starts_with($argument, 'ControlPath='))
        );
        $this->assertSame($controlPath, $reusedControlPath);

        (new \ReflectionProperty($harness, 'session_id'))->setValue($harness, 'chat-b');
        $otherCommand = (new \ReflectionMethod($harness, 'sshCommand'))->invoke($harness);
        $otherControlPath = current(
            array_filter($otherCommand, fn(string $argument): bool => str_starts_with($argument, 'ControlPath='))
        );
        $this->assertIsString($otherControlPath);
        $this->assertNotSame($controlPath, $otherControlPath);
    }

    function test__google_stream_preserves_plain_json_errors(): void
    {
        $ai = (new \ReflectionClass(\vielhuber\aihelper\ai_google::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(aihelper::class, 'stream'))->setValue($ai, true);
        $callback = (new \ReflectionMethod(aihelper::class, 'getStreamCallback'))->invoke($ai);

        $callback(json_encode(['error' => ['message' => 'API key not valid.']], JSON_THROW_ON_ERROR) . "\n");

        $response = (new \ReflectionProperty(aihelper::class, 'stream_response'))->getValue($ai);
        $this->assertSame('API key not valid.', $response->result->error->message);
    }

    function test__empty_stream_before_first_payload_is_retried(): void
    {
        $ai = $this->retryAihelper([
            'AI Request fehlgeschlagen: empty_stream: upstream stream closed before first payload'
        ]);

        $result = $ai->ask('test');

        $this->assertTrue($result['success']);
        $this->assertSame('ok', $result['response']);
        $this->assertSame(2, $ai->attempts);
        $this->assertSame([true, false], $ai->promptAdditions);
    }

    function test__empty_success_response_is_retried(): void
    {
        $ai = $this->retryAihelper([['response' => '', 'success' => true, 'costs' => 0.0]]);

        $result = $ai->ask('test');

        $this->assertTrue($result['success']);
        $this->assertSame('ok', $result['response']);
        $this->assertSame(2, $ai->attempts);
        $this->assertSame([true, false], $ai->promptAdditions);
    }

    function test__cli_authentication_expiry_formats_are_recognized(): void
    {
        $ai = $this->retryAihelper([]);

        $this->assertTrue($ai->authenticationIsExpired(['expired' => date(DATE_ATOM, time() - 60)]));
        $this->assertFalse($ai->authenticationIsExpired(['expired' => date(DATE_ATOM, time() + 60)]));
        $this->assertTrue($ai->authenticationIsExpired(['claudeAiOauth' => ['expiresAt' => (time() - 60) * 1000]]));
        $this->assertFalse($ai->authenticationIsExpired(['claudeAiOauth' => ['expiresAt' => (time() + 60) * 1000]]));
        $this->assertFalse($ai->authenticationIsExpired(['access_token' => 'token']));
    }

    function test__cli_usage_caches_are_isolated_by_authentication_source(): void
    {
        $ai = $this->retryAihelper([]);

        $this->assertSame('claude-cliproxyapi', $ai->cliUsageCacheKey('cliproxyapi', 'claude'));
        $this->assertSame('claude-native', $ai->cliUsageCacheKey('claudecode', 'claude'));
        $this->assertSame('codex-cliproxyapi', $ai->cliUsageCacheKey('cliproxyapi', 'codex'));
        $this->assertSame('codex-native', $ai->cliUsageCacheKey('codex', 'codex'));
    }

    function test__transient_dns_errors_are_retried(): void
    {
        $ai = $this->retryAihelper([
            'AI Request fehlgeschlagen: dial tcp: lookup chatgpt.com on 127.0.0.11:53: read udp 127.0.0.1:37313->127.0.0.11:53: i/o timeout',
            'AI Request fehlgeschlagen: dial tcp: lookup chatgpt.com on 127.0.0.11:53: server misbehaving'
        ]);

        $result = $ai->ask('test');

        $this->assertTrue($result['success']);
        $this->assertSame('ok', $result['response']);
        $this->assertSame(3, $ai->attempts);
        $this->assertSame([true, false, false], $ai->promptAdditions);
    }

    function test__transient_connection_errors_are_retried(): void
    {
        $ai = $this->retryAihelper([
            'AI Request fehlgeschlagen: dial tcp [2606:4700:4408::ac40:9bd1]:443: connect: network is unreachable',
            'AI Request fehlgeschlagen: upstream connect error or disconnect/reset before headers. retried and the latest reset reason: connection timeout',
            'AI Request fehlgeschlagen: required MCP servers failed to initialize: filesystem: Client error: HTTP request failed: http/request failed: error sending request for url (https://example.test/mcp/)',
            'AI Request fehlgeschlagen: required MCP servers failed to initialize: email: unexpected server response: HTTP 520: error code: 520'
        ]);

        $result = $ai->ask('test');

        $this->assertTrue($result['success']);
        $this->assertSame('ok', $result['response']);
        $this->assertSame(5, $ai->attempts);
        $this->assertSame([true, false, false, false, false], $ai->promptAdditions);
    }

    function test__transient_http2_stream_errors_are_retried(): void
    {
        $ai = $this->retryAihelper([
            'AI Request fehlgeschlagen: stream error: stream ID 1; INTERNAL_ERROR; received from peer'
        ]);

        $result = $ai->ask('test');

        $this->assertTrue($result['success']);
        $this->assertSame('ok', $result['response']);
        $this->assertSame(2, $ai->attempts);
        $this->assertSame([true, false], $ai->promptAdditions);
    }

    function test__permanent_request_errors_are_not_retried(): void
    {
        $ai = $this->retryAihelper(['invalid request']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('invalid request');
        $ai->ask('test');
    }

    function test__interrupted_stream_is_not_retried_after_text_was_emitted(): void
    {
        $ai = $this->retryAihelper(['stream_after_text']);

        try {
            $ai->ask('test');
            $this->fail('Expected the interrupted stream to be surfaced.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('stream disconnected before completion', $exception->getMessage());
        }
        $this->assertSame(1, $ai->attempts);
    }

    function test__cli_usage_limits_parsing(): void
    {
        $ai = aihelper::create(provider: 'test');

        $parser = new \ReflectionMethod($ai, 'parseCliUsageLimits');
        $codexLimits = $parser->invoke(
            $ai,
            'codex',
            <<<'TXT'
            │  Context window:              73% left (77.6K used / 258K)                             │
            │  5h limit:                    [██████████████████░░] 92% left (resets 19:12)           │
            │  Weekly limit:                [███████████████████░] 97% left (resets 03:03 on 6 Jul)  │
            │  GPT-5.3-Codex-Spark limit:                                                            │
            │  5h limit:                    [████████████████████] 100% left (resets 15:52)          │
            TXT
        );

        $this->assertSame('5-hour', $codexLimits[0]['type']);
        $this->assertNull($codexLimits[0]['scope']);
        $this->assertSame(8, $codexLimits[0]['percent used']);
        $this->assertSame('weekly', $codexLimits[1]['type']);
        $this->assertNull($codexLimits[1]['scope']);
        $this->assertSame(3, $codexLimits[1]['percent used']);
        $this->assertMatchesRegularExpression('/T19:12:00/', $codexLimits[0]['resets_at']);

        $claudeLimits = $parser->invoke(
            $ai,
            'claude',
            <<<'TXT'
            Current session
            ██████████████████████████████████████████████████ 100% used
            Resets 5:59pm (Europe/Berlin)

            Current week (all models)
            ██████████████████████████████████▌ 69% used
            Resets Jun 30, 4:59pm (Europe/Berlin)
            TXT
        );

        $this->assertSame('5-hour', $claudeLimits[0]['type']);
        $this->assertNull($claudeLimits[0]['scope']);
        $this->assertSame(100, $claudeLimits[0]['percent used']);
        $this->assertSame('weekly', $claudeLimits[1]['type']);
        $this->assertNull($claudeLimits[1]['scope']);
        $this->assertSame(69, $claudeLimits[1]['percent used']);
        $this->assertMatchesRegularExpression('/T17:59:00\\+02:00/', $claudeLimits[0]['resets_at']);
    }

    function test__opencode_usage_reads_local_messages(): void
    {
        $this->skipOnCi();
        $dataHome = sys_get_temp_dir() . '/aihelper-opencode-' . uniqid('', true);
        mkdir($dataHome . '/opencode', 0777, true);
        $database = $dataHome . '/opencode/opencode.db';
        $connection = new \PDO('sqlite:' . $database, options: [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        $connection->exec('CREATE TABLE message (time_created INTEGER NOT NULL, data TEXT NOT NULL)');
        $statement = $connection->prepare('INSERT INTO message (time_created, data) VALUES (:time_created, :data)');
        $statement->execute([
            'time_created' => (time() - 60) * 1000,
            'data' => json_encode(
                [
                    'role' => 'assistant',
                    'providerID' => 'opencode-go',
                    'modelID' => 'glm-5.2',
                    'cost' => 0.25,
                    'tokens' => [
                        'total' => 150,
                        'input' => 100,
                        'output' => 20,
                        'reasoning' => 30,
                        'cache' => ['read' => 40, 'write' => 10]
                    ]
                ],
                JSON_THROW_ON_ERROR
            )
        ]);
        $statement->execute([
            'time_created' => (time() - 60) * 1000,
            'data' => json_encode(
                [
                    'role' => 'assistant',
                    'providerID' => 'other',
                    'modelID' => 'ignored',
                    'cost' => 99
                ],
                JSON_THROW_ON_ERROR
            )
        ]);

        $previousDataHome = getenv('XDG_DATA_HOME');
        putenv('XDG_DATA_HOME=' . $dataHome);
        $limits = aihelper::create(provider: 'opencode')->getCliUsageLimits();
        $previousDataHome === false ? putenv('XDG_DATA_HOME') : putenv('XDG_DATA_HOME=' . $previousDataHome);

        $this->assertSame('5-hour', $limits[0]['type']);
        $this->assertSame(1, $limits[0]['requests']);
        $this->assertSame(2.08, $limits[0]['percent used']);
        $this->assertTrue($limits[0]['estimated']);
        $this->assertSame(0.25, $limits[0]['used_usd']);
        $this->assertSame(12.0, $limits[0]['limit_usd']);
        $this->assertSame(150, $limits[0]['tokens']['total']);
        $this->assertSame(40, $limits[0]['tokens']['cache_read']);
        $this->assertSame(['glm-5.2'], $limits[0]['models']);

        unlink($database);
        rmdir($dataHome . '/opencode');
        rmdir($dataHome);
    }

    function test__opencode_usage_parses_dashboard_limits(): void
    {
        $method = new \ReflectionMethod(aihelper::create(provider: 'opencode'), 'parseOpenCodeDashboardLimits');
        $limits = $method->invoke(
            null,
            'rollingUsage:$R[1]={resetInSec:17940,usagePercent:0}' .
                ' weeklyUsage:$R[2]={usagePercent:5,resetInSec:583200}' .
                ' monthlyUsage:$R[3]={usagePercent:52,resetInSec:2250000}'
        );

        $this->assertSame(0.0, $limits['5-hour']['percent used']);
        $this->assertSame(5.0, $limits['weekly']['percent used']);
        $this->assertSame(52.0, $limits['monthly']['percent used']);
        $this->assertMatchesRegularExpression('/T/', $limits['5-hour']['resets_at']);
        $this->assertMatchesRegularExpression('/T/', $limits['weekly']['resets_at']);
        $this->assertMatchesRegularExpression('/T/', $limits['monthly']['resets_at']);
    }

    function test__ai_all(): void
    {
        $stats = [];
        file_put_contents('tests/aihelper.log', '');
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_anthropic($stats, true);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_google($stats, true);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_openai($stats, true);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_xai($stats, true);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_deepseek($stats, true);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_openrouter($stats, true);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_llamacpp($stats, true);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_lmstudio($stats, true);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_nvidia($stats, true);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_elevenlabs($stats, true);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_test($stats, true);
        }
        $this->log('stats (' . $this->run_count . ' runs):');
        foreach ($stats as $stats__key => $stats__value) {
            foreach ($stats__value as $stats__value__key => $stats__value__value) {
                $time = 0;
                $costs = 0;
                $fail_count = 0;
                $success_count = 0;
                foreach ($stats__value__value as $stats__value__value__value) {
                    $time += $stats__value__value__value['time'];
                    $costs += $stats__value__value__value['costs'];
                    $fail_count += $stats__value__value__value['fail_count'];
                    $success_count += $stats__value__value__value['success_count'];
                }
                $this->log(
                    $stats__key .
                        ' (' .
                        $stats__value__key .
                        '): ' .
                        ($fail_count === 0 ? '✅' : '⛔') .
                        ' ' .
                        $success_count .
                        '/' .
                        ($success_count + $fail_count) .
                        ' in ' .
                        $time .
                        's (' .
                        number_format($costs, 5, ',', '.') .
                        '€)'
                );
            }
        }
    }

    function test__ai_anthropic(array &$stats = [], bool $force = false): void
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->skipIfMissingEnv('ANTHROPIC_API_KEY', $force)) {
            return;
        }
        $this->ai_test_prepare('anthropic', $_SERVER['ANTHROPIC_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_google(array &$stats = [], bool $force = false): void
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->skipIfMissingEnv('GOOGLE_API_KEY', $force)) {
            return;
        }
        $this->ai_test_prepare('google', $_SERVER['GOOGLE_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_openai(array &$stats = [], bool $force = false): void
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->skipIfMissingEnv('OPENAI_API_KEY', $force)) {
            return;
        }
        $this->ai_test_prepare('openai', $_SERVER['OPENAI_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_xai(array &$stats = [], bool $force = false): void
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->skipIfMissingEnv('XAI_API_KEY', $force)) {
            return;
        }
        $this->ai_test_prepare('xai', $_SERVER['XAI_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_deepseek(array &$stats = [], bool $force = false): void
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->skipIfMissingEnv('DEEPSEEK_API_KEY', $force)) {
            return;
        }
        $this->ai_test_prepare('deepseek', $_SERVER['DEEPSEEK_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_openrouter(array &$stats = [], bool $force = false): void
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->skipIfMissingEnv('OPENROUTER_API_KEY', $force)) {
            return;
        }
        $this->ai_test_prepare('openrouter', $_SERVER['OPENROUTER_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_llamacpp(array &$stats = [], bool $force = false): void
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->skipIfMissingEnv('LLM_URL', $force)) {
            return;
        }
        $this->ai_test_prepare('llamacpp', $_SERVER['LLM_API_KEY'] ?? null, $_SERVER['LLM_URL'] ?? null, $stats);
    }

    function test__ai_lmstudio(array &$stats = [], bool $force = false): void
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->skipIfMissingEnv('LLM_URL', $force)) {
            return;
        }
        $this->ai_test_prepare('lmstudio', $_SERVER['LLM_API_KEY'] ?? null, $_SERVER['LLM_URL'] ?? null, $stats);
    }

    function test__ai_nvidia(array &$stats = [], bool $force = false): void
    {
        if (1 === 1) {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->isCi()) {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->skipIfMissingEnv('NVIDIA_API_KEY', $force)) {
            return;
        }
        $this->ai_test_prepare('nvidia', $_SERVER['NVIDIA_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_test(array &$stats = [], bool $force = false): void
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        $this->ai_test_prepare('test', null, null, $stats);
    }

    function test__ai_elevenlabs(array &$stats = [], bool $force = false): void
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->skipIfMissingEnv('ELEVENLABS_API_KEY', $force)) {
            return;
        }
        $this->ai_test_prepare('elevenlabs', $_SERVER['ELEVENLABS_API_KEY'] ?? null, null, $stats);
    }

    function test__auto_compact(): void
    {
        $session_id = 'auto-compact-test-' . mt_rand(100000, 999999);
        $cache_file =
            sys_get_temp_dir() . '/aihelper-cache/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $session_id) . '.txt';
        @unlink($cache_file);

        // fabricate a session that exceeds ~70% of test provider's ctx (128k).
        // ~90k tokens ≈ ~360k chars. use a compact mixture of roles.
        $bloat = str_repeat('Dies ist ein langer Gesprächsverlauf mit vielen Details. ', 400);
        $path_marker = '/host/data/files/12345678-1234-1234-1234-123456789012/Datei mit Leerzeichen.docx';
        $history = [];
        // head: 10 prepended system-like prompts (matches autoCompactSession's keep_head)
        for ($i = 0; $i < 10; $i++) {
            $history[] = ['role' => 'user', 'content' => '# SKILL ' . $i . "\n\nInstruktionen für den Assistenten."];
        }
        // middle: lots of back-and-forth that we want compacted
        for ($i = 0; $i < 30; $i++) {
            $history[] = ['role' => 'user', 'content' => 'Frage ' . $i . ': ' . $bloat];
            $history[] = ['role' => 'assistant', 'content' => 'Antwort ' . $i . ': ' . $bloat];
        }
        $history[20]['content'] .= ' Erzeugte Datei: ' . $path_marker;
        // tail: retain the current unanswered tool result, but compact a large
        // earlier result that already has an assistant response.
        $tail_marker_user = 'TAIL_USER_MARKER_' . mt_rand(1000, 9999);
        $tail_marker_asst = 'TAIL_ASSISTANT_MARKER_' . mt_rand(1000, 9999);
        $processed_tool_payload = 'PROCESSED_START|' . str_repeat('processed payload ', 5000) . '|PROCESSED_END';
        $pending_tool_payload = str_repeat('pending payload ', 5000);
        $history[] = ['role' => 'user', 'content' => $tail_marker_user . ' frage 1'];
        $history[] = [
            'role' => 'assistant',
            'content' => null,
            'tool_calls' => [['id' => 'processed', 'function' => ['name' => 'read', 'arguments' => '{}']]]
        ];
        $history[] = ['role' => 'tool', 'tool_call_id' => 'processed', 'content' => $processed_tool_payload];
        $history[] = ['role' => 'assistant', 'content' => $tail_marker_asst . ' antwort'];
        $history[] = [
            'role' => 'assistant',
            'content' => null,
            'tool_calls' => [['id' => 'pending', 'function' => ['name' => 'read', 'arguments' => '{}']]]
        ];
        $history[] = ['role' => 'tool', 'tool_call_id' => 'pending', 'content' => $pending_tool_payload];

        $message_count_before = count($history);
        $this->assertGreaterThan(9, $message_count_before);

        $ai = aihelper::create(
            provider: 'test',
            log: 'tests/aihelper.log',
            session_id: $session_id,
            history: $history,
            auto_compact: true
        );
        $this->assertNotNull($ai);

        // trigger compaction explicitly (so we don't depend on ask() side effects)
        $ai->autoCompactSession();

        $session_after = $ai->getSessionContent();
        $this->assertLessThan($message_count_before, count($session_after), 'session should shrink after compaction');
        // head (10) + summary (1) + tail (6) == 17
        $this->assertSame(17, count($session_after), 'head(10) + summary(1) + tail(6) = 17 expected');

        // head is preserved verbatim
        for ($i = 0; $i < 10; $i++) {
            $this->assertSame(
                '# SKILL ' . $i . "\n\nInstruktionen für den Assistenten.",
                $session_after[$i]['content']
            );
        }
        // the processed payload is compacted; the unanswered result and its
        // matching call remain available verbatim.
        $tail_after = array_slice($session_after, -6);
        $this->assertStringContainsString($tail_marker_user, $tail_after[0]['content']);
        $this->assertSame('processed', $tail_after[1]['tool_calls'][0]['id']);
        $this->assertStringContainsString('während Kontext-Kompression entfernt', $tail_after[2]['content']);
        $this->assertStringContainsString('PROCESSED_START', $tail_after[2]['content']);
        $this->assertStringContainsString('PROCESSED_END', $tail_after[2]['content']);
        $this->assertLessThan(12000, mb_strlen($tail_after[2]['content']));
        $this->assertStringContainsString($tail_marker_asst, $tail_after[3]['content']);
        $this->assertSame('pending', $tail_after[4]['tool_calls'][0]['id']);
        $this->assertSame($pending_tool_payload, $tail_after[5]['content']);

        // summary message sits between head and tail (index = keep_head),
        // carries the banner text. content shape differs per provider
        // (ai_test → ai_anthropic returns content as an array of blocks, not a
        // plain string) — serialize for a shape-agnostic substring check.
        $summary_msg = $session_after[10];
        $this->assertArrayHasKey('content', $summary_msg);
        $this->assertStringContainsString(
            'Zusammenfassung des bisherigen Verlaufs',
            json_encode($summary_msg['content'])
        );
        $this->assertStringContainsString('Frage 0', json_encode($summary_msg['content'], JSON_UNESCAPED_UNICODE));
        $this->assertStringContainsString($path_marker, json_encode($summary_msg['content'], JSON_UNESCAPED_SLASHES));

        // persistence: running summary should now live on disk under /tmp/aihelper-cache/
        $this->assertFileExists($cache_file, 'running summary must be persisted to disk');
        $persisted = file_get_contents($cache_file);
        $this->assertNotEmpty($persisted);

        // noop: with auto_compact=false, even a bloated session stays untouched
        $ai3 = aihelper::create(
            provider: 'test',
            log: 'tests/aihelper.log',
            session_id: 'no-compact-test-' . mt_rand(100000, 999999),
            history: $history,
            auto_compact: false
        );
        $ai3->autoCompactSession();
        $this->assertSame($message_count_before, count($ai3->getSessionContent()));

        $parallel_session_id = 'auto-compact-parallel-test-' . mt_rand(100000, 999999);
        $parallel_cache_file =
            sys_get_temp_dir() .
            '/aihelper-cache/' .
            preg_replace('/[^a-zA-Z0-9_\-]/', '_', $parallel_session_id) .
            '.txt';
        if (is_file($parallel_cache_file)) {
            unlink($parallel_cache_file);
        }
        $parallel_history = [];
        for ($i = 0; $i < 9; $i++) {
            $parallel_history[] = ['role' => 'user', 'content' => '# SKILL ' . $i . "\n\n" . $bloat];
        }
        $parallel_history[] = [
            'role' => 'assistant',
            'content' => null,
            'tool_calls' => [
                ['id' => 'parallel-1', 'function' => ['name' => 'read', 'arguments' => '{}']],
                ['id' => 'parallel-2', 'function' => ['name' => 'read', 'arguments' => '{}']],
                ['id' => 'parallel-3', 'function' => ['name' => 'read', 'arguments' => '{}']],
                ['id' => 'parallel-4', 'function' => ['name' => 'read', 'arguments' => '{}']]
            ]
        ];
        for ($i = 1; $i <= 4; $i++) {
            $parallel_history[] = [
                'role' => 'tool',
                'tool_call_id' => 'parallel-' . $i,
                'content' => str_repeat('parallel payload ', 5000)
            ];
        }
        for ($i = 0; $i < 6; $i++) {
            $parallel_history[] = ['role' => 'assistant', 'content' => 'Tail ' . $i . ': ' . $bloat];
        }
        $parallel_ai = aihelper::create(
            provider: 'test',
            log: 'tests/aihelper.log',
            session_id: $parallel_session_id,
            history: $parallel_history,
            auto_compact: true
        );
        $this->assertNotNull($parallel_ai);
        $parallel_ai->autoCompactSession();
        $parallel_session_after = $parallel_ai->getSessionContent();
        $parallel_assistant_index = null;
        foreach ($parallel_session_after as $message_index => $message) {
            if (($message['tool_calls'][0]['id'] ?? null) === 'parallel-1') {
                $parallel_assistant_index = $message_index;
                break;
            }
        }
        $this->assertNotNull($parallel_assistant_index);
        for ($i = 1; $i <= 4; $i++) {
            $parallel_tool_message = $parallel_session_after[$parallel_assistant_index + $i] ?? [];
            $this->assertSame('tool', $parallel_tool_message['role'] ?? null);
            $this->assertSame('parallel-' . $i, $parallel_tool_message['tool_call_id'] ?? null);
        }

        @unlink($cache_file);
        @unlink($parallel_cache_file);
    }

    function test__artificial_analysis_model_enrichment(): void
    {
        $artificialAnalysisApiKey = $_SERVER['ARTIFICIAL_ANALYSIS_API_KEY'] ?? null;
        if ($artificialAnalysisApiKey === null || $artificialAnalysisApiKey === '') {
            $artificialAnalysisApiKey = $_ENV['ARTIFICIAL_ANALYSIS_API_KEY'] ?? null;
        }
        if ($artificialAnalysisApiKey === null || $artificialAnalysisApiKey === '') {
            $artificialAnalysisApiKey = getenv('ARTIFICIAL_ANALYSIS_API_KEY') ?: null;
        }
        if ($artificialAnalysisApiKey === null || $artificialAnalysisApiKey === '') {
            $this->markTestSkipped('Skipped.');
        }
        $models = aihelper::create(provider: 'openai', api_key: $_SERVER['OPENAI_API_KEY'] ?? null)->models;
        $hasArtificialAnalysisData = false;
        foreach ($models as $model) {
            if (($model['artificial_analysis_intelligence_index'] ?? null) === null) {
                continue;
            }
            $hasArtificialAnalysisData = true;
            break;
        }
        $this->assertTrue($hasArtificialAnalysisData);
    }

    function ai_test_prepare(string $provider, ?string $api_key = null, ?string $url = null, array &$stats = []): void
    {
        $models = aihelper::create(
            provider: $provider,
            api_key: $api_key,
            url: $url,
            log: 'tests/aihelper.log'
        )->getTestModels();
        if (!empty($models)) {
            foreach ($models as $models__value) {
                __::log_begin('ai');
                [$costs, $success_count, $fail_count] = $this->ai_test($provider, $models__value, $api_key, $url);
                $time = __::log_end('ai', false)['time'];
                if (!isset($stats[$provider])) {
                    $stats[$provider] = [];
                }
                if (!isset($stats[$provider][$models__value])) {
                    $stats[$provider][$models__value] = [];
                }
                $stats[$provider][$models__value][] = [
                    'time' => $time,
                    'costs' => $costs,
                    'fail_count' => $fail_count,
                    'success_count' => $success_count
                ];
            }
        }
    }

    function modelSupports(aihelper $ai, string $model, string $cap): bool
    {
        foreach ($ai->models as $m) {
            if (($m['name'] ?? null) === $model) {
                return ($m[$cap] ?? false) === true;
            }
        }
        return false;
    }

    function ai_test(string $provider, string $model, ?string $api_key, ?string $url): array
    {
        $this->log('Testing ' . $provider . ' (' . $model . ')...');

        $ai = aihelper::create(
            provider: $provider,
            model: $model,
            temperature: 1.0,
            max_tries: 2,
            api_key: $api_key,
            session_id: null,
            log: 'tests/aihelper.log',
            url: $url
        );

        $costs = 0;
        $fail_count = 0;
        $success_count = 0;

        $supported = in_array($provider, [
            'anthropic',
            'google',
            'openai',
            'xai',
            'deepseek',
            'openrouter',
            'llamacpp',
            'lmstudio',
            'nvidia'
        ]);
        if ($supported === true) {
            $return = $ai->ping();
            $this->log($return);
            $success_this = $return === true;
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $this->log(($success_this ? '✅' : '⛔') . ' #1 (ping)');
        }

        $supported = in_array($provider, [
            'anthropic',
            'google',
            'openai',
            'xai',
            'deepseek',
            'openrouter',
            'llamacpp',
            'lmstudio',
            'nvidia'
        ]);
        if ($supported === true) {
            $return = $ai->ask('Wer wurde 2018 Fußball-Weltmeister? Antworte bitte kurz.');
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 2 &&
                (stripos($return['response'], 'Frankreich') !== false ||
                    stripos($return['response'], 'französisch') !== false);
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #1 (simple)');
            if ($success_this === false) {
                $this->log([$return, count($ai->getSessionContent())]);
            }
        }

        $supported = in_array($provider, [
            'anthropic',
            'google',
            'openai',
            'xai',
            'deepseek',
            'openrouter',
            'llamacpp',
            'lmstudio',
            'nvidia'
        ]);
        if ($supported === true) {
            $return = $ai->ask('Was habe ich vorher gefragt?');
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 4 &&
                (stripos($return['response'], 'Wer wurde 2018 Fußball-Weltmeister?') !== false ||
                    stripos($return['response'], 'Frankreich') !== false ||
                    stripos($return['response'], 'französisch') !== false ||
                    stripos($return['response'], 'Weltmeister') !== false);
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #2 (simple)');
            if ($success_this === false) {
                $this->log([$return, count($ai->getSessionContent())]);
            }
        }

        $supported = in_array($provider, [
            'anthropic',
            'google',
            'openai',
            'xai',
            'deepseek',
            'openrouter',
            'llamacpp',
            'lmstudio',
            'nvidia'
        ]);
        if ($supported === true) {
            $return = $ai->ask('Welchen Satz hast Du exakt zuvor geschrieben?');
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 6 &&
                (stripos($return['response'], 'Wer wurde 2018 Fußball-Weltmeister?') !== false ||
                    stripos($return['response'], 'Frankreich') !== false ||
                    stripos($return['response'], 'französisch') !== false ||
                    stripos($return['response'], 'Weltmeister') !== false);
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #3 (memory)');
            if ($success_this === false) {
                $this->log([$return, count($ai->getSessionContent())]);
            }
        }

        $supported = in_array($provider, [
            'anthropic',
            'google',
            'openai',
            'xai',
            'deepseek',
            'openrouter',
            'llamacpp',
            'lmstudio',
            'nvidia'
        ]);
        if ($supported === true) {
            $return = $ai->ask('Ich heiße David mit Vornamen. Bitte merk Dir das!');
            //$this->log($return);
            $ai = aihelper::create(
                provider: $provider,
                model: $model,
                temperature: 1.0,
                max_tries: 2,
                api_key: $api_key,
                session_id: $ai->getSessionId(),
                log: 'tests/aihelper.log',
                url: $url
            );
            $return = $ai->ask('Wie heiße ich mit Vornamen?');
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 10 &&
                stripos($return['response'], 'David') !== false;
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #4 (memory)');
            if ($success_this === false) {
                $this->log([$return, count($ai->getSessionContent())]);
            }
        }

        $supported = in_array($provider, [
            'anthropic',
            'google',
            'openai',
            'xai',
            'deepseek',
            'openrouter',
            'llamacpp',
            'lmstudio',
            'nvidia'
        ]);
        if ($supported === true) {
            $ai = aihelper::create(
                provider: $provider,
                model: $model,
                temperature: 1.0,
                max_tries: 2,
                api_key: $api_key,
                history: $ai->getSessionContent(),
                log: 'tests/aihelper.log',
                url: $url
            );
            $return = $ai->ask('Wie heiße ich mit Vornamen?');
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 12 &&
                stripos($return['response'], 'David') !== false;
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #5 (memory)');
            if ($success_this === false) {
                $this->log([$return, count($ai->getSessionContent())]);
            }
        }

        $supported = in_array($provider, ['anthropic', 'google', 'openai', 'xai', 'openrouter']);
        if ($supported === true) {
            $return = $ai->ask('Was ist auf dem Bild zu sehen?', 'tests/assets/iptc_write.jpg');

            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 14 &&
                (stripos($return['response'], 'Tulpe') !== false ||
                    stripos($return['response'], 'Tulpen') !== false ||
                    stripos($return['response'], 'Tulip') !== false ||
                    stripos($return['response'], 'Tulipe') !== false ||
                    stripos($return['response'], 'Tulipan') !== false);
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #6 (image)');
            if ($success_this === false) {
                $this->log([$return, $ai->getSessionContent(), count($ai->getSessionContent())]);
            }
        }

        $supported = in_array($provider, ['anthropic', 'google', 'openai', 'xai', 'openrouter']);
        if ($supported === true) {
            $return = $ai->ask('Welches Bild habe ich im Gesprächsverlauf hochgeladen?');

            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 16 &&
                (stripos($return['response'], 'Tulpe') !== false ||
                    stripos($return['response'], 'Tulpen') !== false ||
                    stripos($return['response'], 'Tulip') !== false ||
                    stripos($return['response'], 'Tulipe') !== false ||
                    stripos($return['response'], 'Tulipan') !== false);
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #7 (image)');
            if ($success_this === false) {
                $this->log([$return, count($ai->getSessionContent())]);
            }
        }

        $supported = in_array($provider, ['anthropic', 'google', 'openai', 'openrouter']);
        if ($supported === true) {
            $return = $ai->ask(
                'Wie lautet die Kundennummer (Key: customer_nr)? Wann wurde der Brief verfasst (Key: date)? Von wem wurde der Brief verfasst (Key: author)? Bitte antworte nur im JSON-Format. Wenn Du unsicher bist, gib den wahrscheinlichsten Wert zurück. Wenn Du einen Wert gar nicht findest, gib einen leeren String zurück.',
                'tests/assets/lorem.pdf'
            );
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 18 &&
                in_array($return['response']->customer_nr ?? '', ['F123465789']) &&
                !empty(
                    array_filter(['31.10.2018', '31. Oktober 2018', 'Oktober 2018', '2018-10-31'], function (
                        $value
                    ) use ($return) {
                        return strpos($value, $return['response']->date ?? '') !== false;
                    })
                ) &&
                in_array($return['response']->author ?? '', ['David Vielhuber']);
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #8 (pdf)');
            if ($success_this === false) {
                $this->log([$return, count($ai->getSessionContent())]);
            }
        }

        $supported = in_array($provider, ['anthropic', 'google', 'openai', 'openrouter']);
        if ($supported === true) {
            $return = $ai->ask(
                'Wie lautet die Kundennummer (Key: customer_nr)? Wie lautet die Zählernummer (Key: meter_number)? Welche Blume ist auf dem Bild zu sehen (Key: flower)? Bitte antworte nur im JSON-Format. Wenn Du unsicher bist, gib den wahrscheinlichsten Wert zurück. Wenn Du einen Wert gar nicht findest, gib einen leeren String zurück.',
                [
                    'tests/assets/lorem.pdf',
                    'tests/assets/lorem2.pdf',
                    'tests/assets/iptc_write.jpg',
                    'tests/assets/not_existing.jpg'
                ]
            );
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 20 &&
                in_array($return['response']->customer_nr ?? '', ['F123465789']) &&
                in_array($return['response']->meter_number ?? '', ['123456789']) &&
                in_array($return['response']->flower ?? '', [
                    'Tulpe',
                    'Tulpen',
                    'Tulip',
                    'Tulips',
                    'Tulipe',
                    'Tulipan',
                    'tulpe',
                    'tulpen',
                    'tulip',
                    'tulips',
                    'tulipe',
                    'tulipan'
                ]);
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #9 (image+pdf)');
            if ($success_this === false) {
                $this->log([$return, count($ai->getSessionContent())]);
            }
        }

        $supported = in_array($provider, [
            'anthropic',
            'openai',
            'openrouter',
            'llamacpp',
            'lmstudio',
            'nvidia',
            'test'
        ]);
        if ($supported === true) {
            $ai_stream = aihelper::create(
                provider: $provider,
                model: $model,
                temperature: 1.0,
                api_key: $api_key,
                session_id: null,
                log: 'tests/aihelper.log',
                url: $url,
                max_tries: 2,
                mcp_servers: null,
                stream: true
            );
            $return = $ai_stream->ask('Wer wurde 2018 Fußball-Weltmeister? Antworte bitte kurz.');
            //$this->log($return);
            $success_this =
                $return['success'] && count($ai_stream->getSessionContent()) >= 2 && mb_strlen($return['response']) > 3;
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #10 (stream)');
            if ($success_this === false) {
                $this->log([$return, count($ai_stream->getSessionContent())]);
            }
        }

        $supported = in_array($provider, ['anthropic', 'openai', 'openrouter', 'llamacpp', 'lmstudio', 'nvidia']);
        if ($supported === true) {
            $ai_stream = aihelper::create(
                provider: $provider,
                model: $model,
                temperature: 1.0,
                api_key: $api_key,
                session_id: null,
                log: 'tests/aihelper.log',
                url: $url,
                max_tries: 2,
                mcp_servers: null,
                stream: true
            );
            $return = $ai_stream->ask('Wer wurde 2018 Fußball-Weltmeister? Antworte bitte kurz.');
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($ai_stream->getSessionContent()) === 2 &&
                (stripos($return['response'], 'Frankreich') !== false ||
                    stripos($return['response'], 'französisch') !== false);
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #10 (stream)');
            if ($success_this === false) {
                $this->log([$return, count($ai_stream->getSessionContent())]);
            }
        }

        if (($_SERVER['MCP_SERVER_TEST'] ?? '') == '1') {
            $supported = in_array($provider, ['anthropic', 'openai', 'llamacpp', 'lmstudio']);
            if ($supported === true) {
                $return = __::curl(
                    $_SERVER['MCP_SERVER_TEST_AUTH_URL'] ?? '',
                    [
                        'client_id' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_ID'] ?? '',
                        'client_secret' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_SECRET'] ?? '',
                        'audience' => $_SERVER['MCP_SERVER_TEST_AUTH_AUDIENCE'] ?? '',
                        'grant_type' => 'client_credentials'
                    ],
                    'POST'
                );
                //$this->log('token: ' . $return->result->access_token);
                $i_url = 1;
                $i_prompt = 1;
                $mcp_servers = [];
                while (
                    ($_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') !=
                    ''
                ) {
                    $mcp_servers[] = [
                        'url' => $_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_URL'],
                        'authorization_token' => $return->result->access_token
                    ];
                    $i_url++;
                }
                $ai_mcp = aihelper::create(
                    provider: $provider,
                    model: $model,
                    temperature: 1.0,
                    api_key: $api_key,
                    session_id: null,
                    log: 'tests/aihelper.log',
                    url: $url,
                    max_tries: 2,
                    mcp_servers: $mcp_servers
                );
                while (
                    ($_SERVER['MCP_SERVER_TEST_PROMPT_' . str_pad((string) $i_prompt, 2, '0', STR_PAD_LEFT)] ?? '') !=
                        '' &&
                    ($_SERVER['MCP_SERVER_TEST_ANSWER_' . str_pad((string) $i_prompt, 2, '0', STR_PAD_LEFT)] ?? '') !=
                        ''
                ) {
                    $return = $ai_mcp->ask(
                        $_SERVER['MCP_SERVER_TEST_PROMPT_' . str_pad((string) $i_prompt, 2, '0', STR_PAD_LEFT)]
                    );
                    $success_this =
                        $return['success'] &&
                        count($ai_mcp->getSessionContent()) === $i_prompt * 2 &&
                        stripos(
                            $return['response'],
                            $_SERVER['MCP_SERVER_TEST_ANSWER_' . str_pad((string) $i_prompt, 2, '0', STR_PAD_LEFT)]
                        ) !== false;
                    if ($success_this) {
                        $success_count++;
                    } else {
                        $fail_count++;
                    }
                    $costs += $return['costs'];
                    $this->log(($success_this ? '✅' : '⛔') . ' #11 (mcp nr ' . $i_prompt . ')');
                    if ($success_this === false) {
                        $this->log([$return, count($ai_mcp->getSessionContent())]);
                    }
                    $i_prompt++;
                }
            }
        }

        // text_to_audio
        if ($this->modelSupports($ai, $model, 'supports_text_to_audio')) {
            $out = sys_get_temp_dir() . '/aihelper-test-' . uniqid() . '.mp3';
            $return = $ai->audio(prompt: 'Hallo, dies ist ein Test der Sprachsynthese.', output_file: $out);
            $success_this =
                ($return['success'] ?? false) === true &&
                is_string($return['response'] ?? null) &&
                is_file($return['response']) &&
                filesize($return['response']) > 0;
            @unlink($out);
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += (float) ($return['costs'] ?? 0);
            $this->log(($success_this ? '✅' : '⛔') . ' #12 (text_to_audio)');
            if ($success_this === false) {
                $this->log([$return]);
            }
        }

        // audio_to_text
        if ($this->modelSupports($ai, $model, 'supports_audio_to_text')) {
            $ai_audio = aihelper::create(
                provider: $provider,
                model: $model,
                max_tries: 2,
                api_key: $api_key,
                log: 'tests/aihelper.log',
                url: $url
            );
            $return = $ai_audio->ask('Was wird in der Audiodatei gesagt? Antworte knapp.', 'tests/assets/lorem.mp3');
            $success_this =
                ($return['success'] ?? false) === true &&
                stripos((string) ($return['response'] ?? ''), 'Paris') !== false;
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += (float) ($return['costs'] ?? 0);
            $this->log(($success_this ? '✅' : '⛔') . ' #13 (audio_to_text)');
            if ($success_this === false) {
                $this->log([$return]);
            }
        }

        $this->assertTrue($fail_count <= 3);
        return [$costs, $success_count, $fail_count];
    }

    function test__ai_wrong_api_key(): void
    {
        $providers = aihelper::getProviders();
        foreach ([false, true] as $streams__value) {
            foreach ($providers as $providers__value) {
                if (in_array($providers__value['name'], ['test', 'llamacpp', 'lmstudio', 'nvidia'], true)) {
                    continue;
                }
                foreach ($providers__value['models'] as $models__value) {
                    if ($models__value['test'] === true) {
                        $this->log(
                            'Testing wrong API key for ' .
                                $providers__value['name'] .
                                ' (' .
                                $models__value['name'] .
                                ')...'
                        );
                        $ai = aihelper::create(
                            provider: $providers__value['name'],
                            model: $models__value['name'],
                            api_key: '123',
                            log: 'tests/aihelper.log',
                            stream: $streams__value
                        );
                        $return = $ai->ping();
                        $this->assertSame($return, false);
                        $return = $ai->ask('Test!');
                        $this->assertSame($return['success'], false);
                        $this->assertMatchesRegularExpression(
                            '/^$|api|error|missing|auth|provider/i',
                            $return['response'] ?? ''
                        );
                    }
                }
            }
        }
    }

    function test__ai_mcp_meta_tools(): void
    {
        if (($_SERVER['MCP_SERVER_TEST'] ?? '') != '1') {
            $this->markTestSkipped('Skipped.');
        }

        $return = __::curl(
            $_SERVER['MCP_SERVER_TEST_AUTH_URL'] ?? '',
            [
                'client_id' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_ID'] ?? '',
                'client_secret' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_SECRET'] ?? '',
                'audience' => $_SERVER['MCP_SERVER_TEST_AUTH_AUDIENCE'] ?? '',
                'grant_type' => 'client_credentials'
            ],
            'POST'
        );
        //$this->log('token: ' . $return->result->access_token);
        $i_url = 1;
        while (($_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') != '') {
            $status = aihelper::getMcpOnlineStatus(
                $_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_URL'],
                $return->result->access_token
            );
            $this->assertTrue(is_bool($status));
            $this->assertTrue($status);

            $meta = aihelper::getMcpMetaInfo(
                $_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_URL'],
                $return->result->access_token
            );
            $this->assertTrue(array_key_exists('name', $meta));
            $this->assertTrue(array_key_exists('online', $meta));
            $this->assertTrue(array_key_exists('instructions', $meta));
            $this->assertTrue(array_key_exists('tools', $meta));
            $this->assertTrue(is_string($meta['name']));
            $this->assertTrue(is_bool($meta['online']));
            $this->assertTrue(is_string($meta['instructions']));
            $this->assertTrue(is_array($meta['tools']));
            $this->assertTrue($meta['name'] !== '');
            $this->assertTrue($meta['online']);
            $this->assertTrue($meta['instructions'] !== '');
            $this->assertTrue(!empty($meta['tools']) && count($meta['tools']) > 0);

            if (($_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_TOOL'] ?? '') != '') {
                $tool_response = aihelper::callMcpTool(
                    $_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_TOOL'],
                    null,
                    $_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_URL'],
                    $return->result->access_token
                );
                $this->assertTrue(is_array($tool_response));
                $this->assertTrue(isset($tool_response['result']));
                $this->assertTrue(mb_strpos(serialize($tool_response), '"jsonrpc"') !== false);
            }

            $i_url++;
        }

        $status = aihelper::getMcpOnlineStatus('https://tld.test/mcp_invalid_endpoint', 'xxx');
        $this->assertTrue(is_bool($status));
        $this->assertFalse($status);

        $meta = aihelper::getMcpMetaInfo('https://tld.test/mcp_invalid_endpoint', 'xxx');
        $this->assertTrue(array_key_exists('name', $meta));
        $this->assertTrue(array_key_exists('online', $meta));
        $this->assertTrue(array_key_exists('instructions', $meta));
        $this->assertTrue(array_key_exists('tools', $meta));
        $this->assertTrue(is_null($meta['name']));
        $this->assertTrue(is_bool($meta['online']));
        $this->assertTrue(is_null($meta['instructions']));
        $this->assertTrue(is_array($meta['tools']));
        $this->assertNull($meta['name']);
        $this->assertFalse($meta['online']);
        $this->assertNull($meta['instructions']);
        $this->assertTrue(empty($meta['tools']));

        $tool_response = aihelper::callMcpTool('unknown_tool', null, 'https://tld.test/mcp_invalid_endpoint', 'xxx');
        $this->assertNull($tool_response);
    }

    function test__ai_mcp_response_times(): void
    {
        if (($_SERVER['MCP_SERVER_TEST'] ?? '') != '1') {
            $this->markTestSkipped('Skipped.');
        }

        $return = __::curl(
            $_SERVER['MCP_SERVER_TEST_AUTH_URL'] ?? '',
            [
                'client_id' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_ID'] ?? '',
                'client_secret' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_SECRET'] ?? '',
                'audience' => $_SERVER['MCP_SERVER_TEST_AUTH_AUDIENCE'] ?? '',
                'grant_type' => 'client_credentials'
            ],
            'POST'
        );
        $access_token = $return->result->access_token;

        for ($run = 1; $run <= 2; $run++) {
            $mcp_servers_all = [];
            $i_cur = 1;
            while (
                ($_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_cur, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') !=
                ''
            ) {
                $mcp_servers_all[] =
                    $_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_cur, 2, '0', STR_PAD_LEFT) . '_URL'];
                $i_cur++;
            }
            // randomize mcp servers
            shuffle($mcp_servers_all);
            for ($i_cur = 0; $i_cur <= count($mcp_servers_all); $i_cur++) {
                $i_url = 1;
                $mcp_servers = [];
                while ($i_url <= $i_cur) {
                    $url = $mcp_servers_all[$i_url - 1];
                    // replace chat id with random number
                    $url = str_replace('[CHAT_ID]', '[' . rand(100000, 999999) . ']', $url);
                    $mcp_servers[] = [
                        'url' => $url,
                        'authorization_token' => $access_token
                    ];
                    $i_url++;
                }
                $ai_mcp = aihelper::create(
                    provider: 'anthropic',
                    model: 'claude-haiku-4-5',
                    temperature: 1.0,
                    api_key: $_SERVER['ANTHROPIC_API_KEY'] ?? '',
                    session_id: null,
                    log: 'tests/aihelper.log',
                    timeout: 60 * 30,
                    max_tries: 2,
                    mcp_servers: $mcp_servers,
                    stream: false
                );
                $prompt = 'Hallo. Wie geht es Dir?';
                __::log_begin('mcp');
                $return = $ai_mcp->ask($prompt);
                $time = __::log_end('mcp', false)['time'];
                if ($return['success'] === false) {
                    __::o($return);
                }
                $this->assertTrue($return['success']);
                $this->log(
                    'RUN ' .
                        $run .
                        ': Response time with ' .
                        count($mcp_servers) .
                        ' MCP server(s): ' .
                        number_format($time, 2, ',', '.') .
                        ' seconds (' .
                        number_format($return['costs'], 5, '.', ',') .
                        '$).'
                );
            }
        }
    }

    function test__ai_mcp_response_format(): void
    {
        if (($_SERVER['MCP_SERVER_TEST'] ?? '') != '1') {
            $this->markTestSkipped('Skipped.');
        }
        if ($this->skipIfMissingEnv('LLM_URL', false)) {
            return;
        }

        $return = __::curl(
            $_SERVER['MCP_SERVER_TEST_AUTH_URL'] ?? '',
            [
                'client_id' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_ID'] ?? '',
                'client_secret' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_SECRET'] ?? '',
                'audience' => $_SERVER['MCP_SERVER_TEST_AUTH_AUDIENCE'] ?? '',
                'grant_type' => 'client_credentials'
            ],
            'POST'
        );
        $access_token = $return->result->access_token;

        $mcp_servers = [];
        $i_cur = 1;
        while (($_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_cur, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') != '') {
            $url = $_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_cur, 2, '0', STR_PAD_LEFT) . '_URL'];
            // replace chat id with random number
            $url = str_replace('[CHAT_ID]', '[' . rand(100000, 999999) . ']', $url);
            $mcp_servers[] = [
                'url' => $url,
                'authorization_token' => $access_token
            ];
            $i_cur++;
        }
        $ai_mcp = aihelper::create(
            provider: 'lmstudio',
            model: 'qwen3.5-27b-ud',
            temperature: 0.3,
            api_key: $_SERVER['LLM_API_KEY'] ?? '',
            session_id: null,
            log: 'tests/aihelper.log',
            timeout: 60 * 30,
            max_tries: 2,
            mcp_servers: $mcp_servers,
            stream: false,
            url: $_SERVER['LLM_URL'] ?? null
        );
        $return = $ai_mcp->ask('Hallo. Welche Dateien liegen in /tmp?');
        $return = $ai_mcp->ask('Was ist 7+4?');
        $this->assertTrue(mb_strpos($return['response'], '11') !== false);
        $return = $ai_mcp->ask('Wie lautete das Ergebnis vorher?');
        $this->assertTrue(mb_strpos($return['response'], '11') !== false);
    }

    function test__ai_mcp_long_running_task(): void
    {
        if (($_SERVER['MCP_SERVER_TEST'] ?? '') != '1') {
            $this->markTestSkipped('Skipped.');
        }

        $sites = [];
        for ($i = 1; $i <= 10; $i++) {
            $sites[] = 'https://news.ycombinator.com/?p=' . $i;
        }

        $return = __::curl(
            $_SERVER['MCP_SERVER_TEST_AUTH_URL'] ?? '',
            [
                'client_id' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_ID'] ?? '',
                'client_secret' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_SECRET'] ?? '',
                'audience' => $_SERVER['MCP_SERVER_TEST_AUTH_AUDIENCE'] ?? '',
                'grant_type' => 'client_credentials'
            ],
            'POST'
        );

        //$this->log('token: ' . $return->result->access_token);
        $i_url = 1;
        $mcp_servers = [];
        while (($_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') != '') {
            $mcp_servers[] = [
                'url' => $_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_URL'],
                'authorization_token' => $return->result->access_token
            ];
            $i_url++;
        }

        $stream_option = [true, false];
        foreach ($stream_option as $stream_option__key => $stream_option__value) {
            // clean up files in /tests/storage folder
            $files = glob('tests/storage/*.*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }

            $ai_mcp = aihelper::create(
                provider: 'anthropic',
                model: 'claude-haiku-4-5',
                //model: 'claude-sonnet-4-5',
                temperature: 1.0,
                api_key: $_SERVER['ANTHROPIC_API_KEY'] ?? '',
                session_id: null,
                log: 'tests/aihelper.log',
                timeout: 60 * 30,
                max_tries: 2,
                mcp_servers: $mcp_servers,
                stream: $stream_option__value
            );
            $prompt = '';
            $prompt .= 'Starte einen lang laufenden Test mit folgendem Ablauf:';
            $prompt .= PHP_EOL . PHP_EOL;
            foreach ($sites as $sites__key => $sites__value) {
                $prompt .=
                    '- Öffne https://' . $sites__value . ', suche nach dem neuesten Artikel und gib den Titel aus.';
                $prompt .= PHP_EOL;
                $prompt .=
                    '- Fertige einen Screenshot der Seite mit dem Namen "screenshot-' . ($sites__key + 1) . '.png" an';
                $prompt .= '- Verschiebe den Screenshot in den Ordner /host/aihelper/tests/storage.';
                $prompt .= '- Prüfe stets tatsächlich, ob der Screenshot in /host/aihelper/tests/storage liegt.';
                $prompt .= '- Wenn er fehlt, führe die Aktion erneut durch.';
                $prompt .= PHP_EOL;
            }
            $prompt .= PHP_EOL;
            $prompt .= 'Nutze zum Browsen immer das MCP-Browser-Tool.';
            $prompt .= 'Ich benötige keinen Code, führe die Aktionen alle selbst aus.';
            $prompt .= 'Prüfe am Ende, ob alle Dateien vorhanden sind.';
            $prompt .= 'Wenn welche fehlen, erstelle die fehlenden Screenshots.';
            $prompt .= 'Antworte nur auf Deutsch.';
            $return = $ai_mcp->ask($prompt);

            $this->log(
                $return['costs'] .
                    '€ total costs for long running task test with ' .
                    count($sites) .
                    ' sites (stream: ' .
                    ($stream_option__value ? 'yes' : 'no') .
                    ').'
            );

            $this->assertSame(count(glob('tests/storage/*.*')), count($sites));
            $this->log(
                '✅ Long running task test with ' .
                    count($sites) .
                    ' sites ' .
                    ($stream_option__value ? '(stream)' : '(no stream)') .
                    ' completed successfully.'
            );

            // throttle to avoid rate limits on new session
            if ($stream_option__key < count($stream_option) - 1) {
                $throttle = max(60 * 2 * count($sites), 60 * 5);
                $this->log('⏳ Throttling next test for ' . $throttle . ' seconds to avoid rate limits...');
                sleep($throttle);
            }
        }
    }

    function test__ai_mcp_servers_call_type(): void
    {
        if (($_SERVER['MCP_SERVER_TEST'] ?? '') != '1') {
            $this->markTestSkipped('Skipped.');
        }

        $return = __::curl(
            $_SERVER['MCP_SERVER_TEST_AUTH_URL'] ?? '',
            [
                'client_id' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_ID'] ?? '',
                'client_secret' => $_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_SECRET'] ?? '',
                'audience' => $_SERVER['MCP_SERVER_TEST_AUTH_AUDIENCE'] ?? '',
                'grant_type' => 'client_credentials'
            ],
            'POST'
        );
        $access_token = $return->result->access_token;

        // collect all mcp servers
        $mcp_servers = [];
        $i_url = 1;
        while (($_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') != '') {
            $url = $_SERVER['MCP_SERVER_TEST_' . str_pad((string) $i_url, 2, '0', STR_PAD_LEFT) . '_URL'];
            $url = str_replace('[CHAT_ID]', '[' . rand(100000, 999999) . ']', $url);
            $mcp_servers[] = [
                'url' => $url,
                'authorization_token' => $access_token
            ];
            $i_url++;
        }

        // collect all prompts/answers
        $prompts = [];
        $i_prompt = 1;
        while (($_SERVER['MCP_SERVER_TEST_PROMPT_' . str_pad((string) $i_prompt, 2, '0', STR_PAD_LEFT)] ?? '') != '') {
            $prompts[] = [
                'prompt' => $_SERVER['MCP_SERVER_TEST_PROMPT_' . str_pad((string) $i_prompt, 2, '0', STR_PAD_LEFT)],
                'answer' =>
                    $_SERVER['MCP_SERVER_TEST_ANSWER_' . str_pad((string) $i_prompt, 2, '0', STR_PAD_LEFT)] ?? ''
            ];
            $i_prompt++;
        }

        $providers = [
            [
                'provider' => 'anthropic',
                'model' => 'claude-haiku-4-5',
                'api_key' => $_SERVER['ANTHROPIC_API_KEY'] ?? '',
                'url' => null,
                'env_key' => 'ANTHROPIC_API_KEY',
                'call_types' => ['local']
            ],
            [
                'provider' => 'openai',
                'model' => 'gpt-4.1-mini',
                'api_key' => $_SERVER['OPENAI_API_KEY'] ?? '',
                'url' => null,
                'env_key' => 'OPENAI_API_KEY',
                'call_types' => ['local']
            ],
            [
                'provider' => 'google',
                'model' => 'gemini-2.5-flash',
                'api_key' => $_SERVER['GOOGLE_API_KEY'] ?? '',
                'url' => null,
                'env_key' => 'GOOGLE_API_KEY',
                'call_types' => ['local']
            ],
            [
                'provider' => 'llamacpp',
                'model' => 'qwen3.5-27b-ud',
                'api_key' => $_SERVER['LLM_API_KEY'] ?? '',
                'url' => $_SERVER['LLM_URL'] ?? null,
                'env_key' => 'LLM_URL',
                'call_types' => ['local']
            ],
            [
                'provider' => 'lmstudio',
                'model' => 'qwen3.5-27b-ud',
                'api_key' => $_SERVER['LLM_API_KEY'] ?? '',
                'url' => $_SERVER['LLM_URL'] ?? null,
                'env_key' => 'LLM_URL',
                'call_types' => ['local']
            ]
        ];
        $providers = array_values(
            array_filter($providers, function ($provider) {
                return ($_SERVER[$provider['env_key']] ?? '') !== '';
            })
        );
        if (empty($providers)) {
            $this->markTestSkipped('Skipped.');
        }

        $all_passed = true;

        foreach ($providers as $prov) {
            $this->log('--- ' . $prov['provider'] . ' / ' . $prov['model'] . ' ---');
            foreach ($prov['call_types'] as $call_type) {
                foreach ([1, 2] as $mcp_count) {
                    $mcp_subset = array_slice($mcp_servers, 0, $mcp_count);
                    foreach ($prompts as $p_index => $p) {
                        $label =
                            $prov['provider'] .
                            ' / ' .
                            $call_type .
                            ' / ' .
                            $mcp_count .
                            ' mcp(s) / prompt ' .
                            ($p_index + 1);
                        $ai = aihelper::create(
                            provider: $prov['provider'],
                            model: $prov['model'],
                            temperature: 1.0,
                            api_key: $prov['api_key'],
                            session_id: null,
                            log: 'tests/aihelper.log',
                            timeout: 60 * 10,
                            max_tries: 2,
                            mcp_servers: $mcp_subset,
                            mcp_servers_call_type: $call_type,
                            stream: false,
                            url: $prov['url']
                        );
                        $time_start = microtime(true);
                        $result = $ai->ask($p['prompt']);
                        $time = microtime(true) - $time_start;
                        if (!$result['success']) {
                            $this->log(
                                '⛔ ' .
                                    $label .
                                    ': FAILED (' .
                                    number_format($time, 2) .
                                    's) — ' .
                                    mb_substr($result['response'] ?? 'no response', 0, 100)
                            );
                            $all_passed = false;
                        } elseif ($p['answer'] !== '' && mb_stripos($result['response'], $p['answer']) === false) {
                            $this->log(
                                '⛔ ' .
                                    $label .
                                    ': WRONG ANSWER (' .
                                    number_format($time, 2) .
                                    's) — ' .
                                    mb_substr($result['response'], 0, 100)
                            );
                            $all_passed = false;
                        } else {
                            $this->log(
                                '✅ ' .
                                    $label .
                                    ': OK (' .
                                    number_format($result['costs'], 5) .
                                    '$ / ' .
                                    number_format($time, 2) .
                                    's)'
                            );
                        }
                        sleep(10);
                    }
                }
            }
        }

        $this->assertTrue($all_passed, 'Some test combinations failed — see log above');
    }

    function test__ai_missing_or_wrong_models(): void
    {
        $providers = aihelper::getProviders();
        $success = true;
        $compared = 0;
        $normalizeModelName = function (string $model): string {
            $model = strtolower($model);
            $model = preg_replace('/-\d{4}-\d{2}-\d{2}$/', '', $model);
            $model = preg_replace('/-\d{8}$/', '', $model);
            $model = preg_replace('/-\d{4}$/', '', $model);
            $model = preg_replace('/^(claude-(?:opus|sonnet|haiku)-\d+)$/', '$1-0', $model);
            return $model;
        };
        $isIgnoredCatalogModel = function (string $model): bool {
            $model = strtolower($model);
            if (in_array($model, ['anthropic/claude-fable-5', 'gemini-pro-latest', 'z-ai/glm-5v-turbo'], true)) {
                return true;
            }
            foreach (
                [
                    'audio',
                    'dall-e',
                    'deep-research',
                    'embed',
                    'guard',
                    'image',
                    'imagen',
                    'moderation',
                    'realtime',
                    'rerank',
                    'safety',
                    'search-api',
                    'sora',
                    'transcribe',
                    'tts',
                    'veo',
                    'video',
                    'whisper'
                ]
                as $ignoredNeedle
            ) {
                if (str_contains($model, $ignoredNeedle)) {
                    return true;
                }
            }
            return str_contains($model, '-preview') || str_contains($model, '-exp');
        };
        foreach ($providers as $provider) {
            $providerName = $provider['name'];
            $envKey = mb_strtoupper($providerName) . '_API_KEY';
            if (($_SERVER[$envKey] ?? '') === '') {
                continue;
            }
            $modelsExpected = [];
            foreach ($provider['models'] as $model) {
                if (!isset($model['name'])) {
                    continue;
                }
                $modelsExpected[$normalizeModelName($model['name'])] = true;
            }
            $modelsApi = array_map(function ($m) {
                return $m['name'];
            }, aihelper::create(
                provider: $providerName,
                api_key: $_SERVER[$envKey] ?? null,
                url: $_SERVER[mb_strtoupper($providerName) . '_API_URL'] ?? null,
                log: 'tests/aihelper.log'
            )->fetchModelsFromProvider());
            $compared++;
            foreach ($modelsApi as $models__value) {
                if ($isIgnoredCatalogModel($models__value)) {
                    continue;
                }
                if (!isset($modelsExpected[$normalizeModelName($models__value)])) {
                    $this->log(
                        '⚠️ Model ' .
                            $models__value .
                            ' is available via API but not listed in static array or models.dev for provider ' .
                            $providerName
                    );
                }
            }
        }
        if ($compared === 0) {
            $this->markTestSkipped('Skipped.');
        }
        $this->assertTrue($success);
    }

    private function harnessStoreAihelper(string $provider, ?string $home, ?string $authHome = null): object
    {
        return aihelper::create(
            provider: $provider,
            model: 'test',
            log: 'tests/aihelper.log',
            cli_session_home: $home,
            cli_auth_home: $authHome
        );
    }

    private function harnessOverrides(object $ai): array
    {
        return (new \ReflectionMethod($ai, 'harnessEnvironmentOverrides'))->invoke($ai);
    }

    public function test__a_cli_session_home_moves_every_cli_store_below_it()
    {
        $home = sys_get_temp_dir() . '/aihelper-harness-' . getmypid();
        $nativeHome = $home . '/native';
        $previousHome = getenv('HOME');
        putenv('HOME=' . $nativeHome);
        try {
            $overrides = $this->harnessOverrides($this->harnessStoreAihelper('claudecode', $home));
            $this->assertSame($home . '/claude', $overrides['CLAUDE_CONFIG_DIR'] ?? null);

            $overrides = $this->harnessOverrides($this->harnessStoreAihelper('opencode', $home));
            $this->assertSame($home . '/opencode/data', $overrides['XDG_DATA_HOME'] ?? null);
            $this->assertSame($home . '/opencode/config', $overrides['XDG_CONFIG_HOME'] ?? null);

            $this->assertDirectoryExists($home . '/claude');
            $this->assertDirectoryExists($home . '/opencode/data/opencode');
            $this->assertFileDoesNotExist($home . '/claude/.credentials.json');
            $this->assertFileDoesNotExist($home . '/opencode/data/opencode/auth.json');
        } finally {
            putenv($previousHome === false ? 'HOME' : 'HOME=' . $previousHome);
            __::rrmdir($home);
        }
    }

    public function test__without_a_cli_session_home_the_clis_keep_their_native_store()
    {
        $overrides = $this->harnessOverrides($this->harnessStoreAihelper('claudecode', null));
        $this->assertArrayNotHasKey('CLAUDE_CONFIG_DIR', $overrides);

        $overrides = $this->harnessOverrides($this->harnessStoreAihelper('opencode', null));
        $this->assertArrayNotHasKey('XDG_DATA_HOME', $overrides);
    }

    public function test__harness_authentication_can_be_shared_by_separate_session_stores()
    {
        $home = sys_get_temp_dir() . '/aihelper-harness-auth-' . getmypid();
        mkdir($home . '/auth/claude', 0700, true);
        mkdir($home . '/auth/codex', 0700, true);
        mkdir($home . '/auth/opencode/data/opencode', 0700, true);
        file_put_contents($home . '/auth/claude/.credentials.json', '{}');
        file_put_contents($home . '/auth/codex/auth.json', '{}');
        file_put_contents($home . '/auth/opencode/data/opencode/auth.json', '{}');
        $normalizePath = static fn(string $path): string => str_replace('\\', '/', $path);
        try {
            $this->harnessOverrides(
                $this->harnessStoreAihelper('claudecode', $home . '/chat-a', $home . '/auth')
            );
            $this->harnessOverrides(
                $this->harnessStoreAihelper('opencode', $home . '/chat-b', $home . '/auth')
            );
            $codex = $this->harnessStoreAihelper('codex', $home . '/chat-c', $home . '/auth');
            $systemPrompt = new \ReflectionProperty($codex, 'system_prompt');
            $systemPrompt->setValue($codex, 'test');
            $this->harnessOverrides($codex);
            $this->assertFalse(is_link($home . '/chat-c/codex/skills'));
            $this->assertSame(
                $normalizePath($home . '/auth/claude/.credentials.json'),
                $normalizePath((string) readlink($home . '/chat-a/claude/.credentials.json'))
            );
            $this->assertSame(
                $normalizePath($home . '/auth/opencode/data/opencode/auth.json'),
                $normalizePath((string) readlink($home . '/chat-b/opencode/data/opencode/auth.json'))
            );
            $this->assertSame(
                $normalizePath($home . '/auth/codex/auth.json'),
                $normalizePath((string) readlink($home . '/chat-c/codex/auth.json'))
            );
        } finally {
            __::rrmdir($home);
        }
    }

    public function test__harness_usage_cache_is_isolated_by_authentication_profile()
    {
        $first = $this->harnessStoreAihelper('codex', null, '/profiles/codex-primary');
        $second = $this->harnessStoreAihelper('codex', null, '/profiles/codex-secondary');
        $method = new \ReflectionMethod($first, 'getCliUsageCacheKey');

        $this->assertNotSame($method->invoke($first, 'codex'), $method->invoke($second, 'codex'));
    }
}
