<?php
namespace vielhuber\aihelper;

use vielhuber\stringhelper\__;

abstract class aihelper
{
    public $provider = null;
    public $title = null;
    public $name = null;
    protected $url = null;
    public $models = [];
    public $support_mcp = null;
    public $support_stream = null;

    protected $model = null;
    protected $temperature = null;
    protected $timeout = null;
    protected $api_key = null;
    protected $log = null;
    protected $max_tries = null;
    protected $mcp_servers = null;

    protected $stream = null;
    protected $stream_response = null;
    protected $stream_event = null;
    protected $stream_buffer_in = null;
    protected $stream_buffer_data = null;
    protected $stream_current_block_type = null;
    protected $stream_first_text_sent = false;
    protected $stream_running = false;
    protected $stream_in_think = false;
    protected $stream_think_tag_buf = '';

    protected $session_id = null;
    protected static $sessions = [];

    public static function create(
        string $provider,
        ?string $model = null,
        ?float $temperature = null,
        ?int $timeout = null,
        ?string $api_key = null,
        ?string $log = null,
        ?int $max_tries = null,
        ?array $mcp_servers = null,
        ?string $session_id = null,
        ?array $history = null,
        ?bool $stream = null,
        ?string $url = null
    ): ?self {
        if ($provider === 'chatgpt') {
            return new ai_chatgpt(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url
            );
        }
        if ($provider === 'claude') {
            return new ai_claude(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url
            );
        }
        if ($provider === 'gemini') {
            return new ai_gemini(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url
            );
        }
        if ($provider === 'grok') {
            return new ai_grok(
                model: $model,
                temperature: $temperature,
                timeout: $timeout,
                api_key: $api_key,
                log: $log,
                max_tries: $max_tries,
                mcp_servers: $mcp_servers,
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url
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
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url
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
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url
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
                session_id: $session_id,
                history: $history,
                stream: $stream,
                url: $url
            );
        }
        return null;
    }

    public static function getProviders(): array
    {
        $data = [];
        foreach (
            [
                new ai_claude(),
                new ai_gemini(),
                new ai_chatgpt(),
                new ai_grok(),
                new ai_deepseek(),
                new ai_lmstudio(),
                new ai_test()
            ]
            as $providers__value
        ) {
            $data[] = [
                'provider' => $providers__value->provider,
                'title' => $providers__value->title,
                'name' => $providers__value->name,
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
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $initResponse = curl_exec($ch);
            if ($initResponse) {
                // parse sse response if needed
                if (strpos($initResponse, 'event: message') !== false) {
                    preg_match('/data: (.+)/s', $initResponse, $matches);
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
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $toolsResponse = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode >= 200 && $httpCode < 300 && $toolsResponse) {
                // parse sse response if needed
                if (strpos($toolsResponse, 'event: message') !== false) {
                    preg_match('/data: (.+)/s', $toolsResponse, $matches);
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
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
                        'input' => (object) $args
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
                    preg_match('/data: (.+)/s', $response, $matches);
                    if (isset($matches[1])) {
                        $response = trim($matches[1]);
                    }
                }
                $decoded_response = json_decode($response, true);
                return $decoded_response;
            }
            return null;
        } catch (\Exception $e) {
            print_r($e->getMessage());
            die();
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
        ?string $session_id = null,
        ?array $history = null,
        ?bool $stream = null,
        ?string $url = null
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
        if ($api_key !== null) {
            $this->api_key = $api_key;
        }
        if (empty($this->models) && method_exists($this, 'fetchModels')) {
            foreach ($this->fetchModels() as $models__key => $models__value) {
                $this->models[] = [
                    'name' => $models__value['name'],
                    'max_tokens' => $models__value['max_tokens'],
                    'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
                    'supports_temperature' => $models__value['supports_temperature'] ?? true,
                    'default' => $models__key === 0 ? true : false,
                    'test' => $models__key === 0 ? true : false
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
        if ($this->support_mcp && $mcp_servers !== null && !empty($mcp_servers)) {
            if (is_array(current($mcp_servers))) {
                $this->mcp_servers = $mcp_servers;
            } else {
                $this->mcp_servers = [$mcp_servers];
            }
        }
        $this->stream = $this->support_stream && $stream === true ? true : false;

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
    }

    public function ask(?string $prompt = null, mixed $files = null): array
    {
        $return = ['response' => null, 'success' => false, 'costs' => 0.0];
        $max_tries = $this->max_tries;
        while ($return['success'] === false && $max_tries > 0) {
            if ($max_tries < $this->max_tries) {
                $this->log('⚠️ tries left: ' . $max_tries);
            }
            $return = $this->askThis(
                prompt: $prompt,
                files: $files,
                add_prompt_to_session: $max_tries === $this->max_tries,
                prev_output_text: null,
                prev_costs: $return['costs']
            );
            $this->log($return, 'return');
            $max_tries--;
        }
        return $return;
    }

    abstract public function fetchModels(): array;

    abstract protected function askThis(
        ?string $prompt = null,
        mixed $files = null,
        bool $add_prompt_to_session = true,
        ?string $prev_output_text = null,
        float $prev_costs = 0.0
    ): array;

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

    protected function stripThinkingBlocks(string $text): string
    {
        // remove <think>...</think> blocks produced by reasoning models (e.g. QwQ)
        return trim(preg_replace('/<think>.*?<\/think>\s*/s', '', $text));
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
        if (strpos(trim($msg), '```json') === 0 || __::string_is_json($msg)) {
            $msg = json_decode(trim(rtrim(ltrim(ltrim(trim($msg), '```json'), '```'), '```')));
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

    protected function getMaxTokensForModel(): int
    {
        foreach ($this->models as $models__value) {
            if ($models__value['name'] === $this->model) {
                return $models__value['max_tokens'];
            }
        }
        return 4096;
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

        $costs = 0;
        foreach ($this->models as $models__value) {
            if ($models__value['name'] === $this->model) {
                $costs =
                    $input_tokens * $models__value['costs']['input'] +
                    $input_cached_tokens * $models__value['costs']['input_cached'] +
                    $output_tokens * $models__value['costs']['output'];
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

        if ($this->name === 'claude' || $this->name === 'test') {
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

            $stream_callback = function ($chunk) {
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
                            'message' => $parsed['error']['message'] ?? null
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
                                    if (isset($parsed['delta']['thinking'])) {
                                        if (!isset($block->thinking)) {
                                            $block->thinking = '';
                                        }
                                        $block->thinking .= $parsed['delta']['thinking'];
                                        echo "event: reasoning\n";
                                        echo 'data: ' . json_encode(['delta' => $parsed['delta']['thinking']]) . "\n\n";
                                        if (ob_get_level() > 0) {
                                            ob_flush();
                                        }
                                        flush();
                                        $this->stream_running = false;
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
                                        // convert empty string to empty array (API expects object/array, not string)
                                        if ($block->input === '') {
                                            $block->input = [];
                                        } else {
                                            $parsedInput = json_decode($block->input, true);
                                            if ($parsedInput !== null) {
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
                                    $parsed['usage']['input_tokens'] ?? null;
                                $this->stream_response->result->usage->cache_creation_input_tokens +=
                                    $parsed['usage']['cache_creation_input_tokens'] ?? null;
                                $this->stream_response->result->usage->cache_read_input_tokens +=
                                    $parsed['usage']['cache_read_input_tokens'] ?? null;
                                $this->stream_response->result->usage->output_tokens +=
                                    $parsed['usage']['output_tokens'] ?? null;
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

        if ($this->name === 'chatgpt' || $this->name === 'lmstudio') {
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

            $stream_callback = function ($chunk) {
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
                            'message' => $parsed['error']['message'] ?? null
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
                                // finally sleep to ensure all chunks arrive
                                sleep(2);
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

        if (!headers_sent()) {
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
        // 2k padding (for browsers)
        ob_implicit_flush(true);
        echo ': pad ' . str_repeat(' ', 2048) . "\n\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();

        return $stream_callback;
    }
}

class ai_chatgpt extends aihelper
{
    public $provider = 'OpenAI';

    public $title = 'ChatGPT';

    public $name = 'chatgpt';

    protected $url = 'https://api.openai.com/v1';

    public $support_mcp = true;

    public $support_stream = true;

    public $models = [
        [
            'name' => 'gpt-5',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'gpt-5-mini',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000025, 'input_cached' => 0.000000025, 'output' => 0.000002],
            'supports_temperature' => false,
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'gpt-5-nano',
            'max_tokens' => 32768,
            'costs' => ['input' => 0.00000005, 'input_cached' => 0.000000005, 'output' => 0.0000004],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1',
            'max_tokens' => 32768,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000005, 'output' => 0.000008],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o',
            'max_tokens' => 16384,
            'costs' => ['input' => 0.0000025, 'input_cached' => 0.00000125, 'output' => 0.00001],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-mini',
            'max_tokens' => 16384,
            'costs' => ['input' => 0.00000015, 'input_cached' => 0.000000075, 'output' => 0.0000006],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2-2025-12-11',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2-chat-latest',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2-pro',
            'max_tokens' => 272000,
            'costs' => ['input' => 0.000021, 'input_cached' => 0.000021, 'output' => 0.000168],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2-pro-2025-12-11',
            'max_tokens' => 272000,
            'costs' => ['input' => 0.000021, 'input_cached' => 0.000021, 'output' => 0.000168],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.2-codex',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-2026-03-05',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-pro',
            'max_tokens' => 272000,
            'costs' => ['input' => 0.000021, 'input_cached' => 0.000021, 'output' => 0.000168],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-pro-2026-03-05',
            'max_tokens' => 272000,
            'costs' => ['input' => 0.000021, 'input_cached' => 0.000021, 'output' => 0.000168],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-mini',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.0000006, 'input_cached' => 0.00000006, 'output' => 0.0000024],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-mini-2026-03-17',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.0000006, 'input_cached' => 0.00000006, 'output' => 0.0000024],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-nano',
            'max_tokens' => 32768,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.00000001, 'output' => 0.0000004],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.4-nano-2026-03-17',
            'max_tokens' => 32768,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.00000001, 'output' => 0.0000004],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1-2025-11-13',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1-chat-latest',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1-codex',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1-codex-mini',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000025, 'input_cached' => 0.000000025, 'output' => 0.000002],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.1-codex-max',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.3-codex',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5.3-chat-latest',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000175, 'input_cached' => 0.000000175, 'output' => 0.000014],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-chat-latest',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-2025-08-07',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-mini-2025-08-07',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000025, 'input_cached' => 0.000000025, 'output' => 0.000002],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-nano-2025-08-07',
            'max_tokens' => 32768,
            'costs' => ['input' => 0.00000005, 'input_cached' => 0.000000005, 'output' => 0.0000004],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-codex',
            'max_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-pro',
            'max_tokens' => 272000,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.000015, 'output' => 0.00012],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-pro-2025-10-06',
            'max_tokens' => 272000,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.000015, 'output' => 0.00012],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1-2025-04-14',
            'max_tokens' => 32768,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000005, 'output' => 0.000008],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1-mini',
            'max_tokens' => 32768,
            'costs' => ['input' => 0.0000004, 'input_cached' => 0.0000001, 'output' => 0.0000016],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1-mini-2025-04-14',
            'max_tokens' => 32768,
            'costs' => ['input' => 0.0000004, 'input_cached' => 0.0000001, 'output' => 0.0000016],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1-nano',
            'max_tokens' => 32768,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.000000025, 'output' => 0.0000004],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1-nano-2025-04-14',
            'max_tokens' => 32768,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.000000025, 'output' => 0.0000004],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000005, 'output' => 0.000008],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3-2025-04-16',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000005, 'output' => 0.000008],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3-pro',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.00002, 'input_cached' => 0.00002, 'output' => 0.00008],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3-pro-2025-06-10',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.00002, 'input_cached' => 0.00002, 'output' => 0.00008],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3-mini',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.0000011, 'input_cached' => 0.00000055, 'output' => 0.0000044],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o3-mini-2025-01-31',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.0000011, 'input_cached' => 0.00000055, 'output' => 0.0000044],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o4-mini',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.0000011, 'input_cached' => 0.000000275, 'output' => 0.0000044],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o4-mini-2025-04-16',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.0000011, 'input_cached' => 0.000000275, 'output' => 0.0000044],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o1',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.0000075, 'output' => 0.00006],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o1-2024-12-17',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.0000075, 'output' => 0.00006],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o1-pro',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.00015, 'input_cached' => 0.00015, 'output' => 0.0006],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'o1-pro-2025-03-19',
            'max_tokens' => 100000,
            'costs' => ['input' => 0.00015, 'input_cached' => 0.00015, 'output' => 0.0006],
            'supports_temperature' => false,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-2024-05-13',
            'max_tokens' => 4096,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.000005, 'output' => 0.000015],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-2024-08-06',
            'max_tokens' => 16384,
            'costs' => ['input' => 0.0000025, 'input_cached' => 0.00000125, 'output' => 0.00001],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-2024-11-20',
            'max_tokens' => 16384,
            'costs' => ['input' => 0.0000025, 'input_cached' => 0.00000125, 'output' => 0.00001],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-mini-2024-07-18',
            'max_tokens' => 16384,
            'costs' => ['input' => 0.00000015, 'input_cached' => 0.000000075, 'output' => 0.0000006],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4-0613',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.00003, 'input_cached' => 0.00003, 'output' => 0.00006],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.00003, 'input_cached' => 0.00003, 'output' => 0.00006],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4-turbo',
            'max_tokens' => 4096,
            'costs' => ['input' => 0.00001, 'input_cached' => 0.00001, 'output' => 0.00003],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4-turbo-2024-04-09',
            'max_tokens' => 4096,
            'costs' => ['input' => 0.00001, 'input_cached' => 0.00001, 'output' => 0.00003],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-3.5-turbo',
            'max_tokens' => 4096,
            'costs' => ['input' => 0.0000005, 'input_cached' => 0.0000005, 'output' => 0.0000015],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-3.5-turbo-1106',
            'max_tokens' => 4096,
            'costs' => ['input' => 0.000001, 'input_cached' => 0.000001, 'output' => 0.000002],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-3.5-turbo-0125',
            'max_tokens' => 4096,
            'costs' => ['input' => 0.0000005, 'input_cached' => 0.0000005, 'output' => 0.0000015],
            'supports_temperature' => true,
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
                    if (
                        in_array($name, [
                            'gpt-5-search-api',
                            'gpt-5-search-api-2025-10-14',
                            'o3-deep-research',
                            'o3-deep-research-2025-06-26',
                            'o4-mini-deep-research',
                            'o4-mini-deep-research-2025-06-26',
                            'gpt-realtime',
                            'gpt-realtime-2025-08-28',
                            'gpt-realtime-1.5',
                            'gpt-realtime-mini',
                            'gpt-realtime-mini-2025-10-06',
                            'gpt-realtime-mini-2025-12-15',
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
                            'gpt-4o-mini-tts',
                            'gpt-4o-mini-tts-2025-03-20',
                            'gpt-4o-mini-tts-2025-12-15',
                            'gpt-3.5-turbo-instruct',
                            'gpt-3.5-turbo-instruct-0914',
                            'gpt-3.5-turbo-16k',
                            'davinci-002',
                            'babbage-002',
                            'gpt-image-1',
                            'gpt-image-1-mini',
                            'gpt-image-1.5',
                            'chatgpt-image-latest',
                            'dall-e-3',
                            'dall-e-2',
                            'sora-2',
                            'sora-2-pro',
                            'text-embedding-3-small',
                            'text-embedding-3-large',
                            'text-embedding-ada-002',
                            'omni-moderation-latest',
                            'omni-moderation-2024-09-26',
                            'tts-1',
                            'tts-1-hd',
                            'tts-1-1106',
                            'tts-1-hd-1106',
                            'whisper-1'
                        ])
                    ) {
                        continue;
                    }
                    $max_tokens = 8192;
                    foreach ($this->models as $definedModel) {
                        if ($definedModel['name'] === $name) {
                            $max_tokens = $definedModel['max_tokens'];
                            break;
                        }
                    }
                    $models[] = ['name' => $name, 'max_tokens' => $max_tokens];
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

                    // strip <think>...</think> blocks before storing in history (e.g. QwQ)
                    foreach ($content as $content_item) {
                        if (is_object($content_item) && isset($content_item->text)) {
                            $content_item->text = $this->stripThinkingBlocks($content_item->text);
                        }
                    }

                    $content = $this->truncateMcpToolResultContent($content);

                    self::$sessions[$this->session_id][] = [
                        'role' => 'assistant',
                        'content' => $content
                    ];
                } elseif (!in_array($output__value->type, ['mcp_call', 'mcp_list_tools', 'reasoning'])) {
                    // mcp_call is excluded: the API does not accept it as input in follow-up requests;
                    // mcp_list_tools is excluded (re-discovered fresh on each call);
                    // reasoning is excluded because the API requires it to be followed by a message item —
                    // if that message is missing or empty, storing reasoning alone causes an API error
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
        float $prev_costs = 0.0
    ): array {
        $return = ['response' => null, 'success' => false, 'costs' => $prev_costs];

        if (__::nx($this->model) || __::nx($this->session_id) || __::nx($prompt)) {
            $return['response'] = 'data missing.';
            return $return;
        }

        $prompt = $this->trimPrompt($prompt);

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

        if (__::nx($output_text ?? null)) {
            $this->log($response, 'failed');
            if (
                __::x($response ?? null) &&
                __::x($response?->result ?? null) &&
                __::x($response?->result?->error ?? null) &&
                __::x($response?->result?->error?->message ?? null) &&
                is_string($response->result->error->message)
            ) {
                $return['response'] = $response->result->error->message;
            }
            return $return;
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
            $args['reasoning'] = ['effort' => 'medium', 'summary' => 'detailed'];
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

class ai_claude extends aihelper
{
    public $provider = 'Anthropic';

    public $title = 'Claude';

    public $name = 'claude';

    protected $url = 'https://api.anthropic.com/v1';

    public $support_mcp = true;

    public $support_stream = true;

    public $models = [
        [
            'name' => 'claude-sonnet-4-6',
            'max_tokens' => 64000,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.0000003, 'output' => 0.000015],
            'supports_temperature' => true,
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-6',
            'max_tokens' => 64000,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.0000005, 'output' => 0.000025],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-sonnet-4-5',
            'max_tokens' => 64000,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.0000003, 'output' => 0.000015],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-5',
            'max_tokens' => 64000,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.0000005, 'output' => 0.000025],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-haiku-4-5',
            'max_tokens' => 64000,
            'costs' => ['input' => 0.000001, 'input_cached' => 0.0000001, 'output' => 0.000005],
            'supports_temperature' => true,
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'claude-sonnet-4-0',
            'max_tokens' => 64000,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.0000003, 'output' => 0.000015],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-1',
            'max_tokens' => 32000,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.0000015, 'output' => 0.000075],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-0',
            'max_tokens' => 32000,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.0000015, 'output' => 0.000075],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-3-haiku-20240307',
            'max_tokens' => 4096,
            'costs' => ['input' => 0.00000025, 'input_cached' => 0.00000003, 'output' => 0.00000125],
            'supports_temperature' => true,
            'default' => false,
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
                            'grok-imagine-image',
                            'grok-imagine-image-pro',
                            'grok-imagine-video',
                            'grok-2-vision-1212'
                        ])
                    ) {
                        continue;
                    }
                    $max_tokens = 8192;
                    foreach ($this->models as $definedModel) {
                        if ($definedModel['name'] === $name) {
                            $max_tokens = $definedModel['max_tokens'];
                            break;
                        }
                    }
                    $models[] = ['name' => $name, 'max_tokens' => $max_tokens];
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

            // fix mcp_tool_use blocks with empty array or string inputs (should be objects)
            if (is_array($content)) {
                for ($i = 0; $i < count($content); $i++) {
                    if (
                        isset($content[$i]->type) &&
                        $content[$i]->type === 'mcp_tool_use' &&
                        isset($content[$i]->input)
                    ) {
                        if (is_array($content[$i]->input) && count($content[$i]->input) === 0) {
                            $content[$i]->input = new \stdClass();
                        }
                        if (is_string($content[$i]->input)) {
                            $decoded = json_decode($content[$i]->input);
                            if (is_object($decoded)) {
                                $content[$i]->input = $decoded;
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
        float $prev_costs = 0.0
    ): array {
        $return = ['response' => null, 'success' => false, 'costs' => $prev_costs];

        if (__::nx($this->model) || __::nx($this->session_id) || __::nx($prompt)) {
            $return['response'] = 'data missing.';
            return $return;
        }

        $prompt = $this->trimPrompt($prompt);

        if ($add_prompt_to_session === true) {
            $this->appendPromptToSession($prompt, $files);
        }

        $args = [
            'model' => $this->model,
            'max_tokens' => $this->getMaxTokensForModel(),
            'messages' => self::$sessions[$this->session_id]
        ];

        $args = $this->applyTemperatureParameter($args);

        if (!empty($this->mcp_servers)) {
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

        // handle stop reason
        // normally claude sends pause_turn as a stop reason
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
                prev_costs: $return['costs']
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
            if (
                __::x($response ?? null) &&
                __::x($response?->result ?? null) &&
                __::x($response?->result?->error ?? null) &&
                __::x($response?->result?->error?->message ?? null) &&
                is_string($response->result->error->message)
            ) {
                $return['response'] = $response->result->error->message;
            }
            if (
                __::x($response ?? null) &&
                __::x($response?->result ?? null) &&
                __::x($response?->result?->error ?? null) &&
                is_string($response->result->error)
            ) {
                $return['response'] = $response->result->error;
            }
            return $return;
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
        $supports_thinking = str_contains($model_name, 'sonnet') || str_contains($model_name, 'opus');

        if ($supports_thinking) {
            $args['thinking'] = ['type' => 'enabled', 'budget_tokens' => 10000];
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

class ai_gemini extends aihelper
{
    public $provider = 'Google';

    public $title = 'Gemini';

    public $name = 'gemini';

    protected $url = 'https://generativelanguage.googleapis.com/v1beta';

    public $support_mcp = false;

    public $support_stream = false;

    public $models = [
        [
            'name' => 'gemini-2.5-pro',
            'max_tokens' => 65536,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => true,
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'gemini-2.5-flash',
            'max_tokens' => 65536,
            'costs' => ['input' => 0.0000003, 'input_cached' => 0.00000003, 'output' => 0.0000025],
            'supports_temperature' => true,
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'gemini-2.5-flash-lite',
            'max_tokens' => 65536,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.00000001, 'output' => 0.0000004],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-2.0-flash',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.000000025, 'output' => 0.0000004],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-2.0-flash-lite',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000000075, 'input_cached' => 0.000000075, 'output' => 0.0000003],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-2.5-flash-image',
            'max_tokens' => 65536,
            'costs' => ['input' => 0.0000003, 'input_cached' => 0.00000003, 'output' => 0.0000025],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-pro-latest',
            'max_tokens' => 65536,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-flash-latest',
            'max_tokens' => 65536,
            'costs' => ['input' => 0.0000003, 'input_cached' => 0.00000003, 'output' => 0.0000025],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-flash-lite-latest',
            'max_tokens' => 65536,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.00000001, 'output' => 0.0000004],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemma-3-1b-it',
            'max_tokens' => 8192,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemma-3-4b-it',
            'max_tokens' => 8192,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemma-3-12b-it',
            'max_tokens' => 8192,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemma-3-27b-it',
            'max_tokens' => 8192,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemma-3n-e4b-it',
            'max_tokens' => 8192,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemma-3n-e2b-it',
            'max_tokens' => 8192,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
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
                            'gemini-2.0-flash-001',
                            'gemini-2.0-flash-lite-001',
                            'gemini-embedding-001',
                            'aqa',
                            'imagen-4.0-generate-001',
                            'imagen-4.0-ultra-generate-001',
                            'imagen-4.0-fast-generate-001',
                            'veo-2.0-generate-001',
                            'veo-3.0-generate-001',
                            'veo-3.0-fast-generate-001'
                        ])
                    ) {
                        continue;
                    }
                    $max_tokens = 8192;
                    if (!empty($models__value->outputTokenLimit)) {
                        $max_tokens = (int) $models__value->outputTokenLimit;
                    } else {
                        foreach ($this->models as $definedModel) {
                            if ($definedModel['name'] === $name) {
                                $max_tokens = $definedModel['max_tokens'];
                                break;
                            }
                        }
                    }
                    $models[] = ['name' => $name, 'max_tokens' => $max_tokens];
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
        float $prev_costs = 0.0
    ): array {
        $return = ['response' => null, 'success' => false, 'costs' => $prev_costs];

        if (__::nx($this->model) || __::nx($this->session_id) || __::nx($prompt)) {
            $return['response'] = 'data missing.';
            return $return;
        }

        $prompt = $this->trimPrompt($prompt);

        if ($add_prompt_to_session === true) {
            $this->appendPromptToSession($prompt, $files);
        }

        $args = [
            'contents' => self::$sessions[$this->session_id]
        ];
        $args = $this->applyTemperatureParameter($args, 'generationConfig');

        if (method_exists($this, 'modifyArgs')) {
            $args = $this->modifyArgs($args);
        }
        $this->log((int) round(strlen(json_encode($args)) / 3.5), 'ask with input token length');
        $this->log($args, 'ask');
        $response = $this->makeApiCall($args);
        $this->log($response?->result ?? null, 'response');
        $this->addCosts($response, $return);

        $output_text = $prev_output_text !== null ? $prev_output_text : '';
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
                    }
                }
            }
        }

        if (__::nx($output_text)) {
            $this->log($response, 'failed');
            if (
                __::x($response ?? null) &&
                __::x($response?->result ?? null) &&
                __::x($response?->result?->error ?? null) &&
                __::x($response?->result?->error?->message ?? null) &&
                is_string($response->result->error->message)
            ) {
                $return['response'] = $response->result->error->message;
            }
            return $return;
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
        return __::curl(
            url: $this->url . '/models/' . $this->model . ':generateContent?key=' . $this->api_key,
            data: $args,
            method: 'POST',
            headers: null,
            timeout: $this->timeout
        );
    }
}

/* compatible with the anthropic api */
class ai_grok extends ai_claude
{
    public $provider = 'xAI';

    public $title = 'Grok';

    public $name = 'grok';

    protected $url = 'https://api.x.ai/v1';

    public $support_mcp = false;

    public $support_stream = false;

    public $models = [
        [
            'name' => 'grok-4-1-fast-reasoning',
            'max_tokens' => 131072,
            'costs' => ['input' => 0.0000002, 'input_cached' => 0.0000002, 'output' => 0.0000005],
            'supports_temperature' => true,
            'default' => true,
            'test' => true
        ],
        [
            'name' => 'grok-4-1-fast-non-reasoning',
            'max_tokens' => 131072,
            'costs' => ['input' => 0.0000002, 'input_cached' => 0.0000002, 'output' => 0.0000005],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-4-fast-reasoning',
            'max_tokens' => 131072,
            'costs' => ['input' => 0.0000002, 'input_cached' => 0.0000002, 'output' => 0.0000005],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-4-fast-non-reasoning',
            'max_tokens' => 131072,
            'costs' => ['input' => 0.0000002, 'input_cached' => 0.0000002, 'output' => 0.0000005],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-code-fast-1',
            'max_tokens' => 131072,
            'costs' => ['input' => 0.0000002, 'input_cached' => 0.0000002, 'output' => 0.0000015],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-3',
            'max_tokens' => 131072,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.000003, 'output' => 0.000015],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-3-mini',
            'max_tokens' => 131072,
            'costs' => ['input' => 0.0000002, 'input_cached' => 0.0000002, 'output' => 0.0000005],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-4.20-0309-non-reasoning',
            'max_tokens' => 131072,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000002, 'output' => 0.000006],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-4.20-0309-reasoning',
            'max_tokens' => 131072,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000002, 'output' => 0.000006],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-4.20-multi-agent-0309',
            'max_tokens' => 131072,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.0000002, 'output' => 0.000006],
            'supports_temperature' => true,
            'default' => false,
            'test' => false
        ]
    ];
}

/* compatible with the anthropic api */
class ai_deepseek extends ai_claude
{
    public $provider = 'DeepSeek';

    public $title = 'DeepSeek';

    public $name = 'deepseek';

    protected $url = 'https://api.deepseek.com/anthropic';

    public $support_mcp = false;

    public $support_stream = false;

    public $models = [
        [
            'name' => 'deepseek-chat',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.00000028, 'input_cached' => 0.000000028, 'output' => 0.00000042],
            'supports_temperature' => true,
            'default' => true,
            'test' => true
        ],
        [
            'name' => 'deepseek-reasoner',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.00000028, 'input_cached' => 0.000000028, 'output' => 0.00000042],
            'supports_temperature' => true,
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
                    $max_tokens = 8192;
                    foreach ($this->models as $definedModel) {
                        if ($definedModel['name'] === $name) {
                            $max_tokens = $definedModel['max_tokens'];
                            break;
                        }
                    }
                    $models[] = ['name' => $name, 'max_tokens' => $max_tokens];
                }
            }
        }
        return $models;
    }
}

/* compatible with the openai api */
class ai_lmstudio extends ai_chatgpt
{
    public $provider = 'Element Labs';

    public $title = 'LM Studio';

    public $name = 'lmstudio';

    protected $url = 'http://localhost:1234/v1';

    public $support_mcp = true;

    public $support_stream = true;

    public $models = [];

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
                    $max_tokens = 32768;
                    if (!empty($models__value->max_context_length)) {
                        $max_tokens = min((int) $models__value->max_context_length, 65536);
                    }
                    $models[] = ['name' => $models__value->key, 'max_tokens' => $max_tokens];
                }
            }
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
        $model_name = strtolower($this->model ?? '');
        $uses_tools = !empty($args['tools']) && is_array($args['tools']);

        // --- detect profile ---

        $profile = 'default';
        if ($uses_tools) {
            $profile = 'agentic';
        } else {
            $prompt_text = '';
            foreach (array_reverse($args['input'] ?? []) as $item) {
                if (!is_array($item) || ($item['role'] ?? null) !== 'user') {
                    continue;
                }
                foreach (($item['content'] ?? []) as $part) {
                    if (is_array($part) && ($part['type'] ?? null) === 'input_text' && isset($part['text'])) {
                        $prompt_text .= ' ' . $part['text'];
                    }
                }
                break;
            }
            $prompt_text = mb_strtolower(trim($prompt_text));

            if ($prompt_text !== '') {
                $creative_keywords = [
                    'geschichte', 'kreativ', 'gedicht', 'erzähl', 'schreib', 'story',
                    'märchen', 'roman', 'szene', 'witz', 'witzig', 'lustig', 'ulkig', 'humor', 'komisch',
                ];
                $reasoning_keywords = [
                    'denke', 'überlege', 'analysiere', 'erkläre', 'warum',
                    'berechne', 'löse', 'beweise', 'vergleiche', 'schlussfolgere',
                ];
                $matches = fn(array $keywords) => array_reduce(
                    $keywords,
                    fn($carry, $kw) => $carry || str_contains($prompt_text, $kw),
                    false
                );
                if ($matches($creative_keywords)) {
                    $profile = 'creative';
                } elseif ($matches($reasoning_keywords) || preg_match('/\d+\s*[\*\+\-x\/]\s*\d+/', $prompt_text) === 1) {
                    $profile = 'reasoning';
                }
            }
        }

        // --- sampling parameters per model family ---

        if (str_contains($model_name, 'qwq')) {
            $args += ['top_p' => 0.95, 'top_k' => 40];
        } elseif (str_contains($model_name, 'qwen3.5')) {
            $args += [
                'top_p' => ($profile === 'reasoning' || $profile === 'creative') ? 0.95 : 0.8,
                'top_k' => 20,
                'presence_penalty' => ($profile === 'creative') ? 0.4 : 0.0,
                // lmstudio responses api ignores frequency_penalty; use repeat_penalty instead
                // (llmster maps repeat_penalty → llama.repeatPenalty; default is 1.1)
                'repeat_penalty' => ($profile === 'agentic') ? 1.0 : 1.1,
            ];
        } elseif (str_contains($model_name, 'qwen3')) {
            $args += ['top_p' => 0.8, 'top_k' => 20];
        } elseif (str_contains($model_name, 'gpt-oss') && $uses_tools) {
            $args += ['top_p' => 0.9, 'top_k' => 20];
        }

        // --- qwen3: suppress runaway thinking via empty <think> priming ---
        // qwen3 variants in lmstudio do not reliably follow /no_think;
        // the empty <think> priming trick is the only reliable way to prevent it

        if (str_contains($model_name, 'qwen3')) {
            $think_block = "<think>\n\n</think>\n\n";

            if (!empty($args['input']) && is_array($args['input'])) {
                $already_primed = false;
                foreach ($args['input'] as $item) {
                    if (!is_array($item) || ($item['role'] ?? null) !== 'assistant') {
                        continue;
                    }
                    foreach (($item['content'] ?? []) as $part) {
                        if (is_array($part) && ($part['text'] ?? '') === $think_block) {
                            $already_primed = true;
                            break 2;
                        }
                    }
                }
                if (!$already_primed) {
                    $args['input'][] = [
                        'role' => 'assistant',
                        'content' => [['type' => 'output_text', 'text' => $think_block]],
                    ];
                }
            }
            if (!empty($args['messages'])) {
                $args['messages'][] = ['role' => 'assistant', 'content' => $think_block];
            }
        }

        // --- output limits per profile ---

        if (str_contains($model_name, 'qwen3.5')) {
            if ($uses_tools) {
                $args += ['max_output_tokens' => 12000, 'parallel_tool_calls' => false, 'max_tool_calls' => 30];
            } elseif ($profile === 'creative') {
                $args += ['max_output_tokens' => 2500];
            } elseif ($profile === 'reasoning') {
                $args += ['max_output_tokens' => 4000];
            }
        }

        if (str_contains($model_name, 'glm')) {
            $args['max_output_tokens'] = 1500; // glm produces excessive thinking; hard cap
        }

        unset($args['reasoning'], $args['ttl']);

        return $args;
    }
}

class ai_test extends ai_claude
{
    public $provider = 'aihelper';

    public $title = 'Test';

    public $name = 'test';

    protected $url = null;

    public $support_mcp = false;

    public $support_stream = true;

    public $models = [
        [
            'name' => 'test-model-1',
            'max_tokens' => 8192,
            'costs' => ['input' => 0, 'input_cached' => 0, 'output' => 0],
            'supports_temperature' => true,
            'default' => true,
            'test' => true
        ]
    ];

    public function fetchModels(): array
    {
        return array_map(function ($model) {
            return ['name' => $model['name'], 'max_tokens' => $model['max_tokens']];
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
