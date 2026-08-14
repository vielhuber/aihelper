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
    public ?bool $is_harness = false;

    protected ?string $model = null;
    protected ?string $system_prompt = null;
    protected array $cli_skills = [];
    protected ?string $effort = null;
    protected ?float $temperature = null;
    protected ?int $timeout = null;
    protected ?string $api_key = null;
    protected ?string $workdir = null;
    protected ?string $ssh_host = null;
    protected ?string $ssh_user = null;
    protected ?int $ssh_port = null;
    protected ?string $ssh_key = null;
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
    protected int $stream_block_offset = 0;
    protected bool $stream_first_text_sent = false;
    protected bool $stream_text_emitted_since_tool = false;
    protected bool $stream_running = false;
    protected bool $stream_in_think = false;
    protected string $stream_think_tag_buf = '';
    protected string $stream_reasoning_buffer = '';
    protected bool $stream_tool_call_strip_in_block = false;
    protected string $stream_tool_call_strip_tag_buf = '';
    protected ?\Closure $stream_callback = null;
    protected array $transcript_states = [];
    protected array $transcript_labels = [];

    protected ?string $session_id = null;
    protected static array $sessions = [];

    protected ?bool $auto_compact = null;
    protected ?string $auto_compact_summary = null;
    protected ?string $auto_compact_cache = null;
    protected array $auto_compact_removed_messages = [];
    protected static ?array $artificial_analysis_models = null;
    protected static array $cli_usage_limits_cache = [];
    protected static array $cli_usage_reset_credits_cache = [];

    public static function create(
        string $provider,
        ?string $model = null,
        ?string $effort = null,
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
        ?bool $auto_compact = null,
        ?string $cli_workdir = null,
        ?string $cli_ssh_host = null,
        ?string $cli_ssh_user = null,
        ?int $cli_ssh_port = null,
        ?string $cli_ssh_key = null,
        ?string $system_prompt = null,
        ?array $cli_skills = null
    ): ?self {
        if ($provider === 'openai') {
            return new ai_openai(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'anthropic') {
            return new ai_anthropic(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'google') {
            return new ai_google(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'xai') {
            return new ai_xai(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'deepseek') {
            return new ai_deepseek(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'openrouter') {
            return new ai_openrouter(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'llamacpp') {
            return new ai_llamacpp(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'lmstudio') {
            return new ai_lmstudio(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'nvidia') {
            return new ai_nvidia(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'cliproxyapi') {
            return new ai_cliproxyapi(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'elevenlabs') {
            return new ai_elevenlabs(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'claudecode') {
            return new ai_claudecode(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                cli_workdir: $cli_workdir,
                cli_ssh_host: $cli_ssh_host,
                cli_ssh_user: $cli_ssh_user,
                cli_ssh_port: $cli_ssh_port,
                cli_ssh_key: $cli_ssh_key,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'codex') {
            return new ai_codex(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                cli_workdir: $cli_workdir,
                cli_ssh_host: $cli_ssh_host,
                cli_ssh_user: $cli_ssh_user,
                cli_ssh_port: $cli_ssh_port,
                cli_ssh_key: $cli_ssh_key,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'opencode') {
            return new ai_opencode(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                cli_workdir: $cli_workdir,
                cli_ssh_host: $cli_ssh_host,
                cli_ssh_user: $cli_ssh_user,
                cli_ssh_port: $cli_ssh_port,
                cli_ssh_key: $cli_ssh_key,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        if ($provider === 'test') {
            return new ai_test(
                model: $model,
                effort: $effort,
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
                auto_compact: $auto_compact,
                system_prompt: $system_prompt,
                cli_skills: $cli_skills
            );
        }
        return null;
    }

    public static function getProviders(): array
    {
        $data = [];
        foreach (
            [
                ai_anthropic::class,
                ai_google::class,
                ai_openai::class,
                ai_xai::class,
                ai_deepseek::class,
                ai_openrouter::class,
                ai_cliproxyapi::class,
                ai_llamacpp::class,
                ai_lmstudio::class,
                ai_nvidia::class,
                ai_elevenlabs::class,
                ai_claudecode::class,
                ai_codex::class,
                ai_opencode::class,
                ai_test::class
            ]
            as $providerClass
        ) {
            $providers__value = new $providerClass();
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

    public static function getMcpOnlineStatus(
        ?string $url = null,
        ?string $authorization_token = null,
        ?int $timeout = null
    ): bool {
        try {
            $timeout = $timeout ?? 30;
            // add trailing slash to avoid 307 redirect
            if (substr($url, -1) !== '/') {
                $url .= '/';
            }

            // use mcp ping endpoint
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
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
        ?string $effort = null,
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
        ?bool $auto_compact = null,
        ?string $cli_workdir = null,
        ?string $cli_ssh_host = null,
        ?string $cli_ssh_user = null,
        ?int $cli_ssh_port = null,
        ?string $cli_ssh_key = null,
        ?string $system_prompt = null,
        ?array $cli_skills = null
    ) {
        if ($cli_workdir !== null) {
            $this->workdir = $cli_workdir;
        }
        if ($cli_ssh_host !== null) {
            $this->ssh_host = $cli_ssh_host;
        }
        if ($cli_ssh_user !== null) {
            $this->ssh_user = $cli_ssh_user;
        }
        if ($cli_ssh_port !== null) {
            $this->ssh_port = $cli_ssh_port;
        }
        if ($cli_ssh_key !== null) {
            $this->ssh_key = $cli_ssh_key;
        }
        if ($cli_skills !== null) {
            $this->cli_skills = $cli_skills;
        }
        if ($temperature === null) {
            $temperature = 1.0;
        }
        if ($timeout === null) {
            $timeout = 300;
        }
        $this->timeout = $timeout;
        if ($log !== null) {
            $this->log = $log;
        }
        if ($url !== null) {
            $this->url = $url;
        }
        if ($effort !== null && in_array($effort, ['none', 'minimal', 'low', 'medium', 'high', 'xhigh', 'max'], true)) {
            $this->effort = $effort;
        }
        if ($enable_thinking !== null) {
            $this->enable_thinking = $enable_thinking;
        }
        if ($api_key !== null) {
            $this->api_key = $api_key;
        }
        $this->models = $this->fetchModels();
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
        // last: for a non-harness provider this writes into the session, which
        // only exists once the history above has been restored
        if ($system_prompt !== null) {
            $this->setSystemPrompt($system_prompt);
        }
    }

    protected function getCliUsageTool(): ?string
    {
        $model = strtolower((string) $this->model);
        $tool = null;
        $owned_by = null;
        if ($this->name === 'cliproxyapi') {
            foreach ($this->models as $available_model) {
                if (($available_model['name'] ?? null) !== $this->model) {
                    continue;
                }
                $owned_by = strtolower((string) ($available_model['owned_by'] ?? ''));
                break;
            }
        }
        if ($owned_by === 'antigravity') {
            $tool = 'antigravity';
        }
        if (
            $owned_by === 'anthropic' ||
            ($owned_by === null &&
                (str_contains($model, 'claude') || in_array($this->name, ['anthropic', 'claudecode'], true)))
        ) {
            $tool = 'claude';
        }
        if (
            $owned_by === 'openai' ||
            ($owned_by === null &&
                (str_contains($model, 'codex') ||
                    $this->name === 'codex' ||
                    ($this->name === 'cliproxyapi' && str_contains($model, 'gpt'))))
        ) {
            $tool = 'codex';
        }
        if (
            $owned_by === null &&
            (str_contains($model, 'antigravity') ||
                str_contains($model, 'agy') ||
                ($this->name === 'cliproxyapi' && str_contains($model, 'gemini')))
        ) {
            $tool = 'antigravity';
        }

        return $tool;
    }

    // credentials belong to the machine the cli runs on, not to the caller
    protected function readCliAuthFile(string $path): ?string
    {
        return is_file($path) ? (string) file_get_contents($path) : null;
    }

    protected function getCodexAuthentication(): ?array
    {
        $auth_files =
            $this->name === 'cliproxyapi'
                ? (glob('/host/data/server/cliproxyapi/auth/codex*.json') ?: [])
                : ['/root/.codex/auth.json'];
        foreach ($auth_files as $auth_file) {
            $auth_content = $this->readCliAuthFile($auth_file);
            if ($auth_content === null) {
                continue;
            }
            $auth = json_decode($auth_content, true);
            if (!is_array($auth) || $this->isCliAuthenticationExpired($auth)) {
                continue;
            }
            $access_token = $auth['tokens']['access_token'] ?? ($auth['access_token'] ?? null);
            $account_id = $auth['tokens']['account_id'] ?? ($auth['account_id'] ?? null);
            if (($access_token ?? '') !== '' && ($account_id ?? '') !== '') {
                return ['access_token' => $access_token, 'account_id' => $account_id];
            }
        }

        return null;
    }

    protected function getCliUsageCacheKey(string $tool): string
    {
        return $tool . ($this->name === 'cliproxyapi' ? '-cliproxyapi' : '-native');
    }

    protected function isCliAuthenticationExpired(array $auth): bool
    {
        $expiresAt = $auth['expired'] ?? ($auth['expires_at'] ?? null);
        if (is_numeric($auth['claudeAiOauth']['expiresAt'] ?? null)) {
            $expiresAt = (int) floor((float) $auth['claudeAiOauth']['expiresAt'] / 1000);
        }
        if ($expiresAt === null || $expiresAt === '') {
            return false;
        }
        if (is_numeric($expiresAt)) {
            return (int) $expiresAt <= time();
        }
        $expiresAtTimestamp = strtotime((string) $expiresAt);
        return $expiresAtTimestamp !== false && $expiresAtTimestamp <= time();
    }

    public function getCliUsageResetCredits(): ?array
    {
        $tool = $this->getCliUsageTool();
        if ($tool !== 'codex') {
            return null;
        }
        $cacheKey = $this->getCliUsageCacheKey($tool);
        if (
            isset(self::$cli_usage_reset_credits_cache[$cacheKey]) &&
            time() - self::$cli_usage_reset_credits_cache[$cacheKey]['time'] < 60
        ) {
            return self::$cli_usage_reset_credits_cache[$cacheKey]['credits'];
        }
        $cache_file =
            sys_get_temp_dir() .
            '/aihelper-cliusage-reset-credits-' .
            $cacheKey .
            '-' .
            (function_exists('posix_geteuid') ? posix_geteuid() : getmyuid()) .
            '.json';
        $cached = is_file($cache_file) ? json_decode((string) file_get_contents($cache_file), true) : null;
        $cached = is_array($cached) ? $cached : null;
        $last_good = $cached !== null && is_array($cached['credits'] ?? null) ? $cached['credits'] : null;
        if ($cached !== null && time() - (int) ($cached['time'] ?? 0) < 60) {
            self::$cli_usage_reset_credits_cache[$cacheKey] = ['time' => time(), 'credits' => $last_good];
            return $last_good;
        }
        $auth = $this->getCodexAuthentication();
        if ($auth === null) {
            return $last_good;
        }
        $response = __::curl(
            url: 'https://chatgpt.com/backend-api/wham/rate-limit-reset-credits',
            method: 'GET',
            headers: [
                'Authorization' => 'Bearer ' . $auth['access_token'],
                'ChatGPT-Account-Id' => $auth['account_id'],
                'User-Agent' => 'codex-cli',
                'Accept' => 'application/json'
            ],
            timeout: 15
        );
        $payload = $response?->result ?? null;
        if (!is_object($payload) || !is_numeric($payload->available_count ?? null)) {
            file_put_contents($cache_file, json_encode(['time' => time(), 'credits' => $last_good]));
            self::$cli_usage_reset_credits_cache[$cacheKey] = ['time' => time(), 'credits' => $last_good];
            return $last_good;
        }
        $credits = [];
        foreach ($payload->credits ?? [] as $credit) {
            if (!is_object($credit) || ($credit->status ?? null) !== 'available') {
                continue;
            }
            $credits[] = [
                'title' => isset($credit->title) ? (string) $credit->title : null,
                'expires_at' => isset($credit->expires_at) ? (string) $credit->expires_at : null
            ];
        }

        $result = ['available_count' => max(0, (int) $payload->available_count), 'credits' => $credits];
        file_put_contents($cache_file, json_encode(['time' => time(), 'credits' => $result]));
        self::$cli_usage_reset_credits_cache[$cacheKey] = ['time' => time(), 'credits' => $result];
        return $result;
    }

    public function triggerCliUsageReset(): ?array
    {
        $tool = $this->getCliUsageTool();
        if ($tool !== 'codex') {
            return null;
        }
        $cacheKey = $this->getCliUsageCacheKey($tool);
        $auth = $this->getCodexAuthentication();
        if ($auth === null) {
            return null;
        }
        $uuid = random_bytes(16);
        $uuid[6] = chr((ord($uuid[6]) & 0x0f) | 0x40);
        $uuid[8] = chr((ord($uuid[8]) & 0x3f) | 0x80);
        $redeem_request_id = vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($uuid), 4));
        $data = ['redeem_request_id' => $redeem_request_id];
        $response = __::curl(
            url: 'https://chatgpt.com/backend-api/wham/rate-limit-reset-credits/consume',
            data: $data,
            method: 'POST',
            headers: [
                'Authorization' => 'Bearer ' . $auth['access_token'],
                'ChatGPT-Account-Id' => $auth['account_id'],
                'User-Agent' => 'codex-cli',
                'Accept' => 'application/json'
            ],
            timeout: 15
        );
        $payload = $response?->result ?? null;
        if (!is_object($payload) || !is_string($payload->code ?? null)) {
            return null;
        }
        if ($payload->code === 'reset') {
            unset(self::$cli_usage_limits_cache[$cacheKey]);
            unset(self::$cli_usage_reset_credits_cache[$cacheKey]);
            $cache_file =
                sys_get_temp_dir() .
                '/aihelper-cliusage-' .
                $cacheKey .
                '-' .
                (function_exists('posix_geteuid') ? posix_geteuid() : getmyuid()) .
                '.json';
            if (is_file($cache_file)) {
                unlink($cache_file);
            }
            $reset_credits_cache_file =
                sys_get_temp_dir() .
                '/aihelper-cliusage-reset-credits-' .
                $cacheKey .
                '-' .
                (function_exists('posix_geteuid') ? posix_geteuid() : getmyuid()) .
                '.json';
            if (is_file($reset_credits_cache_file)) {
                unlink($reset_credits_cache_file);
            }
        }

        return [
            'success' => $payload->code === 'reset',
            'status' => $payload->code,
            'windows_reset' => is_numeric($payload->windows_reset ?? null) ? (int) $payload->windows_reset : 0
        ];
    }

    public function getCliUsageLimits(): ?array
    {
        $tool = $this->getCliUsageTool();
        if ($tool === null) {
            return null;
        }
        $cacheKey = $this->getCliUsageCacheKey($tool);
        if (
            isset(self::$cli_usage_limits_cache[$cacheKey]) &&
            time() - self::$cli_usage_limits_cache[$cacheKey]['time'] < 60
        ) {
            return self::$cli_usage_limits_cache[$cacheKey]['limits'];
        }
        // these provider endpoints rate-limit (429) when polled too often, and each web request starts
        // with an empty in-memory cache — so persist the last good result to disk. serve it directly
        // while fresh (skipping the endpoint entirely, which is what actually avoids the rate limit),
        // and keep serving it on any fetch failure until a fresh success replaces it.
        $cache_file =
            sys_get_temp_dir() .
            '/aihelper-cliusage-' .
            $cacheKey .
            '-' .
            (function_exists('posix_geteuid') ? posix_geteuid() : getmyuid()) .
            '.json';
        $raw = is_file($cache_file) ? json_decode((string) file_get_contents($cache_file), true) : null;
        $raw = is_array($raw) ? $raw : null;
        $last_good = $raw !== null && !empty($raw['limits']) ? $raw['limits'] : null;
        // a good result stays fresh for 2 min; a failed attempt only backs off 20 s so the panel
        // can recover quickly when the endpoint is reachable again. once the cache is older than
        // the stale cap, ignore the backoff and refetch on every call — this bounds how long a
        // stuck (429) window can keep stale data on screen.
        $ttl = $last_good !== null ? 120 : 90;
        $last_success = (int) ($raw['last_success'] ?? 0);
        $last_attempt = (int) ($raw['last_attempt'] ?? 0);
        $backoff = 20;
        $stale_cap = 300;
        if ($raw !== null && $last_success > 0) {
            $in_fresh = time() - $last_success < $ttl;
            $backed_off = time() - $last_attempt < $backoff;
            $stale = time() - $last_success >= $stale_cap;
            if ($in_fresh || ($backed_off && !$stale)) {
                self::$cli_usage_limits_cache[$cacheKey] = ['time' => time(), 'limits' => $last_good];
                return $last_good;
            }
        }
        $finish = function (array $limits) use ($cacheKey, $cache_file, $last_good, $last_success): ?array {
            $success = !empty($limits);
            $store = $success ? $limits : $last_good;
            file_put_contents(
                $cache_file,
                json_encode([
                    'time' => time(),
                    'last_attempt' => time(),
                    'last_success' => $success ? time() : $last_success,
                    'limits' => $store
                ])
            );
            self::$cli_usage_limits_cache[$cacheKey] = ['time' => time(), 'limits' => $store];
            return $store;
        };
        if ($tool === 'codex') {
            $auth = $this->getCodexAuthentication();
            if ($auth === null) {
                return $finish([]);
            }
            $response = __::curl(
                url: 'https://chatgpt.com/backend-api/wham/usage',
                method: 'GET',
                headers: [
                    'Authorization' => 'Bearer ' . $auth['access_token'],
                    'ChatGPT-Account-Id' => $auth['account_id'],
                    'User-Agent' => 'codex-cli',
                    'Accept' => 'application/json'
                ],
                timeout: 15
            );
            $payload = $response?->result ?? null;
            $rate_limits = [['rate_limit' => $payload?->rate_limit ?? null, 'scope' => null]];
            if (is_object($payload?->code_review_rate_limit ?? null)) {
                $rate_limits[] = ['rate_limit' => $payload->code_review_rate_limit, 'scope' => 'Code review'];
            }
            foreach ($payload?->additional_rate_limits ?? [] as $additional_rate_limit) {
                if (!is_object($additional_rate_limit) || !is_object($additional_rate_limit->rate_limit ?? null)) {
                    continue;
                }
                $rate_limits[] = [
                    'rate_limit' => $additional_rate_limit->rate_limit,
                    'scope' => trim((string) ($additional_rate_limit->limit_name ?? '')) ?: null
                ];
            }
            $limits = [];
            foreach ($rate_limits as $rate_limit) {
                if (!is_object($rate_limit['rate_limit'])) {
                    continue;
                }
                foreach (
                    [
                        ['window' => $rate_limit['rate_limit']->primary_window ?? null, 'fallback_type' => '5-hour'],
                        ['window' => $rate_limit['rate_limit']->secondary_window ?? null, 'fallback_type' => 'weekly']
                    ]
                    as $window_data
                ) {
                    $window = $window_data['window'];
                    if (!is_object($window) || !is_numeric($window->used_percent ?? null)) {
                        continue;
                    }
                    $type = $window_data['fallback_type'];
                    if (is_numeric($window->limit_window_seconds ?? null)) {
                        $type = match ((int) $window->limit_window_seconds) {
                            5 * 60 * 60 => '5-hour',
                            24 * 60 * 60 => 'daily',
                            7 * 24 * 60 * 60 => 'weekly',
                            30 * 24 * 60 * 60 => 'monthly',
                            default => $type
                        };
                    }
                    if ($type === null) {
                        continue;
                    }
                    $limits[] = [
                        'type' => $type,
                        'scope' => $rate_limit['scope'],
                        'percent used' => (int) round(max(0, min(100, (float) $window->used_percent))),
                        'resets_at' => is_numeric($window->reset_at ?? null)
                            ? date(\DateTimeInterface::ATOM, (int) $window->reset_at)
                            : null
                    ];
                }
            }
            return $finish($limits);
        }
        if ($tool === 'antigravity') {
            $auth_files = array_values(
                array_unique(
                    array_merge(
                        ['/root/.gemini/antigravity-cli/antigravity-oauth-token'],
                        glob('/root/.cli-proxy-api/antigravity*.json') ?: [],
                        glob('/host/data/server/cliproxyapi/auth/antigravity*.json') ?: []
                    )
                )
            );
            $access_token = null;
            $project = null;
            foreach ($auth_files as $auth_file) {
                if (!is_file($auth_file)) {
                    continue;
                }
                $auth = json_decode((string) file_get_contents($auth_file), true);
                if (!is_array($auth)) {
                    continue;
                }
                $access_token =
                    $auth['token']['access_token'] ??
                    ($auth['tokens']['access_token'] ?? ($auth['access_token'] ?? null));
                $project = $auth['project_id'] ?? null;
                if (($access_token ?? '') !== '') {
                    break;
                }
            }
            if (($access_token ?? '') === '') {
                return $finish([]);
            }
            $headers = [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
                'User-Agent' => 'antigravity/cli/1.0.14 (aidev_client; os_type=linux; arch=amd64)',
                'Accept' => 'application/json'
            ];
            if (($project ?? '') === '') {
                $project_response = __::curl(
                    url: 'https://daily-cloudcode-pa.googleapis.com/v1internal:loadCodeAssist',
                    data: json_encode(['metadata' => ['ideType' => 'ANTIGRAVITY']]),
                    method: 'POST',
                    headers: $headers,
                    timeout: 15
                );
                $project = $project_response?->result?->cloudaicompanionProject ?? null;
            }
            if (($project ?? '') === '') {
                return $finish([]);
            }
            $response = __::curl(
                url: 'https://daily-cloudcode-pa.googleapis.com/v1internal:retrieveUserQuotaSummary',
                data: json_encode(['project' => $project]),
                method: 'POST',
                headers: $headers,
                timeout: 15
            );
            $groups = $response?->result?->groups ?? null;
            if (!is_array($groups)) {
                return $finish([]);
            }
            $limits = [];
            foreach ($groups as $group) {
                if (!is_object($group) || !is_array($group->buckets ?? null)) {
                    continue;
                }
                if (stripos((string) ($group->displayName ?? ''), 'Claude and GPT') !== false) {
                    continue;
                }
                foreach ($group->buckets as $bucket) {
                    if (!is_object($bucket) || !is_numeric($bucket->remainingFraction ?? null)) {
                        continue;
                    }
                    $window = strtolower(trim((string) ($bucket->window ?? '')));
                    $type = match (true) {
                        in_array($window, ['session', '5h', '5-hour', 'five_hour'], true),
                        str_contains($window, '5-hour'),
                        str_contains($window, '5 hour') => '5-hour',
                        str_contains($window, 'weekly'),
                        str_contains($window, 'seven_day'),
                        str_contains($window, '7-day'),
                        str_contains($window, '7 day') => 'weekly',
                        str_contains($window, 'monthly'), str_contains($window, 'month') => 'monthly',
                        str_contains($window, 'daily'), str_contains($window, 'day') => 'daily',
                        default => null
                    };
                    if ($type === null) {
                        continue;
                    }
                    $resets_at = null;
                    if (($bucket->resetTime ?? null) !== null) {
                        try {
                            $resets_at = (new \DateTimeImmutable((string) $bucket->resetTime))->format(
                                \DateTimeInterface::ATOM
                            );
                        } catch (\Exception) {
                        }
                    }
                    $limits[] = [
                        'type' => $type,
                        'scope' => null,
                        'percent used' => (int) round(100 - max(0, min(1, (float) $bucket->remainingFraction)) * 100),
                        'resets_at' => $resets_at
                    ];
                }
            }
            return $finish($limits);
        }

        $auth_files =
            $this->name === 'cliproxyapi'
                ? (glob('/host/data/server/cliproxyapi/auth/claude*.json') ?: [])
                : ['/root/.claude/.credentials.json'];
        $access_token = null;
        foreach ($auth_files as $auth_file) {
            $auth_content = $this->readCliAuthFile($auth_file);
            if ($auth_content === null) {
                continue;
            }
            $auth = json_decode($auth_content, true);
            if (!is_array($auth) || $this->isCliAuthenticationExpired($auth)) {
                continue;
            }
            $access_token = $auth['claudeAiOauth']['accessToken'] ?? ($auth['access_token'] ?? null);
            if (($access_token ?? '') !== '') {
                break;
            }
        }
        // an expired token is just another failed attempt — the cli refreshes it on its next run,
        // so keep serving the last good result instead of dropping the panel to "no data"
        if (($access_token ?? '') === '') {
            return $finish([]);
        }
        $format_reset = function ($value): ?string {
            if (($value ?? null) === null) {
                return null;
            }
            try {
                return (new \DateTimeImmutable((string) $value))->format(\DateTimeInterface::ATOM);
            } catch (\Exception) {
                return null;
            }
        };
        // one attempt only — the endpoint rate-limits (429) and immediate retries just make it worse;
        // resilience now comes from the persisted cache ($finish serves the last good result on failure)
        $limits = [];
        $response = __::curl(
            url: 'https://api.anthropic.com/api/oauth/usage',
            method: 'GET',
            headers: [
                'Authorization' => 'Bearer ' . $access_token,
                'User-Agent' => 'claude-cli',
                'Accept' => 'application/json'
            ],
            timeout: 15
        );
        $payload = $response?->result ?? null;
        if (is_object($payload)) {
            // Current responses expose general windows twice and scoped limits only in `limits`.
            $used_limits = [];
            foreach (get_object_vars($payload) as $window_name => $window) {
                if (!preg_match('/^(five_hour|seven_day)(?:_(.+))?$/', $window_name, $window_matches)) {
                    continue;
                }
                if (!is_object($window) || !is_numeric($window->utilization ?? null)) {
                    continue;
                }
                $type = $window_matches[1] === 'five_hour' ? '5-hour' : 'weekly';
                $scope = isset($window_matches[2])
                    ? ucwords(str_replace('_', ' ', (string) $window_matches[2]))
                    : null;
                $limits[] = [
                    'type' => $type,
                    'scope' => $scope,
                    'percent used' => (int) round(max(0, min(100, (float) $window->utilization))),
                    'resets_at' => $format_reset($window->resets_at ?? null)
                ];
                $used_limits[$type . '|' . strtolower((string) $scope)] = true;
            }
            if (is_array($payload->limits ?? null)) {
                foreach ($payload->limits as $limit) {
                    if (!is_object($limit) || !is_numeric($limit->percent ?? null)) {
                        continue;
                    }
                    $group = (string) ($limit->group ?? '');
                    $kind = (string) ($limit->kind ?? '');
                    $type = in_array($group, ['daily', 'weekly', 'monthly'], true) ? $group : null;
                    if ($group === 'session') {
                        $type = '5-hour';
                    }
                    if ($type === null && str_starts_with($kind, 'weekly')) {
                        $type = 'weekly';
                    }
                    if ($type === null) {
                        continue;
                    }
                    $scope = null;
                    if (is_object($limit->scope ?? null)) {
                        if (is_object($limit->scope->model ?? null)) {
                            $scope = trim(
                                (string) ($limit->scope->model->display_name ?? ($limit->scope->model->id ?? ''))
                            );
                        }
                        if (($scope ?? '') === '' && isset($limit->scope->surface)) {
                            $scope = trim((string) $limit->scope->surface);
                        }
                    }
                    $scope = ($scope ?? '') !== '' ? $scope : null;
                    $limit_key = $type . '|' . strtolower((string) $scope);
                    if (isset($used_limits[$limit_key])) {
                        continue;
                    }
                    $limits[] = [
                        'type' => $type,
                        'scope' => $scope,
                        'percent used' => (int) round(max(0, min(100, (float) $limit->percent))),
                        'resets_at' => $format_reset($limit->resets_at ?? null)
                    ];
                    $used_limits[$limit_key] = true;
                }
            }
        }
        return $finish($limits);
    }

    protected static function getOpenCodeDatabasePath(): ?string
    {
        $dataHome = trim((string) getenv('XDG_DATA_HOME'));
        $home = trim((string) getenv('HOME'));
        $candidates = [];
        if ($dataHome !== '') {
            $candidates[] = rtrim($dataHome, '/') . '/opencode/opencode.db';
        }
        if ($home !== '') {
            $candidates[] = rtrim($home, '/') . '/.local/share/opencode/opencode.db';
        }
        $candidates[] = '/root/.local/share/opencode/opencode.db';

        foreach (array_unique($candidates) as $candidate) {
            if (!is_file($candidate)) {
                continue;
            }
            return realpath($candidate) ?: $candidate;
        }
        return null;
    }

    protected function parseCliUsageLimits(string $tool, string $output): array
    {
        $output = preg_replace('/\x1B(?:[@-Z\\\\-_]|\[[0-?]*[ -\/]*[@-~])/', '', $output) ?? $output;
        $output = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', str_replace("\r", "\n", $output)) ?? $output;
        $lines = [];
        foreach (explode("\n", $output) as $line) {
            $line = trim(preg_replace('/\s+/', ' ', $line) ?? $line);
            if ($line !== '') {
                $lines[] = $line;
            }
        }

        $parsePercent = function (string $text): int|float|null {
            if (!preg_match('/(\d+(?:\.\d+)?)%\s*(used|left)\b/i', $text, $matches)) {
                return null;
            }
            $percent = (float) $matches[1];
            if (strtolower($matches[2]) === 'left') {
                $percent = 100 - $percent;
            }
            $percent = max(0, min(100, $percent));
            return (float) (int) $percent === $percent ? (int) $percent : $percent;
        };
        $parseReset = function (string $text): ?string {
            $value = null;
            if (preg_match('/\((?:re)?sets?\s+([^)]+)\)/i', $text, $matches)) {
                $value = $matches[1];
            }
            if ($value === null) {
                foreach (explode("\n", $text) as $line) {
                    if (preg_match('/^(?:re)?sets?\s+(.+)$/i', trim($line), $matches)) {
                        $value = $matches[1];
                        break;
                    }
                }
            }
            if ($value === null) {
                return null;
            }

            $value = trim(preg_replace('/\s+/', ' ', $value) ?? $value);
            $timezone = new \DateTimeZone(date_default_timezone_get());
            if (preg_match('/\(([^)]+)\)/', $value, $matches)) {
                try {
                    $timezone = new \DateTimeZone($matches[1]);
                } catch (\Exception) {
                }
                $value = trim(str_replace($matches[0], '', $value));
            }
            $now = new \DateTimeImmutable('now', $timezone);
            $dateValue = null;
            $timeOnly = false;
            if (preg_match('/^(\d{1,2}:\d{2}\s*(?:am|pm)?)$/i', $value, $matches)) {
                $dateValue = $now->format('Y-m-d') . ' ' . $matches[1];
                $timeOnly = true;
            }
            if ($dateValue === null && preg_match('/^(\d{1,2}:\d{2}\s*(?:am|pm)?)\s+on\s+(.+)$/i', $value, $matches)) {
                $dateValue = $matches[2] . ' ' . $now->format('Y') . ' ' . $matches[1];
            }
            if ($dateValue === null && preg_match('/^(.+?),?\s+(\d{1,2}:\d{2}\s*(?:am|pm)?)$/i', $value, $matches)) {
                $dateValue = $matches[1] . ' ' . $now->format('Y') . ' ' . $matches[2];
            }
            if ($dateValue === null) {
                return null;
            }

            try {
                $date = new \DateTimeImmutable($dateValue, $timezone);
            } catch (\Exception) {
                return null;
            }
            if ($timeOnly && $date <= $now) {
                $date = $date->modify('+1 day');
            }
            if (!$timeOnly && $date < $now) {
                $date = $date->modify('+1 year');
            }
            return $date->format(\DateTimeInterface::ATOM);
        };

        $limits = [];
        $usedTypes = [];
        foreach ($lines as $lineKey => $line) {
            $type = null;
            if ($tool === 'codex' && preg_match('/\b5h\s+limit\b/i', $line)) {
                $type = '5-hour';
            }
            if ($tool === 'claude' && preg_match('/\bcurrent\s+session\b/i', $line)) {
                $type = '5-hour';
            }
            if (preg_match($tool === 'codex' ? '/\bweekly\s+limit\b/i' : '/\bcurrent\s+week\b/i', $line)) {
                $type = 'weekly';
            }
            if (preg_match($tool === 'codex' ? '/\bmonthly\s+limit\b/i' : '/\bcurrent\s+month\b/i', $line)) {
                $type = 'monthly';
            }
            if ($type === null || isset($usedTypes[$type])) {
                continue;
            }

            $block = [$line];
            if ($tool === 'claude') {
                $block = [];
                for ($i = $lineKey; $i < count($lines); $i++) {
                    if ($i > $lineKey && preg_match('/\bcurrent\s+(session|week|month)\b/i', $lines[$i])) {
                        break;
                    }
                    $block[] = $lines[$i];
                }
            }
            $text = implode("\n", $block);
            $percent = $parsePercent(str_replace("\n", ' ', $text));
            if ($percent === null) {
                continue;
            }
            $limits[] = ['type' => $type, 'scope' => null, 'percent used' => $percent, 'resets_at' => $parseReset($text)];
            $usedTypes[$type] = true;
        }
        return $limits;
    }

    public static function getCliApiRequests(
        ?int $limit = null,
        ?string $date_from = null,
        ?string $date_until = null,
        bool $include_body = false,
        bool $group_by = false
    ): array {
        $log_files = [];
        foreach (['/root/.cli-proxy-api/logs', '/host/data/server/cliproxyapi/logs'] as $dir) {
            $log_files = array_merge($log_files, glob($dir . '/*.log') ?: []);
        }
        // the proxy rotates logs away while they are being scanned, so a globbed file can already be
        // gone when it is stat'ed or read — stat each one once here and drop whatever vanished, which
        // also spares the loop below a second stat per file
        $log_times = [];
        foreach (array_unique($log_files) as $log_file) {
            $log_time = @filemtime($log_file);
            if ($log_time !== false) {
                $log_times[$log_file] = $log_time;
            }
        }
        $log_files = array_keys($log_times);
        usort($log_files, fn($a, $b) => $log_times[$b] <=> $log_times[$a]);
        $date_from_time = $date_from !== null ? strtotime($date_from) : false;
        $date_until_time = $date_until !== null ? strtotime($date_until) : false;

        $parse_pairs = function (array $lines): array {
            $pairs = [];
            foreach ($lines as $line) {
                if (preg_match('/^([A-Za-z0-9\-_ ]+?):\s?(.*)$/', $line, $matches)) {
                    $pairs[$matches[1]] = $matches[2];
                }
            }
            return $pairs;
        };
        $parse_block = function (array $lines) use ($parse_pairs): array {
            $meta_lines = [];
            $header_lines = [];
            $body_lines = [];
            $state = 'meta';
            $has_markers = false;
            foreach ($lines as $line) {
                if (trim($line) === 'Headers:') {
                    $state = 'headers';
                    $has_markers = true;
                    continue;
                }
                if (trim($line) === 'Body:') {
                    $state = 'body';
                    $has_markers = true;
                    continue;
                }
                if ($state === 'meta') {
                    $meta_lines[] = $line;
                }
                if ($state === 'headers') {
                    $header_lines[] = $line;
                }
                if ($state === 'body') {
                    $body_lines[] = $line;
                }
            }
            if (!$has_markers) {
                // final RESPONSE section: status/header pairs until first blank line, body afterwards
                $header_lines = [];
                $body_lines = [];
                $in_body = false;
                foreach ($meta_lines as $line) {
                    if (!$in_body && trim($line) === '' && !empty($header_lines)) {
                        $in_body = true;
                        continue;
                    }
                    if ($in_body) {
                        $body_lines[] = $line;
                        continue;
                    }
                    if (trim($line) !== '') {
                        $header_lines[] = $line;
                    }
                }
                $meta_lines = $header_lines;
            }
            $meta = $parse_pairs($meta_lines);
            $headers = $has_markers ? $parse_pairs($header_lines) : $meta;
            unset($headers['Status'], $headers['Timestamp']);
            return [
                'time' => $meta['Timestamp'] ?? null,
                'status' => is_numeric($meta['Status'] ?? null) ? (int) $meta['Status'] : null,
                'meta' => $meta,
                'headers' => $headers,
                'body' => trim(implode("\n", $body_lines))
            ];
        };
        $to_time = function (?string $value): ?float {
            if (($value ?? '') === '') {
                return null;
            }
            try {
                return (float) (new \DateTimeImmutable($value))->format('U.u');
            } catch (\Exception) {
                return null;
            }
        };

        $merge_usage = function (array $target, array $fragment) use (&$merge_usage): array {
            foreach ($fragment as $usage_key => $usage_value) {
                if (is_array($usage_value)) {
                    $usage_value = $merge_usage(
                        is_array($target[$usage_key] ?? null) ? $target[$usage_key] : [],
                        $usage_value
                    );
                }
                if (is_numeric($usage_value) && is_numeric($target[$usage_key] ?? null)) {
                    $usage_value = max($target[$usage_key], $usage_value);
                }
                $target[$usage_key] = $usage_value;
            }
            return $target;
        };

        // stable grouping key = source + model + normalized prefix of the last human prompt in the body
        $prompt_key = function ($body): string {
            if (!is_array($body)) {
                return '';
            }
            $text = '';
            foreach (array_reverse($body['messages'] ?? []) as $message) {
                if (($message['role'] ?? '') !== 'user') {
                    continue;
                }
                $content = $message['content'] ?? '';
                $part_text = '';
                if (is_string($content)) {
                    $part_text = $content;
                } elseif (is_array($content)) {
                    foreach ($content as $part) {
                        if (is_string($part)) {
                            $part_text .= ' ' . $part;
                        }
                        if (is_array($part) && ($part['type'] ?? '') === 'text') {
                            $part_text .= ' ' . ($part['text'] ?? '');
                        }
                    }
                }
                $part_text = trim(preg_replace('/\s+/', ' ', $part_text) ?? $part_text);
                if ($part_text !== '') {
                    $text = $part_text;
                    break;
                }
            }
            return mb_strtolower(mb_substr($text, 0, 64));
        };
        // derive the project from the working directory: walk up to the nearest ancestor that carries
        // a vcs/package marker (that is the real project root, so /var/www/foo/src → "foo"). for cwds
        // that no longer exist on disk or have no marker, fall back to the first segment beneath a
        // known web root, then to the directory name. cached per cwd (the filesystem walk stats disk).
        $project_root_cache = [];
        $project_from_cwd = function (?string $cwd) use (&$project_root_cache): ?string {
            if (($cwd ?? '') === '') {
                return null;
            }
            $cwd = rtrim(str_replace('\\', '/', $cwd), '/');
            if (array_key_exists($cwd, $project_root_cache)) {
                return $project_root_cache[$cwd];
            }
            // .git marks the real repo root; composer.json/package.json also live in subdirs (themes,
            // frontend build dirs) so they are only a fallback when no .git exists anywhere up the tree
            $git = null;
            $package = null;
            $dir = $cwd;
            for ($depth = 0; $depth < 12 && $dir !== '' && $dir !== '/' && $dir !== '.'; $depth++) {
                if (file_exists($dir . '/.git')) {
                    $git = $dir;
                    break;
                }
                if ($package === null && (file_exists($dir . '/composer.json') || file_exists($dir . '/package.json'))) {
                    $package = $dir;
                }
                $dir = dirname($dir);
            }
            $result = ($git ?? $package) !== null ? basename($git ?? $package) : null;
            if ($result === null) {
                foreach (['/var/www/html', '/var/www', '/srv/www', '/usr/share/nginx/html'] as $base) {
                    if (!str_starts_with($cwd . '/', $base . '/')) {
                        continue;
                    }
                    $rest = ltrim(substr($cwd, strlen($base)), '/');
                    $result = $rest !== '' ? explode('/', $rest)[0] : basename($cwd);
                    break;
                }
            }
            $result ??= basename($cwd);
            $project_root_cache[$cwd] = $result;
            return $result;
        };
        // add numeric usage fields together (for group_by=true), unlike $merge_usage which takes the max
        $sum_usage = function (array $target, array $fragment) use (&$sum_usage): array {
            foreach ($fragment as $usage_key => $usage_value) {
                if (is_array($usage_value)) {
                    $target[$usage_key] = $sum_usage(
                        is_array($target[$usage_key] ?? null) ? $target[$usage_key] : [],
                        $usage_value
                    );
                } elseif (is_numeric($usage_value)) {
                    $target[$usage_key] =
                        (is_numeric($target[$usage_key] ?? null) ? $target[$usage_key] : 0) + $usage_value;
                } else {
                    $target[$usage_key] = $usage_value;
                }
            }
            return $target;
        };

        $requests = [];
        foreach ($log_files as $log_file) {
            if ($limit !== null && count($requests) >= $limit) {
                break;
            }
            $file_time = $log_times[$log_file];
            if ($date_until_time !== false && $file_time > $date_until_time) {
                continue;
            }
            if ($date_from_time !== false && $file_time < $date_from_time) {
                break;
            }

            $log_raw = @file_get_contents($log_file);
            if ($log_raw === false) {
                continue;
            }

            $sections = [];
            $current = null;
            foreach (explode("\n", str_replace("\r\n", "\n", $log_raw)) as $line) {
                if (preg_match('/^=== (.+?) ===$/', trim($line), $matches)) {
                    $current = $matches[1];
                    $sections[$current] = [];
                    continue;
                }
                if ($current !== null) {
                    $sections[$current][] = $line;
                }
            }

            $info = $parse_pairs($sections['REQUEST INFO'] ?? []);
            $headers = $parse_pairs($sections['HEADERS'] ?? []);
            $request_body_raw = trim(implode("\n", $sections['REQUEST BODY'] ?? []));
            $request_body = json_decode($request_body_raw, true) ?? $request_body_raw;

            $api_key = null;
            if (preg_match('/^Bearer\s+(.+)$/i', $headers['Authorization'] ?? '', $matches)) {
                $api_key = $matches[1];
            }
            $api_key = $api_key ?? ($headers['X-Api-Key'] ?? null);
            $forwarded_for = array_map('trim', explode(',', $headers['X-Forwarded-For'] ?? ''));

            $api_requests = [];
            $api_responses = [];
            $response = null;
            $other = [];
            foreach ($sections as $name => $lines) {
                if (in_array($name, ['REQUEST INFO', 'HEADERS', 'REQUEST BODY'], true)) {
                    continue;
                }
                if (preg_match('/^API REQUEST\s*\d*$/', $name)) {
                    $block = $parse_block($lines);
                    $auth = [];
                    foreach (explode(', ', $block['meta']['Auth'] ?? '') as $auth_pair) {
                        if (str_contains($auth_pair, '=')) {
                            [$auth_key, $auth_value] = explode('=', $auth_pair, 2);
                            $auth[$auth_key] = $auth_value;
                        }
                    }
                    $api_requests[] = [
                        'time' => $block['time'],
                        'url' => $block['meta']['Upstream URL'] ?? null,
                        'method' => $block['meta']['HTTP Method'] ?? null,
                        'auth' => $auth,
                        'headers' => $block['headers'],
                        'body' => $block['body']
                    ];
                    continue;
                }
                if (preg_match('/^API RESPONSE\s*\d*$/', $name)) {
                    $api_responses[] = $parse_block($lines);
                    continue;
                }
                if ($name === 'RESPONSE') {
                    $response = $parse_block($lines);
                    continue;
                }
                $other[$name] = trim(implode("\n", $lines));
            }

            $usage = null;
            $usage_bodies = array_merge(array_map(fn($api_response) => $api_response['body'], $api_responses), [
                $response['body'] ?? ''
            ]);
            foreach ($usage_bodies as $usage_body) {
                $usageMatches = [];
                if (!preg_match_all('/"usage"\s*:\s*(\{(?:[^{}]|(?1))*\})/', $usage_body, $usageMatches)) {
                    continue;
                }
                $usageJsonMatches = $usageMatches[1] ?? [];
                if (!is_array($usageJsonMatches)) {
                    continue;
                }
                foreach ($usageJsonMatches as $usage_json) {
                    $fragment = json_decode($usage_json, true);
                    if (is_array($fragment)) {
                        $usage = $merge_usage($usage ?? [], $fragment);
                    }
                }
            }

            $duration_in_ms = null;
            $started_at = $to_time($info['Timestamp'] ?? null);
            $ended_at = $to_time(!empty($api_responses) ? end($api_responses)['time'] : null);
            if ($started_at !== null && $ended_at !== null) {
                $duration_in_ms = (int) round(($ended_at - $started_at) * 1000);
            }

            $result = [
                'file' => $log_file,
                'error' => str_starts_with(basename($log_file), 'error-'),
                'time' => $info['Timestamp'] ?? null,
                'url' => $info['URL'] ?? null,
                'method' => $info['Method'] ?? null,
                'info' => $info,
                'headers' => $headers,
                'ip' => $headers['Cf-Connecting-Ip'] ?? ($forwarded_for[0] ?? null) ?: null,
                'host' => $headers['X-Forwarded-Host'] ?? ($headers['Host'] ?? null),
                'user_agent' => $headers['User-Agent'] ?? null,
                'api_key' => $api_key,
                'model' => is_array($request_body) ? $request_body['model'] ?? null : null,
                'stream' => is_array($request_body) ? $request_body['stream'] ?? null : null,
                'request_body' => $request_body,
                'api_requests' => $api_requests,
                'api_responses' => $api_responses,
                'response' => [
                    'status' => $response['status'] ?? null,
                    'headers' => $response['headers'] ?? [],
                    'body' => $response['body'] ?? ''
                ],
                'other' => $other,
                'usage' => $usage,
                'duration_in_ms' => $duration_in_ms,
                'source' => 'proxy',
                'project' =>
                    ($referer = trim((string) ($headers['Referer'] ?? ($headers['Referrer'] ?? '')))) !== ''
                        ? basename($referer)
                        : null,
                'group_key' =>
                    'proxy|' .
                    (is_array($request_body) ? $request_body['model'] ?? '' : '') .
                    '|' .
                    $prompt_key($request_body),
                'calls' => 1
            ];
            if (!$include_body) {
                unset($result['request_body'], $result['response']['body']);
                foreach ($result['api_requests'] as $api_request_key => $api_request) {
                    unset($result['api_requests'][$api_request_key]['body']);
                }
                foreach ($result['api_responses'] as $api_response_key => $api_response) {
                    unset($result['api_responses'][$api_response_key]['body']);
                }
            }
            foreach ($result['api_responses'] as $api_response_key => $api_response) {
                unset($result['api_responses'][$api_response_key]['meta']);
            }
            $requests[] = $result;
        }

        // additionally read the local claude code and codex session logs (calls that hit the
        // providers directly, bypassing the proxy), normalized into the same shape with a source tag
        $make_local = function (
            string $file,
            string $time,
            ?string $model,
            array $usage,
            string $source,
            $user_prompt,
            ?string $cwd
        ) use ($include_body, $prompt_key, $project_from_cwd): array {
            $body = $user_prompt !== null ? ['messages' => [['role' => 'user', 'content' => $user_prompt]]] : null;
            $project = $project_from_cwd($cwd);
            // group by the prompt when known; otherwise fall back to the session (file) so
            // unattributable tool-loop/subagent calls stay traceable per conversation, not one blob
            $prompt = $prompt_key($body);
            $suffix = $prompt !== '' ? $prompt : 'session:' . basename($file);
            $result = [
                'file' => $file,
                'error' => false,
                'time' => $time,
                'url' => null,
                'method' => null,
                'info' => [],
                'headers' => [],
                'ip' => null,
                'host' => null,
                'user_agent' => $source,
                'api_key' => null,
                'model' => $model,
                'stream' => null,
                'request_body' => $body,
                'api_requests' => [],
                'api_responses' => [],
                'response' => ['status' => null, 'headers' => [], 'body' => ''],
                'other' => [],
                'usage' => $usage,
                'duration_in_ms' => null,
                'source' => $source,
                'project' => $project,
                'group_key' => $source . '|' . ($model ?? '') . '|' . $suffix,
                'calls' => 1
            ];
            if (!$include_body) {
                unset($result['request_body'], $result['response']['body']);
            }
            return $result;
        };

        // session transcripts can be hundreds of MB; only the newest turns are relevant here,
        // so read just the tail of each file (streamed) instead of loading the whole thing
        $tail_lines = function (string $file, int $max_bytes = 1048576): array {
            $size = filesize($file) ?: 0;
            $handle = fopen($file, 'rb');
            if ($handle === false) {
                return [];
            }
            if ($size > $max_bytes) {
                fseek($handle, $size - $max_bytes);
            }
            $data = (string) stream_get_contents($handle);
            fclose($handle);
            $lines = explode("\n", $data);
            if ($size > $max_bytes) {
                // the first line is likely a partial record — drop it
                array_shift($lines);
            }
            return $lines;
        };

        // bound the work: without an explicit start date, only scan recently touched sessions
        $min_mtime = $date_from_time !== false ? $date_from_time : time() - 45 * 86400;
        $in_range = function (?float $time_value) use ($date_from_time, $date_until_time): bool {
            if ($time_value === null) {
                return false;
            }
            if ($date_from_time !== false && $time_value < $date_from_time) {
                return false;
            }
            if ($date_until_time !== false && $time_value > $date_until_time) {
                return false;
            }
            return true;
        };

        $claude_dirs = ['/root/.claude/projects', '/host/data/claude/projects'];
        foreach ($claude_dirs as $claude_dir) {
            foreach (glob($claude_dir . '/*/*.jsonl') ?: [] as $session_file) {
                if ((filemtime($session_file) ?: 0) < $min_mtime) {
                    continue;
                }
                $last_user = null;
                $cwd = null;
                foreach ($tail_lines($session_file) as $line) {
                    if ($line === '' || $line[0] !== '{') {
                        continue;
                    }
                    $entry = json_decode($line, true);
                    if (!is_array($entry)) {
                        continue;
                    }
                    if (($entry['cwd'] ?? '') !== '') {
                        $cwd = (string) $entry['cwd'];
                    }
                    $type = $entry['type'] ?? '';
                    if ($type === 'user') {
                        // role "user" also covers tool_result entries, which aren't real prompts;
                        // only keep genuine human input so the turn stays attributed to its prompt
                        $content = $entry['message']['content'] ?? null;
                        if (is_string($content) && trim($content) !== '') {
                            $last_user = $content;
                        } elseif (is_array($content)) {
                            foreach ($content as $part) {
                                if (
                                    (is_string($part) && trim($part) !== '') ||
                                    (is_array($part) && ($part['type'] ?? '') === 'text')
                                ) {
                                    $last_user = $content;
                                    break;
                                }
                            }
                        }
                        continue;
                    }
                    if ($type !== 'assistant' || !is_array($entry['message']['usage'] ?? null)) {
                        continue;
                    }
                    // claude code injects synthetic assistant messages (e.g. "you've hit your session
                    // limit") with a "<synthetic>" model and zero tokens — not real api calls, skip them
                    if (($entry['message']['model'] ?? '') === '<synthetic>') {
                        continue;
                    }
                    $time = (string) ($entry['timestamp'] ?? '');
                    if (!$in_range($to_time($time))) {
                        continue;
                    }
                    $entry_usage = $entry['message']['usage'];
                    $requests[] = $make_local(
                        $session_file,
                        $time,
                        $entry['message']['model'] ?? null,
                        [
                            'input_tokens' => (int) ($entry_usage['input_tokens'] ?? 0),
                            'output_tokens' => (int) ($entry_usage['output_tokens'] ?? 0),
                            'cache_read_input_tokens' => (int) ($entry_usage['cache_read_input_tokens'] ?? 0),
                            'cache_creation_input_tokens' => (int) ($entry_usage['cache_creation_input_tokens'] ?? 0)
                        ],
                        'claude-code',
                        $last_user,
                        $cwd
                    );
                }
            }
        }

        $codex_dirs = ['/root/.codex/sessions', '/host/data/codex/sessions'];
        foreach ($codex_dirs as $codex_dir) {
            foreach (glob($codex_dir . '/*/*/*/rollout-*.jsonl') ?: [] as $session_file) {
                if ((filemtime($session_file) ?: 0) < $min_mtime) {
                    continue;
                }
                // one row per token_count event (= one API request). summing every request's
                // last_token_usage matches the session's cumulative total_token_usage exactly,
                // so no tokens are lost (a per-turn collapse would drop the intermediate requests).
                $model = null;
                $last_user = null;
                $cwd = null;
                // read the head for defaults: session_meta (line 1) carries the cwd, the first
                // turn_context (a few lines in) carries the model. both can be absent from the 1MB
                // tail, so seed them here; the tail overrides with more recent values when present.
                $meta_handle = fopen($session_file, 'rb');
                if ($meta_handle !== false) {
                    for ($head_line = 0; $head_line < 200 && ($model === null || $cwd === null); $head_line++) {
                        $raw = fgets($meta_handle);
                        if ($raw === false) {
                            break;
                        }
                        $head_entry = json_decode($raw, true);
                        if (!is_array($head_entry)) {
                            continue;
                        }
                        $head_payload = $head_entry['payload'] ?? [];
                        if ($cwd === null && ($head_payload['cwd'] ?? '') !== '') {
                            $cwd = (string) $head_payload['cwd'];
                        }
                        if (
                            $model === null &&
                            ($head_entry['type'] ?? '') === 'turn_context' &&
                            ($head_payload['model'] ?? '') !== ''
                        ) {
                            $model = (string) $head_payload['model'];
                        }
                    }
                    fclose($meta_handle);
                }
                foreach ($tail_lines($session_file) as $line) {
                    if ($line === '' || $line[0] !== '{') {
                        continue;
                    }
                    $entry = json_decode($line, true);
                    if (!is_array($entry)) {
                        continue;
                    }
                    $payload = $entry['payload'] ?? [];
                    if (($payload['cwd'] ?? '') !== '') {
                        $cwd = (string) $payload['cwd'];
                    }
                    if (($entry['type'] ?? '') === 'turn_context' && isset($payload['model'])) {
                        $model = (string) $payload['model'];
                    }
                    $payload_type = $payload['type'] ?? '';
                    if ($payload_type === 'user_message' && is_string($payload['message'] ?? null)) {
                        $last_user = $payload['message'];
                    }
                    if ($payload_type !== 'token_count' || !is_array($payload['info']['last_token_usage'] ?? null)) {
                        continue;
                    }
                    $time = (string) ($entry['timestamp'] ?? '');
                    if (!$in_range($to_time($time))) {
                        continue;
                    }
                    $last = $payload['info']['last_token_usage'];
                    $requests[] = $make_local(
                        $session_file,
                        $time,
                        $model,
                        [
                            'input_tokens' => (int) ($last['input_tokens'] ?? 0),
                            'output_tokens' => (int) ($last['output_tokens'] ?? 0),
                            'cache_read_input_tokens' => (int) ($last['cached_input_tokens'] ?? 0)
                        ],
                        'codex',
                        $last_user,
                        $cwd
                    );
                }
            }
        }

        $opencode_database = self::getOpenCodeDatabasePath();
        if ($opencode_database !== null) {
            try {
                $connection = new \PDO(
                    'sqlite:' . $opencode_database,
                    options: [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
                );
                $where = [
                    "json_extract(m.data, '$.role') = 'assistant'",
                    "json_extract(m.data, '$.providerID') = 'opencode-go'"
                ];
                $parameters = [];
                if ($date_from_time !== false) {
                    $where[] = 'm.time_created >= :time_from';
                    $parameters['time_from'] = (int) floor($date_from_time * 1000);
                }
                if ($date_until_time !== false) {
                    $where[] = 'm.time_created <= :time_until';
                    $parameters['time_until'] = (int) floor($date_until_time * 1000);
                }
                if ($date_from_time === false) {
                    $where[] = 'm.time_created >= :recent_time';
                    $parameters['recent_time'] = (int) floor($min_mtime * 1000);
                }
                $statement = $connection->prepare(
                    "SELECT m.id, m.session_id, m.time_created, m.data, s.directory,
                        (SELECT p.data FROM part p
                         WHERE p.message_id = json_extract(m.data, '$.parentID')
                           AND json_extract(p.data, '$.type') = 'text'
                         ORDER BY p.time_created ASC LIMIT 1) AS user_part
                     FROM message m
                     INNER JOIN session s ON s.id = m.session_id
                     WHERE " . implode(' AND ', $where) . '
                     ORDER BY m.time_created ASC'
                );
                $statement->execute($parameters);
                foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $entry) {
                    $data = json_decode((string) ($entry['data'] ?? ''), true);
                    if (!is_array($data)) {
                        continue;
                    }
                    $user_part = json_decode((string) ($entry['user_part'] ?? ''), true);
                    $tokens = is_array($data['tokens'] ?? null) ? $data['tokens'] : [];
                    $cache = is_array($tokens['cache'] ?? null) ? $tokens['cache'] : [];
                    $request = $make_local(
                        $opencode_database,
                        date(\DateTimeInterface::ATOM, (int) floor((float) $entry['time_created'] / 1000)),
                        $data['modelID'] ?? null,
                        [
                            'input_tokens' => (int) ($tokens['input'] ?? 0),
                            'output_tokens' => (int) ($tokens['output'] ?? 0),
                            'cache_read_input_tokens' => (int) ($cache['read'] ?? 0),
                            'cache_creation_input_tokens' => (int) ($cache['write'] ?? 0),
                            'costs' => (float) ($data['cost'] ?? 0)
                        ],
                        'opencode',
                        is_array($user_part) ? ($user_part['text'] ?? null) : null,
                        isset($entry['directory']) ? (string) $entry['directory'] : null
                    );
                    $request['file'] = null;
                    $request['session_id'] = (string) ($entry['session_id'] ?? '');
                    $started_at = (int) ($data['time']['created'] ?? 0);
                    $completed_at = (int) ($data['time']['completed'] ?? 0);
                    $request['duration_in_ms'] = $completed_at > $started_at ? $completed_at - $started_at : null;
                    $requests[] = $request;
                }
            } catch (\PDOException) {
            }
        }

        // group_by=true: collapse calls into one row, summing tokens and counting the calls (no
        // tokens lost). always group by project — the working directory (cwd) for local calls
        // (claude-code/codex, always known), the Referer for proxy calls (cliproxyapi); proxy
        // calls without a Referer have no project and fall back to the (short) prompt.
        if ($group_by) {
            $grouped = [];
            foreach ($requests as $row) {
                if (($row['project'] ?? '') !== '') {
                    $key = ($row['source'] ?? 'proxy') . '|project:' . $row['project'];
                } else {
                    $key = $row['group_key'] ?? '';
                }
                if (!isset($grouped[$key])) {
                    $grouped[$key] = $row;
                    continue;
                }
                $grouped[$key]['usage'] = $sum_usage(
                    is_array($grouped[$key]['usage'] ?? null) ? $grouped[$key]['usage'] : [],
                    is_array($row['usage'] ?? null) ? $row['usage'] : []
                );
                $grouped[$key]['calls'] = ($grouped[$key]['calls'] ?? 1) + ($row['calls'] ?? 1);
                if (($to_time($row['time'] ?? null) ?? 0) > ($to_time($grouped[$key]['time'] ?? null) ?? 0)) {
                    $grouped[$key]['time'] = $row['time'];
                }
                // the kept (first) row can be a tail-truncated call with no model/prompt — fill those
                // from a later row in the group so the collapsed row still shows a real model and prompt
                if (($grouped[$key]['model'] ?? '') === '' && ($row['model'] ?? '') !== '') {
                    $grouped[$key]['model'] = $row['model'];
                }
                if (empty($grouped[$key]['request_body']) && !empty($row['request_body'])) {
                    $grouped[$key]['request_body'] = $row['request_body'];
                }
            }
            $requests = array_values($grouped);
        }

        // merge newest-first across all sources and re-apply the limit
        usort($requests, fn($a, $b) => ($to_time($b['time'] ?? null) ?? 0) <=> ($to_time($a['time'] ?? null) ?? 0));
        if ($limit !== null) {
            $requests = array_slice($requests, 0, $limit);
        }

        return $requests;
    }

    public static function purgeCliApiRequestLogs(?string $date_from = null, ?string $date_until = null): array
    {
        $date_from_time = $date_from !== null ? strtotime($date_from) : false;
        $date_until_time = $date_until !== null ? strtotime($date_until) : false;
        $deleted = [];
        $bytes = 0;
        foreach (['/root/.cli-proxy-api/logs', '/host/data/server/cliproxyapi/logs'] as $dir) {
            foreach (glob($dir . '/*.log') ?: [] as $file) {
                // the request timestamp sits in the file head, above the body
                $timestamp = null;
                $handle = @fopen($file, 'r');
                if ($handle !== false) {
                    while (($line = fgets($handle)) !== false) {
                        if (str_starts_with($line, '=== REQUEST BODY ===')) {
                            break;
                        }
                        if (str_starts_with($line, 'Timestamp:')) {
                            $timestamp = strtotime(trim(substr($line, 10))) ?: null;
                            break;
                        }
                    }
                    fclose($handle);
                }
                $timestamp ??= filemtime($file) ?: 0;
                if ($date_from_time !== false && $timestamp < $date_from_time) {
                    continue;
                }
                if ($date_until_time !== false && $timestamp > $date_until_time) {
                    continue;
                }
                $size = filesize($file) ?: 0;
                if (!@unlink($file)) {
                    continue;
                }
                $deleted[] = $file;
                $bytes += $size;
            }
        }
        return ['count' => count($deleted), 'bytes' => $bytes, 'files' => $deleted];
    }

    protected function fetchModelsDevApi(): ?object
    {
        static $api = null;
        if ($api !== null) {
            return $api;
        }
        $cache_file = sys_get_temp_dir() . '/aihelper-modelsdev.json';
        $ttl_seconds = 86400;
        $fetch_timeout = 10;
        if (is_file($cache_file) && time() - filemtime($cache_file) < $ttl_seconds) {
            $cached = json_decode((string) file_get_contents($cache_file));
            if (is_object($cached)) {
                $api = $cached;
                return $api;
            }
        }
        $response = __::curl(url: 'https://models.dev/api.json', method: 'GET', timeout: $fetch_timeout);
        $fresh = $response?->result ?? null;
        if (is_object($fresh)) {
            // atomic write so a concurrent reader never sees a half-written file
            $tmp = $cache_file . '.' . getmypid() . '.tmp';
            if (file_put_contents($tmp, json_encode($fresh)) !== false) {
                rename($tmp, $cache_file);
            }
            $api = $fresh;
            return $api;
        }
        // network failed: serve a stale cache if one exists rather than nothing
        if (is_file($cache_file)) {
            $cached = json_decode((string) file_get_contents($cache_file));
            if (is_object($cached)) {
                $api = $cached;
                return $api;
            }
        }
        return null;
    }

    public function fetchModelsFromModelsDev(): array
    {
        $provider = $this->name;
        if ($provider === null) {
            return [];
        }
        if (in_array($provider, ['openrouter'], true)) {
            return [];
        }

        $result = $this->fetchModelsDevApi();
        if (!is_object($result) || !__::x($result->{$provider}->models ?? null)) {
            return [];
        }

        $models = [];
        $default_candidates = [];
        $required_input_modalities = [];
        if (in_array($provider, ['anthropic', 'google', 'openai', 'openrouter'], true)) {
            $required_input_modalities = ['image', 'pdf'];
        }
        if ($provider === 'xai') {
            $required_input_modalities = ['image'];
        }
        foreach (array_values((array) $result->{$provider}->models) as $model) {
            if (!__::x($model?->id ?? null)) {
                continue;
            }

            $family = (string) ($model->family ?? '');
            $searchable_name = strtolower($model->id . ' ' . ($model->name ?? '') . ' ' . $family);
            if (
                strpos($searchable_name, 'embedding') !== false ||
                strpos($searchable_name, 'embed') !== false ||
                strpos($searchable_name, 'moderation') !== false ||
                strpos($searchable_name, 'rerank') !== false ||
                strpos($searchable_name, 'safety') !== false ||
                strpos($searchable_name, 'guard') !== false ||
                $family === 'bge'
            ) {
                continue;
            }

            $input_modalities = array_values((array) ($model->modalities->input ?? []));
            $output_modalities = array_values((array) ($model->modalities->output ?? []));
            $efforts = [];
            $supports_reasoning_control = false;
            $effort_budget_min = null;
            $effort_budget_max = null;
            foreach ((array) ($model->reasoning_options ?? []) as $reasoning_option) {
                if (in_array($reasoning_option->type ?? null, ['budget_tokens', 'toggle'], true)) {
                    $supports_reasoning_control = true;
                }
                if (($reasoning_option->type ?? null) === 'budget_tokens') {
                    if (isset($reasoning_option->min) && is_numeric($reasoning_option->min)) {
                        $effort_budget_min = (int) $reasoning_option->min;
                    }
                    if (isset($reasoning_option->max) && is_numeric($reasoning_option->max)) {
                        $effort_budget_max = (int) $reasoning_option->max;
                    }
                }
                if (($reasoning_option->type ?? null) !== 'effort' || !is_array($reasoning_option->values ?? null)) {
                    continue;
                }
                $efforts = array_values(
                    array_filter(
                        (array) $reasoning_option->values,
                        fn($value) => is_string($value) && in_array($value, $this->getEffortValues(), true)
                    )
                );
                $supports_reasoning_control = true;
            }
            if (empty($efforts) && $supports_reasoning_control) {
                $efforts = $this->getEffortValues();
            }
            $cost = $model->cost ?? null;
            $model_date = (string) ($model->release_date ?? ($model->last_updated ?? ''));
            $model_key = count($models);
            $models[] = [
                'name' => $model->id,
                'context_length' => (int) ($model->limit->context ?? 128000),
                'max_output_tokens' => (int) ($model->limit->output ?? 16384),
                'costs' => [
                    'input' => ((float) ($cost->input ?? 0)) / 1000000,
                    'input_cached' => ((float) ($cost->cache_read ?? 0)) / 1000000,
                    'output' => ((float) ($cost->output ?? 0)) / 1000000
                ],
                'supports_temperature' => (bool) ($model->temperature ?? true),
                'supports_tools' => (bool) ($model->tool_call ?? true),
                'supports_text_to_image' => in_array('image', $output_modalities, true),
                'supports_text_to_audio' => in_array('audio', $output_modalities, true),
                'supports_image_to_text' => in_array('image', $input_modalities, true),
                'supports_audio_to_text' => $provider !== 'openrouter' && in_array('audio', $input_modalities, true),
                'supports_effort' => (bool) ($model->reasoning ?? false) && $supports_reasoning_control,
                'efforts' => $efforts,
                'effort_budget_min' => $effort_budget_min,
                'effort_budget_max' => $effort_budget_max,
                'open_weights' => (bool) ($model->open_weights ?? false),
                'default' => false,
                'test' => false
            ];
            $matches_required_input_modalities = true;
            foreach ($required_input_modalities as $required_input_modality) {
                if (in_array($required_input_modality, $input_modalities, true)) {
                    continue;
                }
                $matches_required_input_modalities = false;
                break;
            }
            if (
                $matches_required_input_modalities &&
                !str_starts_with($model->id, '~') &&
                in_array('text', $input_modalities, true) &&
                in_array('text', $output_modalities, true) &&
                (bool) ($model->tool_call ?? true) === true
            ) {
                $default_candidates[] = [
                    'key' => $model_key,
                    'date' => $model_date,
                    'price' =>
                        isset($cost->input) || isset($cost->output)
                            ? ((float) ($cost->input ?? 0)) + ((float) ($cost->output ?? 0))
                            : \PHP_FLOAT_MAX
                ];
            }
        }

        if (empty($models)) {
            return [];
        }

        if (empty($default_candidates)) {
            $default_candidates[] = ['key' => 0, 'date' => '', 'price' => \PHP_FLOAT_MAX];
        }
        usort($default_candidates, function ($a, $b) {
            return strcmp($b['date'], $a['date']);
        });
        $default_candidates = array_slice($default_candidates, 0, 10);
        $priced_default_candidates = array_values(
            array_filter($default_candidates, function ($candidate) {
                return $candidate['price'] > 0 && $candidate['price'] < \PHP_FLOAT_MAX;
            })
        );
        if (!empty($priced_default_candidates)) {
            $default_candidates = $priced_default_candidates;
        }
        usort($default_candidates, function ($a, $b) {
            return $a['price'] <=> $b['price'];
        });
        $models[$default_candidates[0]['key']]['default'] = true;
        $models[$default_candidates[0]['key']]['test'] = true;

        return $models;
    }

    public function ask(?string $prompt = null, mixed $files = null): array
    {
        $this->autoCompactSession();
        $this->stubOversizedFileBlocks();
        $this->stream_text_emitted_since_tool = false;
        $return = ['response' => null, 'success' => false, 'costs' => 0.0];
        $max_tries = $this->max_tries;
        $extra_transient_retries = max(0, 3 - $this->max_tries);
        $extra_availability_retries = max(0, 9 - $this->max_tries);
        $transient_retry = false;
        $availability_retry = false;
        $attempt = 0;
        while ($return['success'] === false && $max_tries > 0) {
            if ($attempt > 0) {
                $backoff_s = $this->retryBackoffSeconds($attempt, $transient_retry, $availability_retry);
                $this->log('⚠️ tries left: ' . $max_tries . ' — backoff ' . $backoff_s . 's');
                if ($backoff_s > 0) {
                    sleep($backoff_s);
                }
            }
            $transient_retry = false;
            $availability_retry = false;
            try {
                $return = $this->askThis(
                    prompt: $prompt,
                    files: $files,
                    add_prompt_to_session: $attempt === 0,
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
                } elseif ($this->isTransientRequestError($e->getMessage())) {
                    $this->log('⚠️ transient request error — retrying: ' . $e->getMessage());
                    $return = [
                        'response' => $e->getMessage(),
                        'success' => false,
                        'costs' => $return['costs'] ?? 0.0
                    ];
                    $transient_retry = true;
                } else {
                    throw $e;
                }
            }
            if (
                ($return['success'] ?? false) === true &&
                (($return['response'] ?? null) === null ||
                    (is_string($return['response']) && trim($return['response']) === '')) &&
                !($this->mcp_servers_call_type === 'local' && !empty($this->mcp_servers_tools_map))
            ) {
                $return['response'] = 'No response from provider.';
                $return['success'] = false;
            }
            $retryResponse = '';
            if ($return['success'] === false && $this->isTransientRequestError($return['response'] ?? '')) {
                $transient_retry = true;
                $retryResponse = is_string($return['response'] ?? null)
                    ? strtolower($return['response'])
                    : strtolower(
                        json_encode($return['response'] ?? null, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: ''
                    );
                $availability_retry =
                    str_contains($retryResponse, 'auth_unavailable') ||
                    str_contains($retryResponse, 'no auth available') ||
                    str_contains($retryResponse, 'connection refused') ||
                    str_contains($retryResponse, 'no response from provider') ||
                    str_contains($retryResponse, 'too many concurrent requests') ||
                    str_contains($retryResponse, 'temporarily unavailable') ||
                    str_contains($retryResponse, 'overloaded') ||
                    str_contains($retryResponse, 'upstream connect error') ||
                    str_contains($retryResponse, 'connection termination') ||
                    str_contains($retryResponse, 'http/request failed: error sending request') ||
                    str_contains($retryResponse, 'http 520') ||
                    str_contains($retryResponse, 'error code: 520') ||
                    str_contains($retryResponse, '(http 0)');
            }
            if ($availability_retry) {
                $maximum_availability_tries =
                    str_contains($retryResponse, 'auth_unavailable') ||
                    str_contains($retryResponse, 'no auth available')
                        ? 3
                        : 9;
                if ($attempt + 1 < $maximum_availability_tries && $extra_availability_retries > 0) {
                    $extra_availability_retries--;
                    $max_tries++;
                }
            } elseif ($transient_retry && $extra_transient_retries > 0) {
                $extra_transient_retries--;
                $max_tries++;
            }
            $this->log($return, 'return');
            $attempt++;
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

    protected function isTransientRequestError(mixed $message): bool
    {
        if (!is_string($message)) {
            $message = json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: serialize($message);
        }
        $message = strtolower($message);
        if (preg_match('/\b(?:http\s*)?(?:408|500|502|503|504|520)\b/', $message) === 1) {
            return true;
        }
        foreach (
            [
                'connection reset',
                'upstream connect error',
                'connection termination',
                'unexpected eof',
                'empty_stream',
                'upstream stream closed before first payload',
                'protocol_error',
                'internal_error; received from peer',
                'stream disconnected before completion',
                'stream closed before response.completed',
                'operation timed out',
                'request timed out',
                'i/o timeout',
                'connection timeout',
                'http/request failed: error sending request',
                'network is unreachable',
                'connection refused',
                'no response from provider',
                'too many concurrent requests',
                '(http 0)',
                'server misbehaving',
                'temporary failure in name resolution',
                'could not resolve host',
                'no such host',
                'name or service not known'
            ]
            as $needle
        ) {
            if (str_contains($message, $needle)) {
                return $this->stream_text_emitted_since_tool !== true;
            }
        }
        if (preg_match('/(?:^|:\s*)eof$/', trim($message)) === 1) {
            return $this->stream_text_emitted_since_tool !== true;
        }
        foreach (
            [
                'auth_unavailable',
                'no auth available',
                'temporarily unavailable',
                'overloaded'
            ]
            as $needle
        ) {
            if (str_contains($message, $needle)) {
                return true;
            }
        }
        return false;
    }

    protected function retryBackoffSeconds(int $attempt, bool $transient, bool $availabilityFailure = false): int
    {
        if ($availabilityFailure) {
            return min(60, 5 * (int) pow(2, $attempt - 1));
        }
        return $transient ? min(4, (int) pow(2, $attempt - 1)) : 15 * (int) pow(2, $attempt - 1);
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
        if (
            $n > 1 &&
            (($this->name === 'google' &&
                str_starts_with((string) $this->model, 'gemini-') &&
                str_contains((string) $this->model, '-image')) ||
                ($this->name === 'cliproxyapi' && str_starts_with((string) $this->model, 'gpt-image')))
        ) {
            $results = [];
            $outputInfo = $output_file !== null ? pathinfo($output_file) : null;
            $processes = [];
            for ($imageIndex = 1; $imageIndex <= $n; $imageIndex++) {
                $currentOutputFile = null;
                if ($outputInfo !== null) {
                    $outputDirectory = $outputInfo['dirname'] ?? '.';
                    $outputBase = $outputInfo['filename'] ?? 'out';
                    $outputExtension = isset($outputInfo['extension']) ? '.' . $outputInfo['extension'] : '';
                    $currentOutputFile =
                        $outputDirectory . '/' . $outputBase . '-' . $imageIndex . $outputExtension;
                }
                $currentPrompt =
                    $prompt .
                    "\n\nGenerate variant " .
                    $imageIndex .
                    ' of ' .
                    $n .
                    '. Use a materially different composition while preserving the requested subject and style.';
                if (function_exists('pcntl_fork') && function_exists('pcntl_waitpid')) {
                    $resultFile = tempnam(sys_get_temp_dir(), 'aihelper-image-');
                    if ($resultFile === false) {
                        return ['response' => 'Could not create image result file.', 'success' => false, 'costs' => 0.0];
                    }
                    $processId = pcntl_fork();
                    if ($processId === -1) {
                        unlink($resultFile);
                        foreach ($processes as $process) {
                            pcntl_waitpid($process['process_id'], $status);
                            unlink($process['result_file']);
                        }
                        return ['response' => 'Could not start parallel image generation.', 'success' => false, 'costs' => 0.0];
                    }
                    if ($processId === 0) {
                        try {
                            $result = $this->imageThis(
                                prompt: $currentPrompt,
                                n: 1,
                                aspect_ratio: $aspect_ratio,
                                input_file: $input_file,
                                output_file: $currentOutputFile
                            );
                        } catch (\Throwable $exception) {
                            $result = ['response' => $exception->getMessage(), 'success' => false, 'costs' => 0.0];
                        }
                        file_put_contents($resultFile, serialize($result), LOCK_EX);
                        exit(0);
                    }
                    $processes[$imageIndex] = [
                        'process_id' => $processId,
                        'result_file' => $resultFile
                    ];
                    continue;
                }
                $results[$imageIndex] = $this->imageThis(
                    prompt: $currentPrompt,
                    n: 1,
                    aspect_ratio: $aspect_ratio,
                    input_file: $input_file,
                    output_file: $currentOutputFile
                );
            }
            $status = 0;
            foreach ($processes as $imageIndex => $process) {
                pcntl_waitpid($process['process_id'], $status);
                $serializedResult = file_get_contents($process['result_file']);
                unlink($process['result_file']);
                $result = is_string($serializedResult) ? unserialize($serializedResult, ['allowed_classes' => false]) : false;
                $results[$imageIndex] = is_array($result)
                    ? $result
                    : ['response' => 'Parallel image generation returned no result.', 'success' => false, 'costs' => 0.0];
            }
            ksort($results);
            $responses = [];
            $costs = 0.0;
            foreach ($results as $result) {
                $costs += (float) ($result['costs'] ?? 0.0);
                if (($result['success'] ?? false) !== true) {
                    return ['response' => $result['response'] ?? null, 'success' => false, 'costs' => $costs];
                }
                $responses[] = $result['response'];
            }
            return ['response' => $responses, 'success' => true, 'costs' => $costs];
        }
        $is_edit = $input_file !== null;
        $headers = [];
        $tmp_input = null;
        if ($this->name === 'google') {
            $is_gemini_image_model =
                str_starts_with((string) $this->model, 'gemini-') && str_contains((string) $this->model, '-image');
            if ($is_gemini_image_model) {
                $aspect_payload = '1:1';
                if (
                    $aspect_ratio !== null &&
                    $aspect_ratio !== '' &&
                    preg_match('/^(\d+(?:\.\d+)?)\s*:\s*(\d+(?:\.\d+)?)$/', $aspect_ratio, $aspect_ratio__match) ===
                        1 &&
                    (float) $aspect_ratio__match[1] > 0 &&
                    (float) $aspect_ratio__match[2] > 0
                ) {
                    $aspect_ratio__target = (float) $aspect_ratio__match[1] / (float) $aspect_ratio__match[2];
                    $aspect_ratio__candidates = [
                        '1:1' => 1.0,
                        '2:3' => 2 / 3,
                        '3:2' => 3 / 2,
                        '3:4' => 3 / 4,
                        '4:3' => 4 / 3,
                        '4:5' => 4 / 5,
                        '5:4' => 5 / 4,
                        '9:16' => 9 / 16,
                        '16:9' => 16 / 9,
                        '21:9' => 21 / 9
                    ];
                    $aspect_ratio__best_delta = \PHP_FLOAT_MAX;
                    foreach ($aspect_ratio__candidates as $label => $val) {
                        $d = abs(log($aspect_ratio__target / $val));
                        if ($d < $aspect_ratio__best_delta) {
                            $aspect_ratio__best_delta = $d;
                            $aspect_payload = $label;
                        }
                    }
                }
                $parts = [];
                if ($is_edit) {
                    $input_binary = null;
                    $input_mime = 'image/png';
                    if (is_string($input_file) && is_file($input_file)) {
                        $input_binary = file_get_contents($input_file);
                        $detected_mime = mime_content_type($input_file);
                        if (is_string($detected_mime) && $detected_mime !== '') {
                            $input_mime = $detected_mime;
                        }
                    } elseif (
                        is_string($input_file) &&
                        (str_starts_with($input_file, 'http://') || str_starts_with($input_file, 'https://'))
                    ) {
                        if (!self::isPublicHttpUrl($input_file)) {
                            $this->log('⛔ image: refused private/reserved url ' . $input_file);
                            return ['response' => null, 'success' => false, 'costs' => 0.0];
                        }
                        $input_binary = self::fetchUrlBinary($input_file, (int) ($this->timeout ?? 30));
                    } elseif (is_string($input_file) && str_contains($input_file, ';base64,')) {
                        $input_binary = base64_decode(explode(';base64,', $input_file, 2)[1], true);
                        if (preg_match('/^data:([^;]+);base64,/', $input_file, $input_mime_match) === 1) {
                            $input_mime = $input_mime_match[1];
                        }
                    } elseif (is_string($input_file)) {
                        $input_binary = base64_decode($input_file, true);
                    }
                    if (!is_string($input_binary) || $input_binary === '') {
                        $this->log('⛔ image: invalid input_file for google gemini image');
                        return ['response' => null, 'success' => false, 'costs' => 0.0];
                    }
                    $parts[] = [
                        'inline_data' => [
                            'mime_type' => $input_mime,
                            'data' => base64_encode($input_binary)
                        ]
                    ];
                }
                $parts[] = ['text' => (string) $prompt];
                $payload = [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => $parts
                        ]
                    ],
                    'generationConfig' => [
                        'responseModalities' => ['IMAGE'],
                        'imageConfig' => [
                            'aspectRatio' => $aspect_payload
                        ]
                    ]
                ];
                $endpoint = $this->url . '/models/' . $this->model . ':generateContent?key=' . $this->api_key;
                $headers = ['Content-Type: application/json'];
                $body = json_encode($payload);
            } else {
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
                    preg_match('/^(\d+(?:\.\d+)?)\s*:\s*(\d+(?:\.\d+)?)$/', $aspect_ratio, $aspect_ratio__match) ===
                        1 &&
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
                    $aspect_ratio__best_delta = \PHP_FLOAT_MAX;
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
            }
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
                $aspect_ratio__best_delta = \PHP_FLOAT_MAX;
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
        $max_tries = max(1, (int) ($this->max_tries ?? 1));
        $raw = false;
        $err = '';
        $http = 0;
        for ($attempt = 1; $attempt <= $max_tries; $attempt++) {
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
            // a 200 with an empty image payload
            $empty_image = false;
            if ($raw !== false && $http >= 200 && $http < 300) {
                $peek = json_decode((string) $raw, true);
                $empty_image =
                    $this->name === 'google'
                        ? !is_array($peek) || (empty($peek['predictions']) && empty($peek['candidates']))
                        : !is_array($peek) || empty($peek['data']);
            }
            // retry transient failures (network error, 429, 5xx, empty 200 response)
            $is_transient = $raw === false || $http === 429 || $http >= 500 || $empty_image;
            if (!$is_transient || $attempt >= $max_tries) {
                break;
            }
            $this->log(
                '⚠️ image transient ' .
                    ($empty_image ? 'empty-response' : 'HTTP ' . $http) .
                    ' (' .
                    ($err ?: 'no curl error') .
                    ') — retry ' .
                    $attempt .
                    '/' .
                    ($max_tries - 1)
            );
            sleep($attempt * 3);
        }
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
            $is_gemini_image_model =
                str_starts_with((string) $this->model, 'gemini-') && str_contains((string) $this->model, '-image');
            $items = [];
            if ($is_gemini_image_model) {
                foreach ($data['candidates'] ?? [] as $candidate) {
                    foreach ($candidate['content']['parts'] ?? [] as $part) {
                        $inline_data = $part['inlineData'] ?? ($part['inline_data'] ?? null);
                        if (!empty($inline_data['data'])) {
                            $items[] = ['bytesBase64Encoded' => (string) $inline_data['data']];
                        }
                    }
                }
            } else {
                // Imagen response shape: predictions[].bytesBase64Encoded
                $items = is_array($data) ? $data['predictions'] ?? [] : [];
            }
            if (!is_array($items) || count($items) === 0) {
                $msg =
                    'image: provider returned no image (empty response) — usually a transient provider overload (already retried), possibly a content/safety rejection';
                $this->log('⛔ ' . $msg);
                return ['response' => $msg, 'success' => false, 'costs' => 0.0];
            }
            $b64s = [];
            foreach ($items as $it) {
                if (!empty($it['bytesBase64Encoded'])) {
                    $b64s[] = (string) $it['bytesBase64Encoded'];
                }
            }
            if ($b64s === []) {
                $msg =
                    'image: provider returned no image data — usually a transient provider overload (already retried), possibly a content/safety rejection';
                $this->log('⛔ ' . $msg);
                return ['response' => $msg, 'success' => false, 'costs' => 0.0];
            }
        } else {
            // OpenAI/xAI shape: data[].b64_json or data[].url (download + encode)
            $items = is_array($data) ? $data['data'] ?? [] : [];
            if (!is_array($items) || count($items) === 0) {
                $msg =
                    'image: provider returned no image (empty response) — usually a transient provider overload (already retried), possibly a content/safety rejection';
                $this->log('⛔ ' . $msg);
                return ['response' => $msg, 'success' => false, 'costs' => 0.0];
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
                $msg = 'image: failed to download one or more result urls';
                $this->log('⛔ ' . $msg);
                return ['response' => $msg, 'success' => false, 'costs' => 0.0];
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
                    $msg = 'image: failed to write output_file ' . $path;
                    $this->log('⛔ ' . $msg);
                    return ['response' => $msg, 'success' => false, 'costs' => 0.0];
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
                $cost_per = (float) ($m['costs']['image'] ?? ($m['costs']['input'] ?? 0) ?: 0);
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
        if ((string) $raw === '') {
            $msg = 'audio: provider returned no audio (empty response)';
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
                $msg = 'audio: failed to write output_file ' . $output_file;
                $this->log('⛔ ' . $msg);
                return ['response' => $msg, 'success' => false, 'costs' => 0.0];
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
     * Replace processed oversized tool and image payloads across all known
     * provider shapes with a small text stub. The currently unanswered tool
     * result is excluded by autoCompactSession(), so the next model call still
     * receives its complete input.
     *
     * Recognises:
     *  - OpenAI: ["type" => "image_url", "image_url" => ["url" => "data:..."]]
     *  - Anthropic: ["type" => "image", "source" => ["type" => "base64", "data" => "..."]]
     *  - Google: ["inline_data" => ["mime_type" => "image/...", "data" => "..."]]
     *  - bare data URIs anywhere in the tree (fallback)
     */
    private static function replaceOversizedContextPayloadsWithStubs(mixed $node, int &$count): mixed
    {
        $stub_text =
            '[Großer Tool- oder Bildinhalt aus früherem Turn während Kontext-Kompression entfernt — Inhalt in Zusammenfassung erhalten]';

        if (is_array($node)) {
            // detect known image-container shapes and swap the whole block out
            $is_openai_image = isset($node['type']) && $node['type'] === 'image_url' && isset($node['image_url']);
            $is_anthropic_image = isset($node['type']) && $node['type'] === 'image' && isset($node['source']);
            $is_mcp_image =
                isset($node['type']) &&
                $node['type'] === 'image' &&
                isset($node['data']) &&
                is_string($node['data']);
            $is_google_image = isset($node['inline_data']) && is_array($node['inline_data']);
            if ($is_openai_image || $is_anthropic_image || $is_mcp_image) {
                $count++;
                return ['type' => 'text', 'text' => $stub_text];
            }
            if ($is_google_image) {
                $count++;
                return ['text' => $stub_text];
            }
            if (
                ($node['role'] ?? null) === 'tool' &&
                isset($node['content']) &&
                is_string($node['content']) &&
                mb_strlen($node['content']) > 60000
            ) {
                $count++;
                $node['content'] = self::compactOversizedContextText($node['content'], $stub_text);
            }
            if (
                ($node['type'] ?? null) === 'tool_result' &&
                isset($node['content']) &&
                is_string($node['content']) &&
                mb_strlen($node['content']) > 60000
            ) {
                $count++;
                $node['content'] = self::compactOversizedContextText($node['content'], $stub_text);
            }
            if (
                ($node['type'] ?? null) === 'function_call_output' &&
                isset($node['output']) &&
                is_string($node['output']) &&
                mb_strlen($node['output']) > 60000
            ) {
                $count++;
                $node['output'] = self::compactOversizedContextText($node['output'], $stub_text);
            }
            if (
                isset($node['functionResponse']['response']['result']) &&
                is_string($node['functionResponse']['response']['result']) &&
                mb_strlen($node['functionResponse']['response']['result']) > 60000
            ) {
                $count++;
                $node['functionResponse']['response']['result'] = self::compactOversizedContextText(
                    $node['functionResponse']['response']['result'],
                    $stub_text
                );
            }
            $out = [];
            foreach ($node as $k => $v) {
                $out[$k] = self::replaceOversizedContextPayloadsWithStubs($v, $count);
            }
            return $out;
        }
        if (is_object($node)) {
            $arr = (array) $node;
            $is_openai_image = ($arr['type'] ?? null) === 'image_url' && isset($arr['image_url']);
            $is_anthropic_image = ($arr['type'] ?? null) === 'image' && isset($arr['source']);
            $is_mcp_image =
                ($arr['type'] ?? null) === 'image' && isset($arr['data']) && is_string($arr['data']);
            $is_google_image = isset($arr['inline_data']);
            if ($is_openai_image || $is_anthropic_image || $is_mcp_image) {
                $count++;
                return (object) ['type' => 'text', 'text' => $stub_text];
            }
            if ($is_google_image) {
                $count++;
                return (object) ['text' => $stub_text];
            }
            $out = clone $node;
            foreach (get_object_vars($out) as $k => $v) {
                $out->$k = self::replaceOversizedContextPayloadsWithStubs($v, $count);
            }
            return $out;
        }
        if (is_string($node) && str_starts_with($node, 'data:image/') && str_contains($node, ';base64,')) {
            $count++;
            return $stub_text;
        }
        return $node;
    }

    /**
     * Keep bounded evidence from a processed result while removing most of its payload.
     */
    private static function compactOversizedContextText(string $value, string $stubText): string
    {
        return $stubText .
            ' (' .
            mb_strlen($value) .
            " Zeichen)\n\n[Anfang des entfernten Inhalts]\n" .
            mb_substr($value, 0, 6000) .
            "\n\n[Ende des entfernten Inhalts]\n" .
            mb_substr($value, -2000);
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
        // PARALLEL tool calls put SEVERAL consecutive `tool` messages after one
        // assistant; the batch must be consumed completely (last may already be
        // a `tool` of the same batch), otherwise the remaining outputs get
        // compacted away and the provider rejects the orphaned tool_calls with
        // "No tool output found for function call".
        while ($head_end < $tail_start) {
            $last = is_array($session[$head_end - 1] ?? null)
                ? $session[$head_end - 1]
                : (array) ($session[$head_end - 1] ?? []);
            $next = is_array($session[$head_end] ?? null) ? $session[$head_end] : (array) ($session[$head_end] ?? []);
            $last_has_tool_calls = ($last['role'] ?? '') === 'assistant' && !empty($last['tool_calls']);
            $last_is_tool = ($last['role'] ?? '') === 'tool';
            $next_is_tool = ($next['role'] ?? '') === 'tool';
            if (($last_has_tool_calls || $last_is_tool) && $next_is_tool) {
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

        for ($message_index = 0; $message_index < count($session); $message_index++) {
            $message = is_array($session[$message_index] ?? null)
                ? $session[$message_index]
                : (array) ($session[$message_index] ?? []);
            if (($message['role'] ?? null) !== 'assistant' || empty($message['tool_calls'])) {
                continue;
            }
            $batch_end = $message_index + 1;
            while ($batch_end < count($session)) {
                $batch_message = is_array($session[$batch_end] ?? null)
                    ? $session[$batch_end]
                    : (array) ($session[$batch_end] ?? []);
                if (($batch_message['role'] ?? null) !== 'tool') {
                    break;
                }
                $batch_end++;
            }
            $head_splits_batch = $message_index < $head_end && $head_end < $batch_end;
            $tail_splits_batch = $message_index < $tail_start && $tail_start < $batch_end;
            if (!$head_splits_batch && !$tail_splits_batch) {
                continue;
            }
            $head_end = min($head_end, $message_index);
            $tail_start = min($tail_start, $message_index);
        }

        if ($tail_start <= $head_end) {
            $this->log('⚠️ auto_compact: no safe boundary outside an active tool batch');
            return;
        }

        $head = array_slice($session, 0, $head_end);
        $middle = array_slice($session, $head_end, $tail_start - $head_end);
        $tail = array_slice($session, $tail_start);
        $this->auto_compact_removed_messages = array_merge($this->auto_compact_removed_messages, $middle);

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
        $preserved_paths = [];
        $collect_paths = function (mixed $node) use (&$collect_paths, &$preserved_paths): void {
            if (is_object($node)) {
                $node = (array) $node;
            }
            if (is_array($node)) {
                foreach ($node as $value) {
                    $collect_paths($value);
                }
                return;
            }
            if (!is_string($node)) {
                return;
            }
            $text = str_replace('\\/', '/', $node);
            $candidate = trim($text);
            if (
                !str_contains($candidate, "\n") &&
                preg_match('#^(?:/(?:host|var|tmp|home|root|mnt)/|[A-Za-z]:\\\\)#u', $candidate) === 1
            ) {
                $preserved_paths[rtrim($candidate, " \t.,;:!?\"'`)]}>\\")] = true;
            }
            if (
                preg_match_all(
                    '#(?<![A-Za-z0-9])(?:/(?:host|var|tmp|home|root|mnt)/[^\r\n\"\'`<>]+|[A-Za-z]:\\\\[^\r\n\"\'`<>]+)#u',
                    $text,
                    $matches
                ) === false
            ) {
                return;
            }
            foreach ($matches[0] as $path) {
                $preserved_paths[rtrim($path, " \t.,;:!?\"'`)]}>\\")] = true;
            }
        };
        $collect_paths($middle);
        $collect_paths($this->auto_compact_summary);

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
            '- Wichtige Werte (IDs, Namen, Datumsangaben und sämtliche Dateipfade) die im weiteren Verlauf referenziert werden könnten' .
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
                    if ($text !== '' && preg_match('/^#{1,3}\s+(?:Ziel|Ausgef.uhrte Aktionen|Schl.sselwerte|Offene Punkte)\b/miu', $text) === 1) {
                        $new_summary = $text;
                    } elseif ($text !== '') {
                        $this->log('⚠️ auto_compact: summarizer returned an unusable summary');
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->log('⚠️ auto_compact summarizer error: ' . $e->getMessage());
        }

        // ---- apply result to session --------------------------------------
        if ($new_summary === null) {
            $fallback_transcript = trim($transcript);
            $fallback_limit = 24000;
            if (mb_strlen($fallback_transcript) > $fallback_limit) {
                $fallback_transcript =
                    mb_substr($fallback_transcript, 0, intdiv($fallback_limit, 2)) .
                    "\n\n[... Verlauf für die Komprimierung gekürzt ...]\n\n" .
                    mb_substr($fallback_transcript, -intdiv($fallback_limit, 2));
            }
            $prior_summary = trim((string) $this->auto_compact_summary);
            if ($prior_summary !== '' && $fallback_transcript !== '') {
                $new_summary = $prior_summary . "\n\n## Neu hinzugekommener Verlauf\n\n" . $fallback_transcript;
            }
            if ($prior_summary !== '' && $fallback_transcript === '') {
                $new_summary = $prior_summary;
            }
            if ($prior_summary === '' && $fallback_transcript !== '') {
                $new_summary = "## Ziel und Verlauf\n\n" . $fallback_transcript;
            }
        }
        if ($preserved_paths !== []) {
            $paths_section = "## Dateipfade\n" .
                implode("\n", array_map(fn($path): string => '- `' . $path . '`', array_keys($preserved_paths)));
            if ($new_summary === null) {
                $new_summary = $paths_section;
            } elseif (!str_contains($new_summary, $paths_section)) {
                $new_summary .= "\n\n" . $paths_section;
            }
        }
        if ($new_summary === null) {
            $this->log('⚠️ auto_compact: summarizer returned empty, keeping the original session');
            return;
        }
        $this->auto_compact_summary = $new_summary;
        $summary_banner =
            "[Zusammenfassung des bisherigen Verlaufs — die zwischen den initialen Instruktionen und den letzten Turns liegenden Nachrichten wurden komprimiert]\n\n" .
            $new_summary;
        $summary_message = $this->bringPromptInFormat($summary_banner);
        // Processed large payloads are represented by the summary. Keep only
        // the final unanswered tool result intact for the immediate next call.
        $pending_tail_start = count($tail);
        while ($pending_tail_start > 0) {
            $pending_entry = is_array($tail[$pending_tail_start - 1] ?? null)
                ? $tail[$pending_tail_start - 1]
                : (array) ($tail[$pending_tail_start - 1] ?? []);
            $is_pending_tool_result = ($pending_entry['role'] ?? null) === 'tool';
            $is_pending_function_result = ($pending_entry['type'] ?? null) === 'function_call_output';
            $is_pending_anthropic_result =
                ($pending_entry['role'] ?? null) === 'user' &&
                is_array($pending_entry['content'] ?? null) &&
                array_filter(
                    $pending_entry['content'],
                    static fn(mixed $block): bool =>
                        is_array($block) && ($block['type'] ?? null) === 'tool_result'
                ) !== [];
            $is_pending_google_result =
                ($pending_entry['role'] ?? null) === 'user' &&
                is_array($pending_entry['parts'] ?? null) &&
                array_filter(
                    $pending_entry['parts'],
                    static fn(mixed $part): bool => is_array($part) && isset($part['functionResponse'])
                ) !== [];
            $is_pending_image_message =
                ($pending_entry['role'] ?? null) === 'user' &&
                is_array($pending_entry['content'] ?? null) &&
                array_filter(
                    $pending_entry['content'],
                    static fn(mixed $block): bool =>
                        is_array($block) && in_array($block['type'] ?? null, ['image', 'image_url', 'input_image'], true)
                ) !== [];
            if (
                !$is_pending_tool_result &&
                !$is_pending_function_result &&
                !$is_pending_anthropic_result &&
                !$is_pending_google_result &&
                !$is_pending_image_message
            ) {
                break;
            }
            $pending_tail_start--;
        }
        $payloads_removed = 0;
        $head = self::replaceOversizedContextPayloadsWithStubs($head, $payloads_removed);
        $processed_tail = self::replaceOversizedContextPayloadsWithStubs(
            array_slice($tail, 0, $pending_tail_start),
            $payloads_removed
        );
        $tail = array_merge($processed_tail, array_slice($tail, $pending_tail_start));
        if ($payloads_removed > 0) {
            $this->log(
                '🖼️ auto_compact: stripped ' .
                    $payloads_removed .
                    ' processed large tool/image payload(s) from head/tail'
            );
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
        $is_chat_completions = in_array($this->name, ['openrouter', 'llamacpp', 'nvidia', 'cliproxyapi'], true);
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
                $tool_images = [];
                $transcript_id = null;
                $transcript_failed = false;
                if (isset($this->mcp_servers_tools_map[$tc['name']])) {
                    foreach ($this->mcp_servers_tools_map[$tc['name']]['default_arguments'] ?? [] as $key => $value) {
                        if (
                            !array_key_exists($key, $tc['arguments']) ||
                            $tc['arguments'][$key] === null ||
                            (is_string($tc['arguments'][$key]) && trim($tc['arguments'][$key]) === '')
                        ) {
                            $tc['arguments'][$key] = $value;
                        }
                    }
                    foreach ($this->mcp_servers_tools_map[$tc['name']]['forced_arguments'] ?? [] as $key => $value) {
                        $tc['arguments'][$key] = is_callable($value) ? $value($tc['arguments']) : $value;
                    }
                }
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
                    $this->emitTranscript(
                        null,
                        $this->toolTranscriptLabel((string) $tc['name'], $tc['arguments']),
                        'error',
                        'Loop guard stopped repeated identical calls.'
                    );
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
                    $this->emitTranscript(
                        null,
                        $this->toolTranscriptLabel((string) $tc['name'], $tc['arguments']),
                        'error',
                        'Loop guard stopped repeated identical results.'
                    );
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
                    $transcript_id = $this->emitTranscript(
                        null,
                        $this->toolTranscriptLabel((string) $tc['name'], $tc['arguments']),
                        'running',
                        $tc['arguments']
                    );
                    $this->log('unknown tool: ' . $tc['name'], 'local tool loop');
                    $output = 'Error: unknown tool "' . $tc['name'] . '"';
                    $transcript_failed = true;
                } else {
                    $server = $this->mcp_servers_tools_map[$tc['name']];
                    $transcript_id = $this->emitTranscript(
                        null,
                        $this->toolTranscriptLabel((string) $tc['name'], $tc['arguments']),
                        'running',
                        $tc['arguments']
                    );
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
                    $result = static::callMcpTool(
                        name: $tc['name'],
                        args: $tc['arguments'],
                        url: $server['url'],
                        authorization_token: $server['authorization_token']
                    );
                    if ($result === null) {
                        $output = 'Error: tool call failed (no response from MCP server)';
                        $transcript_failed = true;
                    } elseif (($result['result']['isError'] ?? false) === true) {
                        $output = json_encode(
                            [
                                'isError' => true,
                                'content' => $result['result']['content'] ?? []
                            ],
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
                        );
                        $transcript_failed = true;
                    } elseif (isset($result['result']['content']) && is_array($result['result']['content'])) {
                        $parts = [];
                        foreach ($result['result']['content'] as $item) {
                            if (($item['type'] ?? null) === 'image' && is_string($item['data'] ?? null)) {
                                $tool_images[] = [
                                    'data' => $item['data'],
                                    'mime_type' => $item['mimeType'] ?? $item['mime_type'] ?? 'image/png'
                                ];
                                continue;
                            }
                            $parts[] = $item['text'] ?? json_encode($item, JSON_UNESCAPED_UNICODE);
                        }
                        $output = implode("\n", $parts);
                        if ($output === '' && $tool_images !== []) {
                            $output =
                                'Tool returned ' .
                                count($tool_images) .
                                ' image' .
                                (count($tool_images) === 1 ? '' : 's') .
                                '.';
                        }
                    } else {
                        $output = json_encode($result, JSON_UNESCAPED_UNICODE);
                    }
                    $max_output_chars = min(
                        150000,
                        max(50000, (int) ($this->getContextLengthForModel() * 0.75))
                    );
                    $output = $this->truncateLocalToolOutput($output, $max_output_chars);
                    $this->log(mb_substr($output, 0, 200), 'local tool result');
                }
                $this->emitTranscript(
                    $transcript_id,
                    $this->toolTranscriptLabel((string) $tc['name'], $tc['arguments']),
                    $transcript_failed ? 'error' : 'completed',
                    $output
                );
                // record the hash of this round's output for the cumulative
                // loop-guard. this happens unconditionally (also for unknown-
                // tool errors) so a model that keeps calling a non-existent
                // tool also gets caught after $cumulative_threshold attempts.
                $result_signature = (string) $output;
                foreach ($tool_images as $tool_image) {
                    $result_signature .= '|' . $tool_image['mime_type'] . ':' . hash('sha256', $tool_image['data']);
                }
                $signature_results[$signature][] = md5($result_signature);
                $tool_results[] = [
                    'id' => $tc['id'],
                    'name' => $tc['name'],
                    'output' => $output,
                    'images' => $tool_images
                ];
            }
            $max_tool_batch_chars = min(
                150000,
                max(50000, (int) ($this->getContextLengthForModel() * 0.75))
            );
            $tool_batch_chars = array_sum(
                array_map(fn(array $result): int => mb_strlen($result['output']), $tool_results)
            );
            if ($tool_batch_chars > $max_tool_batch_chars && $tool_results !== []) {
                $remaining_indices = array_keys($tool_results);
                $remaining_chars = $max_tool_batch_chars;
                while ($remaining_indices !== []) {
                    $max_result_chars = max(1, intdiv($remaining_chars, count($remaining_indices)));
                    $oversized_indices = [];
                    foreach ($remaining_indices as $result_index) {
                        $result_length = mb_strlen($tool_results[$result_index]['output']);
                        if ($result_length > $max_result_chars) {
                            $oversized_indices[] = $result_index;
                            continue;
                        }
                        $remaining_chars -= $result_length;
                    }
                    if (count($oversized_indices) === count($remaining_indices)) {
                        foreach ($oversized_indices as $result_index) {
                            $tool_results[$result_index]['output'] = $this->truncateLocalToolOutput(
                                $tool_results[$result_index]['output'],
                                $max_result_chars
                            );
                        }
                        break;
                    }
                    $remaining_indices = $oversized_indices;
                }
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
                    foreach ($tr['images'] ?? [] as $tool_image) {
                        $response_parts[] = [
                            'inlineData' => [
                                'mimeType' => $tool_image['mime_type'],
                                'data' => $tool_image['data']
                            ]
                        ];
                    }
                }
                self::$sessions[$this->session_id][] = [
                    'role' => 'user',
                    'parts' => $response_parts
                ];
            } elseif ($is_anthropic) {
                $result_blocks = [];
                foreach ($tool_results as $tr) {
                    $result_content = [['type' => 'text', 'text' => $tr['output']]];
                    foreach ($tr['images'] ?? [] as $tool_image) {
                        $result_content[] = [
                            'type' => 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => $tool_image['mime_type'],
                                'data' => $tool_image['data']
                            ]
                        ];
                    }
                    $result_blocks[] = [
                        'type' => 'tool_result',
                        'tool_use_id' => $tr['id'],
                        'content' => $result_content
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
                $image_content = [];
                foreach ($tool_results as $tr) {
                    foreach ($tr['images'] ?? [] as $tool_image) {
                        $image_content[] = [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' =>
                                    'data:' . $tool_image['mime_type'] . ';base64,' . $tool_image['data'],
                                'detail' => 'auto'
                            ]
                        ];
                    }
                }
                if ($image_content !== []) {
                    array_unshift($image_content, [
                        'type' => 'text',
                        'text' => 'Images returned by the preceding tool call.'
                    ]);
                    self::$sessions[$this->session_id][] = [
                        'role' => 'user',
                        'content' => $image_content
                    ];
                }
            } else {
                foreach ($tool_results as $tr) {
                    $function_output = [['type' => 'input_text', 'text' => $tr['output']]];
                    foreach ($tr['images'] ?? [] as $tool_image) {
                        $function_output[] = [
                            'type' => 'input_image',
                            'image_url' =>
                                'data:' . $tool_image['mime_type'] . ';base64,' . $tool_image['data'],
                            'detail' => 'auto'
                        ];
                    }
                    self::$sessions[$this->session_id][] = [
                        'type' => 'function_call_output',
                        'call_id' => $tr['id'],
                        'output' => $function_output
                    ];
                }
            }
            $this->stubOversizedFileBlocks();
            $this->emitStreamBlockSeparator();

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
            $extra_transient_retries = max(0, 3 - $this->max_tries);
            $extra_availability_retries = max(0, 9 - $this->max_tries);
            $transient_retry = false;
            $availability_retry = false;
            $attempt = 0;
            while ($return['success'] === false && $max_tries > 0) {
                if ($attempt > 0) {
                    $backoff_s = $this->retryBackoffSeconds($attempt, $transient_retry, $availability_retry);
                    $this->log('⚠️ tries left: ' . $max_tries . ' — backoff ' . $backoff_s . 's');
                    if ($backoff_s > 0) {
                        sleep($backoff_s);
                    }
                }
                $transient_retry = false;
                $availability_retry = false;
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
                    } elseif ($this->isTransientRequestError($e->getMessage())) {
                        $this->log('⚠️ transient request error (tool-loop) — retrying: ' . $e->getMessage());
                        $return = [
                            'response' => $e->getMessage(),
                            'success' => false,
                            'costs' => $return['costs'] ?? 0.0
                        ];
                        $transient_retry = true;
                    } else {
                        throw $e;
                    }
                }
                $retryResponse = '';
                if ($return['success'] === false && $this->isTransientRequestError($return['response'] ?? '')) {
                    $transient_retry = true;
                    $retryResponse = is_string($return['response'] ?? null)
                        ? strtolower($return['response'])
                        : strtolower(
                            json_encode(
                                $return['response'] ?? null,
                                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                            ) ?: ''
                        );
                    $availability_retry =
                        str_contains($retryResponse, 'auth_unavailable') ||
                        str_contains($retryResponse, 'no auth available') ||
                        str_contains($retryResponse, 'connection refused') ||
                        str_contains($retryResponse, 'no response from provider') ||
                        str_contains($retryResponse, 'too many concurrent requests') ||
                        str_contains($retryResponse, 'temporarily unavailable') ||
                        str_contains($retryResponse, 'overloaded') ||
                        str_contains($retryResponse, 'upstream connect error') ||
                        str_contains($retryResponse, 'connection termination') ||
                        str_contains($retryResponse, 'http 520') ||
                        str_contains($retryResponse, 'error code: 520') ||
                        str_contains($retryResponse, '(http 0)');
                }
                if ($availability_retry) {
                    $maximum_availability_tries =
                        str_contains($retryResponse, 'auth_unavailable') ||
                        str_contains($retryResponse, 'no auth available')
                            ? 3
                            : 9;
                    if ($attempt + 1 < $maximum_availability_tries && $extra_availability_retries > 0) {
                        $extra_availability_retries--;
                        $max_tries++;
                    }
                } elseif ($transient_retry && $extra_transient_retries > 0) {
                    $extra_transient_retries--;
                    $max_tries++;
                }
                $this->log($return, 'local tool loop return');
                $attempt++;
                $max_tries--;
            }
            $max_tool_rounds--;
        }
        return $return;
    }

    // read a key from a block that may be an array or a stdClass (rehydrated history)
    private function blockGet(mixed $container, string $key): mixed
    {
        if (is_object($container)) {
            return $container->$key ?? null;
        }
        return is_array($container) ? $container[$key] ?? null : null;
    }

    // detect an inline file/image block across the provider dialects. returns
    // [payload, filename|null, stub-factory] or null when it is not one.
    private function detectInlineFileBlock(mixed $block): ?array
    {
        $type = $this->blockGet($block, 'type');
        // openai responses api
        if ($type === 'input_file' || $type === 'input_image') {
            $payload =
                $type === 'input_file' ? $this->blockGet($block, 'file_data') : $this->blockGet($block, 'image_url');
            return [
                $payload,
                $this->blockGet($block, 'filename'),
                fn(string $text) => ['type' => 'input_text', 'text' => $text]
            ];
        }
        // chat completions (openrouter/llamacpp/nvidia/cliproxyapi)
        if ($type === 'file') {
            $file = $this->blockGet($block, 'file');
            return [
                $this->blockGet($file, 'file_data'),
                $this->blockGet($file, 'filename'),
                fn(string $text) => ['type' => 'text', 'text' => $text]
            ];
        }
        if ($type === 'image_url') {
            $url = $this->blockGet($block, 'image_url');
            return [
                is_string($url) ? $url : $this->blockGet($url, 'url'),
                null,
                fn(string $text) => ['type' => 'text', 'text' => $text]
            ];
        }
        // anthropic
        if (($type === 'document' || $type === 'image') && $this->blockGet($block, 'source') !== null) {
            return [
                $this->blockGet($this->blockGet($block, 'source'), 'data'),
                null,
                fn(string $text) => ['type' => 'text', 'text' => $text]
            ];
        }
        // google
        $inline = $this->blockGet($block, 'inline_data') ?? $this->blockGet($block, 'inlineData');
        if ($inline !== null) {
            return [$this->blockGet($inline, 'data'), null, fn(string $text) => ['text' => $text]];
        }
        return null;
    }

    // large inlined files (base64 pdfs/images) only need to reach the model once;
    // afterwards they burn context on every follow-up request (a 44mb pdf ≈ 270k
    // tokens). replace any that exceed $max_chars with a short text stub.
    protected function stubOversizedFileBlocks(int $max_chars = 1000000): void
    {
        if (empty($this->session_id) || empty(self::$sessions[$this->session_id])) {
            return;
        }
        $session = &self::$sessions[$this->session_id];
        foreach ($session as &$entry) {
            if ($this->blockGet($entry, 'role') !== 'user') {
                continue;
            }
            foreach (['content', 'parts'] as $key) {
                $blocks = $this->blockGet($entry, $key);
                if (!is_array($blocks)) {
                    continue;
                }
                $changed = false;
                foreach ($blocks as $index => $block) {
                    $detected = $this->detectInlineFileBlock($block);
                    if ($detected === null) {
                        continue;
                    }
                    [$payload, $filename, $stub] = $detected;
                    if (!is_string($payload) || strlen($payload) <= $max_chars) {
                        continue;
                    }
                    $blocks[$index] = $stub($this->stubbedAttachmentLabel($filename));
                    $changed = true;
                }
                if ($changed) {
                    if (is_object($entry)) {
                        $entry->$key = $blocks;
                    } else {
                        $entry[$key] = $blocks;
                    }
                }
            }
        }
        unset($entry);
    }

    private function stubbedAttachmentLabel(mixed $filename): string
    {
        $named = is_string($filename) && $filename !== '' ? ' "' . $filename . '"' : '';
        return '[attachment' .
            $named .
            ' removed from context to save tokens — its full content was already provided in an earlier request of this conversation]';
    }

    // in the live stream, separate a follow-up text turn from the previous one
    // with a blank line (pre- vs post-tool narration): the per-turn parser strips
    // leading newlines and never forwards the tool boundary, so text blocks would
    // otherwise glue ("…ausgewertet.Läuft").
    protected function emitStreamBlockSeparator(): void
    {
        if ($this->stream !== true || $this->stream_text_emitted_since_tool !== true) {
            return;
        }
        echo 'data: ' . json_encode(['choices' => [['delta' => ['content' => "\n\n"]]]]) . "\n\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        $this->stream_text_emitted_since_tool = false;
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
            $fetch_failed = [];
            foreach ($handles as $h) {
                $response = curl_multi_getcontent($h['ch']);
                $httpCode = curl_getinfo($h['ch'], CURLINFO_HTTP_CODE);
                curl_multi_remove_handle($mh, $h['ch']);
                $toolsData = null;
                $failureReason = '(http ' . $httpCode . ')';
                for ($attempt = 0; $attempt < 3; $attempt++) {
                    if ($attempt > 0) {
                        usleep($attempt * 500000);
                        $response = curl_exec($h['ch']);
                        $httpCode = curl_getinfo($h['ch'], CURLINFO_HTTP_CODE);
                    }
                    $curlError = curl_error($h['ch']);
                    if ($httpCode < 200 || $httpCode >= 300 || !is_string($response) || $response === '') {
                        $failureReason =
                            '(http ' . $httpCode . ($curlError !== '' ? ', ' . $curlError : '') . ')';
                        $retryable =
                            $httpCode === 0 ||
                            $httpCode === 408 ||
                            $httpCode === 425 ||
                            $httpCode === 429 ||
                            ($httpCode >= 500 && $httpCode <= 599);
                        if ($retryable && $attempt < 2) {
                            continue;
                        }
                        break;
                    }
                    if (strpos($response, 'event: message') !== false) {
                        $matches = [];
                        preg_match('/^data: (.+)$/m', $response, $matches);
                        if (isset($matches[1])) {
                            $response = trim($matches[1]);
                        }
                    }
                    $toolsData = json_decode($response, true);
                    if (isset($toolsData['result']['tools']) && is_array($toolsData['result']['tools'])) {
                        break;
                    }
                    $failureReason = '(unparseable tools/list response)';
                    $toolsData = null;
                }
                if ($toolsData === null) {
                    $fetch_failed[] = ($h['mcp']['url'] ?? '?') . ' ' . $failureReason;
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
                        'default_arguments' => $h['mcp']['default_tool_arguments'][$tool['name']] ?? [],
                        'forced_arguments' => $h['mcp']['forced_tool_arguments'][$tool['name']] ?? [],
                        'schema' => $tool_def
                    ];
                }
            }
            curl_multi_close($mh);
            // fail loudly instead of silently sending a request without tools
            if (empty($this->mcp_servers_tools_map) && !empty($handles)) {
                throw new \Exception(
                    $fetch_failed !== []
                        ? 'tools/list failed for all MCP servers: ' . implode(', ', $fetch_failed)
                        : 'MCP servers responded but no usable tools remained (check allowed_tools filters)'
                );
            }
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

    public function fetchModels(): array
    {
        $raw = $this->fetchModelsFromModelsDev();
        // merge models
        if (method_exists($this, 'fetchModelsFromProvider')) {
            $known = [];
            foreach ($raw as $raw__value) {
                if (isset($raw__value['name'])) {
                    $known[$raw__value['name']] = true;
                }
            }
            foreach ($this->fetchModelsFromProvider() as $provider__value) {
                if (!isset($provider__value['name'])) {
                    continue;
                }
                if (isset($known[$provider__value['name']])) {
                    continue;
                }
                $raw[] = $provider__value;
            }
        }
        $models = $this->normalizeAndEnrichModels($raw);
        if (!empty($models)) {
            return $models;
        }
        return $this->normalizeAndEnrichModels($this->models);
    }

    protected function normalizeAndEnrichModels(array $models): array
    {
        $normalized_models = [];
        foreach ($models as $model) {
            if (!isset($model['name'])) {
                continue;
            }
            $normalized_models[] = [
                'name' => $model['name'],
                'owned_by' => $model['owned_by'] ?? null,
                'context_length' => $model['context_length'] ?? 128000,
                'max_output_tokens' => $model['max_output_tokens'] ?? 16384,
                'costs' => $model['costs'] ?? ['input' => 0, 'input_cached' => 0, 'output' => 0],
                'supports_temperature' => $model['supports_temperature'] ?? true,
                'supports_tools' => $model['supports_tools'] ?? true,
                'supports_text_to_image' => $model['supports_text_to_image'] ?? false,
                'supports_text_to_audio' => $model['supports_text_to_audio'] ?? false,
                'supports_image_to_text' => $model['supports_image_to_text'] ?? false,
                'supports_audio_to_text' => $model['supports_audio_to_text'] ?? false,
                'supports_effort' => $model['supports_effort'] ?? false,
                'efforts' => $model['efforts'] ?? [],
                'effort_budget_min' => $model['effort_budget_min'] ?? null,
                'effort_budget_max' => $model['effort_budget_max'] ?? null,
                'open_weights' => isset($model['open_weights']) ? (bool) $model['open_weights'] : false,
                'supported_parameters' => $model['supported_parameters'] ?? [],
                'artificial_analysis_intelligence_index' => $model['artificial_analysis_intelligence_index'] ?? null,
                'artificial_analysis_coding_index' => $model['artificial_analysis_coding_index'] ?? null,
                'artificial_analysis_agentic_index' => $model['artificial_analysis_agentic_index'] ?? null,
                'artificial_analysis_output_speed' => $model['artificial_analysis_output_speed'] ?? null,
                'artificial_analysis_time_to_first_token' => $model['artificial_analysis_time_to_first_token'] ?? null,
                'artificial_analysis_time_to_first_answer_token' =>
                    $model['artificial_analysis_time_to_first_answer_token'] ?? null,
                'artificial_analysis_response_time' => $model['artificial_analysis_response_time'] ?? null,
                'artificial_analysis_index_cost' => $model['artificial_analysis_index_cost'] ?? null,
                'default' => isset($model['default']) ? $model['default'] : false,
                'test' => isset($model['test']) ? $model['test'] : false
            ];
        }

        $artificial_analysis_api_key = $_SERVER['ARTIFICIAL_ANALYSIS_API_KEY'] ?? null;
        if ($artificial_analysis_api_key === null || $artificial_analysis_api_key === '') {
            $artificial_analysis_api_key = $_ENV['ARTIFICIAL_ANALYSIS_API_KEY'] ?? null;
        }
        if ($artificial_analysis_api_key === null || $artificial_analysis_api_key === '') {
            $artificial_analysis_api_key = getenv('ARTIFICIAL_ANALYSIS_API_KEY') ?: null;
        }
        if ($artificial_analysis_api_key === null || $artificial_analysis_api_key === '') {
            return $normalized_models;
        }

        if (self::$artificial_analysis_models === null) {
            self::$artificial_analysis_models = [];
            $response = __::curl(
                url: 'https://artificialanalysis.ai/api/v2/data/llms/models',
                method: 'GET',
                headers: ['x-api-key' => $artificial_analysis_api_key],
                timeout: $this->timeout
            );
            if (__::x($response?->result?->data ?? null) && is_array($response->result->data)) {
                foreach ($response->result->data as $model_data) {
                    foreach ([$model_data->slug ?? '', $model_data->name ?? ''] as $value) {
                        foreach ($this->getModelMatchingKeys((string) $value) as $model_key) {
                            if (!isset(self::$artificial_analysis_models[$model_key])) {
                                self::$artificial_analysis_models[$model_key] = $model_data;
                            }
                        }
                    }
                }
            }
        }

        foreach ($normalized_models as $model_key => $model) {
            foreach ($this->getModelMatchingKeys((string) ($model['name'] ?? '')) as $matching_key) {
                if (!isset(self::$artificial_analysis_models[$matching_key])) {
                    continue;
                }
                $artificial_analysis_model = self::$artificial_analysis_models[$matching_key];
                $normalized_models[$model_key]['artificial_analysis_intelligence_index'] =
                    $artificial_analysis_model->evaluations->artificial_analysis_intelligence_index ?? null;
                $normalized_models[$model_key]['artificial_analysis_coding_index'] =
                    $artificial_analysis_model->evaluations->artificial_analysis_coding_index ?? null;
                $normalized_models[$model_key]['artificial_analysis_agentic_index'] =
                    $artificial_analysis_model->evaluations->artificial_analysis_agentic_index ?? null;
                $normalized_models[$model_key]['artificial_analysis_output_speed'] =
                    $artificial_analysis_model->median_output_tokens_per_second ??
                    ($artificial_analysis_model->performance->median_output_tokens_per_second ?? null);
                $normalized_models[$model_key]['artificial_analysis_time_to_first_token'] =
                    $artificial_analysis_model->median_time_to_first_token_seconds ??
                    ($artificial_analysis_model->performance->median_time_to_first_token_seconds ?? null);
                $normalized_models[$model_key]['artificial_analysis_time_to_first_answer_token'] =
                    $artificial_analysis_model->median_time_to_first_answer_token ??
                    ($artificial_analysis_model->performance->median_time_to_first_answer_token_seconds ?? null);
                $normalized_models[$model_key]['artificial_analysis_response_time'] =
                    $artificial_analysis_model->median_end_to_end_response_time_seconds ??
                    ($artificial_analysis_model->performance->median_end_to_end_response_time_seconds ?? null);
                $normalized_models[$model_key]['artificial_analysis_index_cost'] =
                    $artificial_analysis_model->artificial_analysis_intelligence_index_cost->total_cost ?? null;
                break;
            }
        }

        return $normalized_models;
    }

    protected function getModelMatchingKeys(string $model): array
    {
        $model = strtolower($model);
        $model = preg_replace('/:[a-z0-9_-]+$/i', '', $model) ?? $model;
        $values = [$model];
        if (str_contains($model, '/')) {
            $values[] = substr($model, (int) strrpos($model, '/') + 1);
        }
        $values[] = preg_replace('/-\d{4}-\d{2}-\d{2}$/', '', end($values)) ?? end($values);
        $values[] = preg_replace('/-(high|medium|low|minimal|max)$/', '', end($values)) ?? end($values);
        $values[] = preg_replace('/\s*\((xhigh|high|medium|low|minimal|max|adaptive[^)]*)\)$/i', '', $model) ?? $model;

        $keys = [];
        foreach ($values as $value) {
            $key = preg_replace('/[^a-z0-9]+/', '', $value) ?? '';
            if ($key !== '') {
                $keys[$key] = true;
            }
        }
        return array_keys($keys);
    }

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
        if (method_exists($this, 'fetchModelsFromProvider')) {
            return !empty($this->fetchModelsFromProvider());
        }
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

    protected function getEffortValues(): array
    {
        return ['none', 'minimal', 'low', 'medium', 'high', 'xhigh', 'max'];
    }

    protected function getEffortForRequest(): ?string
    {
        if ($this->effort === null) {
            return null;
        }
        foreach ($this->models as $model) {
            if (($model['name'] ?? null) !== $this->model) {
                continue;
            }
            if (($model['supports_effort'] ?? false) !== true) {
                return null;
            }
            $efforts = $model['efforts'] ?? [];
            if (!empty($efforts) && !in_array($this->effort, $efforts, true)) {
                return null;
            }
            return $this->effort;
        }
        return null;
    }

    protected function getEffortLevel(?string $effort = null): string
    {
        $effort = $effort ?? $this->effort;
        return match ($effort) {
            'none', 'minimal', 'low' => 'low',
            'medium' => 'medium',
            default => 'high'
        };
    }

    protected function getEffortBudgetTokens(?string $effort = null): int
    {
        $effort = $effort ?? $this->effort;
        return match ($effort) {
            'none' => 0,
            'minimal' => 512,
            'low' => 1024,
            'medium' => 4096,
            'high' => 10000,
            'xhigh' => 20000,
            'max' => 32000,
            default => 1024
        };
    }

    protected function getEffortBudgetTokensForRequest(?string $effort = null): int
    {
        $budget = $this->getEffortBudgetTokens($effort);
        foreach ($this->models as $model) {
            if (($model['name'] ?? null) !== $this->model) {
                continue;
            }
            if ($budget > 0 && isset($model['effort_budget_min']) && $model['effort_budget_min'] !== null) {
                $budget = max((int) $model['effort_budget_min'], $budget);
            }
            if (isset($model['effort_budget_max']) && $model['effort_budget_max'] !== null) {
                $budget = min((int) $model['effort_budget_max'], $budget);
            }
            break;
        }
        return $budget;
    }

    protected function trimPrompt(string $prompt): string
    {
        return __::trim_whitespace(__::trim_indentation($prompt));
    }

    abstract protected function bringPromptInFormat(string $prompt, mixed $files = null): array;

    abstract protected function addResponseToSession(mixed $response): void;

    protected function truncateLocalToolOutput(string $output, int $max_length): string
    {
        $original_length = mb_strlen($output);
        $persisted_path = null;
        if (
            preg_match(
                '#complete structured result persisted at ([^;\]]*aihelper-tool-results[^;\]]+)#',
                $output,
                $persisted_path_match
            ) === 1
        ) {
            $persisted_path = $persisted_path_match[1];
        }
        $decoded = json_decode(trim($output), true);
        if (is_array($decoded)) {
            $compact = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if (is_string($compact)) {
                $output = $compact;
            }
        }
        if (mb_strlen($output) <= $max_length) {
            return $output;
        }

        if ($persisted_path === null && is_array($decoded) && $this->session_id !== null && $this->session_id !== '') {
            $result_directory =
                sys_get_temp_dir() .
                '/aihelper-tool-results/' .
                preg_replace('/[^a-zA-Z0-9_-]/', '_', $this->session_id);
            if ((is_dir($result_directory) || mkdir($result_directory, 0700, true)) && is_writable($result_directory)) {
                chmod($result_directory, 0700);
                $persisted_path = $result_directory . '/' . hash('sha256', $output) . '.json';
                if (
                    (!is_file($persisted_path) && file_put_contents($persisted_path, $output, LOCK_EX) === false) ||
                    !chmod($persisted_path, 0600)
                ) {
                    $persisted_path = null;
                }
            }
        }

        $append_marker = static function (string $trimmed) use (
            $max_length,
            $original_length,
            $persisted_path
        ): string {
            $marker = "\n\n[... truncated from $original_length chars";
            if ($persisted_path !== null) {
                $marker .= '; complete structured result persisted at ' . $persisted_path;
            }
            $marker .= ']';
            if (mb_strlen($marker) >= $max_length) {
                return mb_substr($marker, 0, $max_length);
            }
            return mb_substr($trimmed, 0, $max_length - mb_strlen($marker)) . $marker;
        };

        if (is_array($decoded)) {
            $truncate_json = function (mixed $data, int $max_string = 500, int $max_items = 5) use (
                &$truncate_json
            ): mixed {
                if (is_array($data) && array_is_list($data)) {
                    $sliced = array_map(
                        fn(mixed $value): mixed => $truncate_json($value, $max_string, $max_items),
                        array_slice($data, 0, $max_items)
                    );
                    if (count($data) > $max_items) {
                        $sliced[] =
                            '[... ' . (count($data) - $max_items) . ' more items, ' . count($data) . ' total]';
                    }
                    return $sliced;
                }
                if (is_array($data)) {
                    return array_map(
                        fn(mixed $value): mixed => $truncate_json($value, $max_string, $max_items),
                        $data
                    );
                }
                if (is_string($data) && mb_strlen($data) > $max_string) {
                    return mb_substr($data, 0, $max_string) . '... [' . mb_strlen($data) . ' chars]';
                }
                return $data;
            };
            $trimmed = json_encode(
                $truncate_json($decoded),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
            if (is_string($trimmed)) {
                return $append_marker($trimmed);
            }
        }

        return $append_marker($output);
    }

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
        $configured_effort = $this->effort;
        if ($configured_effort !== null) {
            $enable_thinking = $configured_effort !== 'none';
        }
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
                    $args['chat_template_kwargs'] += [
                        'thinking_budget' =>
                            $configured_effort !== null
                                ? $this->getEffortBudgetTokensForRequest($configured_effort)
                                : 2000
                    ];
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
                    'thinking_budget_tokens' =>
                        $configured_effort !== null ? $this->getEffortBudgetTokensForRequest($configured_effort) : 4000
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
                    'thinking_budget_tokens' =>
                        $configured_effort !== null ? $this->getEffortBudgetTokensForRequest($configured_effort) : 4000
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

    public function getAutoCompactRemovedMessages(): array
    {
        return $this->auto_compact_removed_messages;
    }

    // only a harness can use them: they are placed on disk for the cli to discover.
    // every other provider carries the same knowledge inside its system prompt
    public function setCliSkills(array $skills): static
    {
        $this->cli_skills = $skills;
        return $this;
    }

    // harnesses render it as a real system prompt, everyone else gets it as
    // the first message of the session
    public function setSystemPrompt(string $prompt): static
    {
        $prompt = $this->trimPrompt($prompt);
        if ($prompt === '') {
            return $this;
        }
        $this->system_prompt = $prompt;
        if ($this->is_harness !== true) {
            $this->prependPromptToSession($prompt);
        }
        return $this;
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
            $sensitiveValues = [];
            foreach ($this->mcp_servers ?? [] as $mcpServer) {
                $authorizationToken = $mcpServer['authorization_token'] ?? null;
                if (is_string($authorizationToken) && $authorizationToken !== '') {
                    $sensitiveValues[] = $authorizationToken;
                }
            }
            $redact = function (mixed $value) use (&$redact, $sensitiveValues): mixed {
                if (is_object($value)) {
                    $value = (array) $value;
                }
                if (is_array($value)) {
                    $contentType = strtolower((string) ($value['type'] ?? ''));
                    $mimeType = $value['mimeType'] ?? $value['mime_type'] ?? $value['media_type'] ?? null;
                    $binaryData = $value['data'] ?? null;
                    foreach ($value as $key => $item) {
                        if (
                            is_string($key) &&
                            preg_match(
                                '/^(?:authorization|authorization_token|access_token|refresh_token|id_token|client_secret|token|secret|password|passwd|passphrase|api[_-]?key|private[_-]?key|access[_-]?key|cookie)$/i',
                                $key
                            ) === 1
                        ) {
                            $value[$key] = '***';
                            continue;
                        }
                        if (
                            $key === 'data' &&
                            in_array($contentType, ['audio', 'image', 'base64'], true) &&
                            is_string($binaryData) &&
                            is_string($mimeType)
                        ) {
                            $payload = strtr($binaryData, '-_', '+/');
                            $payload .= str_repeat('=', (4 - (strlen($payload) % 4)) % 4);
                            $binary = base64_decode($payload, true);
                            if ($binary === false) {
                                $binary = $binaryData;
                            }
                            $value[$key] = sprintf(
                                '[binary data omitted: mime=%s bytes=%d sha256=%s]',
                                $mimeType,
                                strlen($binary),
                                hash('sha256', $binary)
                            );
                            continue;
                        }
                        $value[$key] = $redact($item);
                    }
                    return $value;
                }
                if (!is_string($value)) {
                    return $value;
                }
                if ($sensitiveValues !== []) {
                    $value = str_replace($sensitiveValues, '***', $value);
                }
                $trimmedValue = trim($value);
                if (
                    ($trimmedValue[0] ?? '') === '{' ||
                    ($trimmedValue[0] ?? '') === '['
                ) {
                    $decodedValue = json_decode($value, true);
                    if (is_array($decodedValue)) {
                        return json_encode(
                            $redact($decodedValue),
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        );
                    }
                }
                return preg_replace(
                    '/(?i)(\bAIHELPER_MCP_TOKEN_[A-Z0-9_]+\s*=\s*)(?:"(?:\\.|[^"])*"|\'(?:\\.|[^\'])*\'|[^\s,;]+)/',
                    '$1***',
                    $value
                ) ?? $value;
            };
            $msg = $redact($msg);
            if (!is_string($msg)) {
                $msg = json_encode($msg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
            $msg = preg_replace(
                '/(?i)(authorization\s*[:=]\s*bearer\s+)[^\s,\'"}]+/',
                '$1***',
                $msg
            );
            $msg = preg_replace(
                '/(?i)("?(?:access_token|refresh_token|id_token|client_secret|token|secret|password|api[_-]?key)"?\s*[:=]\s*")([^"]+)(")/',
                '$1***$3',
                $msg
            );
            $msg = preg_replace(
                '/(?i)(\b[a-z0-9_]*(?:access_token|refresh_token|id_token|client_secret|token|secret|password|passwd|passphrase|api[_-]?key|private[_-]?key|access[_-]?key|authorization|cookie)\b\s*[:=]\s*)(?:"[^"]*"|\'[^\']*\'|[^\s\\,;]+)/',
                '$1***',
                $msg
            );
            $msg = preg_replace_callback(
                '/data:(?<mime>[A-Za-z0-9.+-]+\/[A-Za-z0-9.+-]+)(?:;[^,\s]*)?;base64,(?<payload>[A-Za-z0-9+\/=\-_]+)/i',
                static function (array $matches): string {
                    $payload = strtr($matches['payload'], '-_', '+/');
                    $payload .= str_repeat('=', (4 - (strlen($payload) % 4)) % 4);
                    $binary = base64_decode($payload, true);
                    if ($binary === false) {
                        $binary = $matches['payload'];
                    }
                    return sprintf(
                        '[binary data omitted: mime=%s bytes=%d sha256=%s]',
                        $matches['mime'],
                        strlen($binary),
                        hash('sha256', $binary)
                    );
                },
                $msg
            );
            $msg = preg_replace_callback(
                '/(?<prefix>\\\\?"data\\\\?"\s*:\s*\\\\?")(?<payload>[A-Za-z0-9+\/=\-_]+)(?<middle>\\\\?"\s*,\s*\\\\?"(?:mimeType|mime_type)\\\\?"\s*:\s*\\\\?")(?<mime>[A-Za-z0-9.+-]+\/[A-Za-z0-9.+-]+)(?<suffix>\\\\?")/i',
                static function (array $matches): string {
                    $payload = strtr($matches['payload'], '-_', '+/');
                    $payload .= str_repeat('=', (4 - (strlen($payload) % 4)) % 4);
                    $binary = base64_decode($payload, true);
                    if ($binary === false) {
                        $binary = $matches['payload'];
                    }
                    return
                        $matches['prefix'] .
                        sprintf(
                            '[binary data omitted: mime=%s bytes=%d sha256=%s]',
                            $matches['mime'],
                            strlen($binary),
                            hash('sha256', $binary)
                        ) .
                        $matches['middle'] .
                        $matches['mime'] .
                        $matches['suffix'];
                },
                $msg
            );
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
        $this->stream_block_offset = 0;
        $this->stream_first_text_sent = false;
        $this->stream_running = false;
        $this->stream_in_think = false;
        $this->stream_think_tag_buf = '';
        $this->stream_callback = null;

        // the cli harnesses hand over literal anthropic streaming events
        if (in_array($this->name, ['anthropic', 'test', 'claudecode', 'codex', 'opencode'], true)) {
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

                            // anchor the block indices of the message that starts here
                            if (isset($parsed['type']) && $parsed['type'] === 'message_start') {
                                $this->stream_block_offset = count($this->stream_response->result->content ?? []);
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
                                $index = $this->stream_block_offset + ($parsed['index'] ?? 0);
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
                                        $this->stream_text_emitted_since_tool = true;

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
                                $index = isset($parsed['index'])
                                    ? $this->stream_block_offset + $parsed['index']
                                    : count($this->stream_response->result->content) - 1;

                                // parse partial_json input to real json object for tool_use/mcp_tool_use blocks
                                if (isset($this->stream_response->result->content[$index])) {
                                    $block = &$this->stream_response->result->content[$index];
                                    // a tool without arguments never sends a partial_json, so its
                                    // input is still the empty array json_decode made of "{}"
                                    if (is_array($block->input ?? null) && $block->input === []) {
                                        $block->input = new \stdClass();
                                    }
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
                                    $this->stream_text_emitted_since_tool = true;

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
            $this->name === 'cliproxyapi'
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
                        $this->stream_text_emitted_since_tool = true;
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

                $parsedChunk = json_decode(trim($chunk), true);
                if (is_array($parsedChunk) && isset($parsedChunk['error'])) {
                    $this->stream_response->result->error = (object) [
                        'message' => self::extractErrorMessage($parsedChunk['error'])
                    ];
                    return strlen($chunk);
                }

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
                                    $this->stream_text_emitted_since_tool = true;
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

    /**
     * Stream one provider-independent process entry while keeping complete payloads in structured content.
     */
    protected function emitTranscript(
        ?string $id,
        string $label,
        string $status,
        mixed $detail = null
    ): string
    {
        $id = trim((string) $id);
        if ($id === '') {
            $id = 'transcript-' . md5(uniqid('', true));
        }
        $label = trim($label) !== '' ? trim($label) : ($this->transcript_labels[$id] ?? 'Activity');
        $this->transcript_labels[$id] = $label;
        $previousStatus = $this->transcript_states[$id] ?? null;
        if ($previousStatus === $status) {
            return $id;
        }
        $this->transcript_states[$id] = $status;
        if ($this->stream !== true) {
            return $id;
        }

        if (
            (is_array($detail) && $detail === []) ||
            (is_object($detail) && get_object_vars($detail) === [])
        ) {
            $detail = null;
        }
        $detailText = '';
        if ($detail !== null) {
            $detailText = is_string($detail)
                ? $detail
                : (json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
            foreach ($this->mcp_servers ?? [] as $mcpServer) {
                $token = $mcpServer['authorization_token'] ?? null;
                if (is_string($token) && $token !== '') {
                    $detailText = str_replace($token, '***', $detailText);
                }
            }
            $detailText = preg_replace(
                '/(?i)(authorization\s*[:=]\s*bearer\s+)[^\s,\'"}]+/',
                '$1***',
                $detailText
            ) ?? $detailText;
            $detailText = preg_replace(
                '/(?i)("?(?:access_token|refresh_token|id_token|client_secret|token|secret|password|passwd|passphrase|api[_-]?key|private[_-]?key|access[_-]?key|cookie)"?\s*[:=]\s*)(?:"[^"]*"|\'[^\']*\'|[^\s,;}]+)/',
                '$1***',
                $detailText
            ) ?? $detailText;
            $detailText = preg_replace(
                '/(?i)(\bAIHELPER_MCP_TOKEN_[A-Z0-9_]+\s*=\s*)(?:"(?:\\.|[^"])*"|\'(?:\\.|[^\'])*\'|[^\s,;]+)/',
                '$1***',
                $detailText
            ) ?? $detailText;
            $detailText = preg_replace(
                '/("data"\s*:\s*")[A-Za-z0-9+\/=\-_]{256,}"/',
                '$1[binary data omitted]"',
                $detailText
            ) ?? $detailText;
            $detailText = preg_replace(
                '/data:[A-Za-z0-9.+-]+\/[A-Za-z0-9.+-]+(?:;[^,\s]*)?;base64,[A-Za-z0-9+\/=\-_]+/i',
                '[binary data omitted]',
                $detailText
            ) ?? $detailText;
            $detailText = trim(str_replace(["\r\n", "\r"], "\n", $detailText));
            if (mb_strlen($detailText) > 6000) {
                $omitted = mb_strlen($detailText) - 5000;
                $detailText =
                    mb_substr($detailText, 0, 4000) .
                    "\n… " .
                    $omitted .
                    " characters omitted …\n" .
                    mb_substr($detailText, -1000);
            }
        }

        $started = $previousStatus === null;
        $delta = $started ? "\n\n• " . $label : '';
        if ($detailText !== '') {
            $prefix = $status === 'running' ? '  ├ ' : '  └ ';
            $delta .= "\n" . $prefix . str_replace("\n", "\n    ", $detailText);
        } elseif ($status === 'error') {
            $delta .= "\n  └ Failed.";
        }
        if ($delta === '') {
            return $id;
        }

        echo "event: reasoning\n";
        echo 'data: ' .
            json_encode(
                [
                    'delta' => $delta,
                    'kind' => 'transcript',
                    'boundary' => $started
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ) .
            "\n\n";
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        return $id;
    }

    /**
     * Turn a technical tool invocation into the same concise wording used by coding CLIs.
     */
    protected function toolTranscriptLabel(string $name, mixed $input = null): string
    {
        $label = preg_replace('/^mcp__/', '', trim($name));
        $label = preg_replace('/__+/', ' · ', (string) $label);
        $label = preg_replace('/_+/', ' ', (string) $label);
        $label = ucfirst(trim((string) $label)) ?: 'Tool';
        $input = is_object($input) ? (array) $input : $input;
        $input = is_array($input) ? $input : [];
        $normalizedName = strtolower(trim($name));
        $command = trim((string) ($input['command'] ?? ''));
        if ($command !== '' && preg_match('/(?:^|__|_)(?:bash|shell)$/', $normalizedName) === 1) {
            return 'Ran ' . $command;
        }
        $path = trim((string) ($input['file_path'] ?? ($input['path'] ?? '')));
        if ($path !== '' && preg_match('/(?:^|__|_)(?:read|read_file)$/', $normalizedName) === 1) {
            return 'Read ' . $path;
        }
        $query = trim((string) ($input['query'] ?? ($input['pattern'] ?? '')));
        if ($query !== '' && preg_match('/(?:search|grep|glob)/', $normalizedName) === 1) {
            return 'Searched ' . $query;
        }
        return 'Used ' . $label;
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

    public array $models = [];

    public function fetchModelsFromProvider(): array
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
                    $supports_effort = preg_match('/^(gpt-5|o1|o3|o4)(-|\.|$)/', strtolower($name)) === 1;
                    $models[] = [
                        'name' => $name,
                        'context_length' => 128000,
                        'supports_effort' => $supports_effort,
                        'efforts' => $supports_effort ? $this->getEffortValues() : []
                    ];
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
        $configured_effort = $this->getEffortForRequest();
        if ($configured_effort !== null) {
            $args['reasoning'] = ['effort' => $configured_effort, 'summary' => 'detailed'];
            return $args;
        }
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

    public array $models = [];

    public function fetchModelsFromProvider(): array
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
                    $models[] = ['name' => $name, 'context_length' => 128000];
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
        $configured_effort = $this->getEffortForRequest();
        $supports_thinking =
            str_contains($model_name, 'sonnet') ||
            str_contains($model_name, 'opus') ||
            (preg_match('/haiku-(\d+)/', $model_name, $_hm) === 1 && (int) $_hm[1] >= 4);
        $adaptive_thinking_models = ['claude-opus-4-7', 'claude-opus-4-8'];
        $adaptive_thinking = in_array($model_name, $adaptive_thinking_models, true);
        // explicit enable_thinking=false overrides the default-on behavior for
        // sonnet/opus models; null keeps the existing default (thinking on where
        // supported); true enables it even if a future model doesn't default to it.
        $want_thinking =
            $configured_effort !== null
                ? $configured_effort !== 'none'
                : $this->enable_thinking !== false && ($this->enable_thinking === true || $supports_thinking);

        if (($supports_thinking || $configured_effort !== null) && $want_thinking) {
            if ($adaptive_thinking) {
                // new API: use adaptive thinking + effort level instead of enabled/budget_tokens
                $args['thinking'] = ['type' => 'adaptive'];
                $args['output_config'] = ['effort' => $this->getEffortLevel($configured_effort)];
            } else {
                $args['thinking'] = [
                    'type' => 'enabled',
                    'budget_tokens' =>
                        $configured_effort !== null
                            ? max(1024, $this->getEffortBudgetTokensForRequest($configured_effort))
                            : 10000
                ];
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

    public array $models = [];

    public function fetchModelsFromProvider(): array
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
                    if (!empty($models__value->inputTokenLimit)) {
                        $entry['context_length'] = (int) $models__value->inputTokenLimit;
                    }
                    if (
                        strpos($name, 'imagen') !== false ||
                        (str_starts_with($name, 'gemini-') && str_contains($name, '-image'))
                    ) {
                        $entry['supports_text_to_image'] = true;
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
        $configured_effort = $this->getEffortForRequest();
        // Gemini 2.5 thinking budget. null = default (1024), true = default (1024),
        // false = explicitly off (0). No-op on models without thinking support.
        if (in_array($this->model, ['gemini-2.5-pro', 'gemini-2.5-flash'], true)) {
            $budget =
                $configured_effort !== null
                    ? $this->getEffortBudgetTokensForRequest($configured_effort)
                    : ($this->enable_thinking === false
                        ? 0
                        : 1024);
            $args['generationConfig']['thinkingConfig'] = ['thinkingBudget' => $budget];
        }
        if (preg_match('/^gemini-3/', $this->model) === 1 && $configured_effort !== null) {
            if (!isset($args['generationConfig']) || !is_array($args['generationConfig'])) {
                $args['generationConfig'] = [];
            }
            $args['generationConfig']['thinkingConfig'] = [
                'thinkingLevel' => $this->getEffortLevel($configured_effort)
            ];
        }
        if (preg_match('/^gemma-4-/', $this->model) === 1) {
            if (!isset($args['generationConfig']) || !is_array($args['generationConfig'])) {
                $args['generationConfig'] = [];
            }
            $args['generationConfig']['temperature'] = 1.0;
            $args['generationConfig']['topP'] = 0.95;
            $args['generationConfig']['topK'] = 64;
            $thinking_level =
                $configured_effort !== null
                    ? $this->getEffortLevel($configured_effort)
                    : ($this->enable_thinking === false
                        ? 'low'
                        : 'high');
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

    public array $models = [];
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

    public array $models = [];

    public function fetchModelsFromProvider(): array
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
                    $models[] = ['name' => $name, 'context_length' => 128000];
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

    public function fetchModelsFromProvider(): array
    {
        $models = [];
        // enhance data
        $openrouter_open_weights_by_model = [];
        $models_dev_api = $this->fetchModelsDevApi();
        if (__::x($models_dev_api?->openrouter?->models ?? null) && is_object($models_dev_api->openrouter->models)) {
            foreach ((array) $models_dev_api->openrouter->models as $model_id => $model_data) {
                $openrouter_open_weights_by_model[$model_id] = (bool) ($model_data->open_weights ?? false);
            }
        }
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
                    $model_id = (string) $models__value->id;
                    $model_id_without_suffix = preg_replace('/:[a-z0-9_-]+$/i', '', $model_id) ?? $model_id;
                    $canonical_slug = (string) ($models__value->canonical_slug ?? '');
                    $input_cost = (float) ($models__value->pricing->prompt ?? 0);
                    $output_cost = (float) ($models__value->pricing->completion ?? 0);
                    $supported_params =
                        isset($models__value->supported_parameters) && is_array($models__value->supported_parameters)
                            ? $models__value->supported_parameters
                            : [];
                    $models[] = [
                        'name' => $model_id,
                        'owned_by' => (string) ($models__value->owned_by ?? ''),
                        'context_length' => (int) ($models__value->context_length ?? 128000),
                        'costs' => ['input' => $input_cost, 'input_cached' => $input_cost, 'output' => $output_cost],
                        'supports_temperature' => in_array('temperature', $supported_params, true),
                        'supports_tools' => in_array('tools', $supported_params, true),
                        'supports_effort' =>
                            in_array('reasoning', $supported_params, true) ||
                            in_array('reasoning_effort', $supported_params, true),
                        'efforts' =>
                            in_array('reasoning', $supported_params, true) ||
                            in_array('reasoning_effort', $supported_params, true)
                                ? $this->getEffortValues()
                                : [],
                        'supported_parameters' => $supported_params,
                        'open_weights' =>
                            $openrouter_open_weights_by_model[$model_id] ??
                            ($openrouter_open_weights_by_model[$canonical_slug] ??
                                ($openrouter_open_weights_by_model[$model_id_without_suffix] ?? false)),
                        'default' => $model_id === 'anthropic/claude-haiku-4.5',
                        'test' => $model_id === 'anthropic/claude-haiku-4.5'
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
        if (!empty($tool_calls) && is_array($tool_calls)) {
            // drop malformed tool calls with an empty name
            $tool_calls = array_values(
                array_filter($tool_calls, fn($tc) => trim((string) ($tc['function']['name'] ?? '')) !== '')
            );
        }
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
            $raw_tools = $this->buildLocalToolsArgs('parameters', false, [
                'additionalProperties',
                '$schema',
                'definition',
                'default'
            ]);
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
            // OR (fallback for cliproxyapi/proxy edge case where the terminal finish_reason
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
        $headers = ['Authorization' => 'Bearer ' . $this->api_key];
        $referer = $_SERVER['HTTP_HOST'] ?? ($_SERVER['SERVER_NAME'] ?? (getcwd() ?: null));
        if (is_string($referer) && $referer !== '') {
            $headers['Referer'] = $referer;
        }
        return __::curl(
            url: $this->url . '/chat/completions',
            data: $args,
            method: 'POST',
            headers: $headers,
            timeout: $this->timeout,
            stream_callback: $this->getStreamCallback()
        );
    }

    protected function modifyArgs(?array $args): ?array
    {
        $args = $this->modifyArgsLocal($args);
        $configured_effort = $this->getEffortForRequest();
        if ($configured_effort !== null) {
            $args['reasoning'] = ['effort' => $configured_effort];
        }
        return $args;
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

    public function fetchModelsFromProvider(): array
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
                        'supports_tools' => true,
                        'supports_effort' => true,
                        'efforts' => $this->getEffortValues()
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

    public function fetchModelsFromProvider(): array
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
                        'supports_tools' => true,
                        'supports_effort' => true,
                        'efforts' => $this->getEffortValues()
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
                        'supports_tools' => true,
                        'supports_effort' => true,
                        'efforts' => $this->getEffortValues()
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

    public array $models = [];

    public function fetchModelsFromProvider(): array
    {
        return $this->models;
    }

    public function ping(): bool
    {
        try {
            $response = __::curl(
                url: rtrim($this->url, '/') . '/chat/completions',
                data: [
                    'model' => $this->model,
                    'messages' => [['role' => 'user', 'content' => 'Test']],
                    'max_tokens' => 1
                ],
                method: 'POST',
                headers: ['Authorization' => 'Bearer ' . $this->api_key],
                timeout: 30
            );
            return ($response->status ?? 0) >= 200 &&
                ($response->status ?? 0) < 300 &&
                __::x($response?->result?->choices ?? null);
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

    public function fetchModelsFromProvider(): array
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
                '⚠️ elevenlabs fetchModelsFromProvider returned empty — HTTP ' .
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
                'response' =>
                    'elevenlabs ask() error: provider is speech-to-text only — pass an audio file via the $files argument.',
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
        if (trim($text) === '') {
            $msg = 'elevenlabs stt: provider returned an empty transcript';
            $this->log('⛔ ' . $msg);
            return ['response' => $msg, 'success' => false, 'costs' => 0.0];
        }
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
        $max_tries = max(1, (int) ($this->max_tries ?? 1));
        $raw = false;
        $err = '';
        $http = 0;
        for ($attempt = 1; $attempt <= $max_tries; $attempt++) {
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
            // retry transient failures
            $is_transient = $raw === false || $http === 429 || $http >= 500;
            if (!$is_transient || $attempt >= $max_tries) {
                break;
            }
            $this->log(
                '⚠️ elevenlabs audio transient HTTP ' .
                    $http .
                    ' (' .
                    ($err ?: 'no curl error') .
                    ') — retry ' .
                    $attempt .
                    '/' .
                    ($max_tries - 1)
            );
            sleep($attempt * 3);
        }
        if ($raw === false || $http >= 400) {
            $msg = 'elevenlabs audio HTTP ' . $http . ' err=' . ($err ?: '') . ' body=' . substr((string) $raw, 0, 500);
            $this->log('⛔ ' . $msg);
            return ['response' => $msg, 'success' => false, 'costs' => 0.0];
        }
        if ((string) $raw === '') {
            $msg = 'elevenlabs audio: provider returned no audio (empty response)';
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
                $msg = 'elevenlabs audio: failed to write output_file ' . $output_file;
                $this->log('⛔ ' . $msg);
                return ['response' => $msg, 'success' => false, 'costs' => 0.0];
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

    public function fetchModelsFromProvider(): array
    {
        return $this->models;
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

class ai_cliproxyapi extends ai_openrouter
{
    public ?string $provider = 'CLIProxyAPI';
    public ?string $title = 'CLIProxyAPI';
    public ?string $name = 'cliproxyapi';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 6.5H16V4.5L21 8L16 11.5V9.5H3Z"/><path d="M21 14.5H8V12.5L3 16L8 19.5V17.5H21Z"/></svg>
    SVG;
    protected ?string $url = 'http://127.0.0.1:8317/v1';

    public ?bool $supports_mcp_remote = false;

    public ?bool $supports_stream = true;

    public function fetchModelsFromProvider(): array
    {
        $models = parent::fetchModelsFromProvider();
        foreach ($models as $model_key => $model) {
            if (($model['owned_by'] ?? null) === 'antigravity' && !str_starts_with((string) $model['name'], 'gemini')) {
                unset($models[$model_key]);
                continue;
            }
            $models[$model_key]['supports_tools'] = true;
            // anthropic models reject `temperature`
            $models[$model_key]['supports_temperature'] = !str_starts_with((string) $model['name'], 'claude');
            // the gateway serves the image endpoint of the subscription account
            if (str_starts_with((string) $model['name'], 'gpt-image')) {
                $models[$model_key]['supports_text_to_image'] = true;
                $models[$model_key]['supports_tools'] = false;
            }
            if (!str_starts_with((string) $model['name'], 'gpt-5')) {
                continue;
            }
            $models[$model_key]['supports_effort'] = true;
            $models[$model_key]['efforts'] = $this->getEffortValues();
        }
        return $models;
    }

    protected function modifyArgs(?array $args): ?array
    {
        $args = $this->modifyArgsLocal($args);
        if ($this->effort !== null) {
            $args['reasoning'] = ['effort' => $this->effort];
        }
        return $args;
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

/**
 * Base for the local agentic CLI harnesses (Claude Code, Codex, OpenCode).
 *
 * Unlike every other provider these do not call a chat completion endpoint:
 * they drive a local CLI process that owns its own system prompt, tool
 * execution and conversation history. Only the newest user turn is handed
 * over — continuity comes from a harness session keyed by the aihelper
 * session id, which keeps the very same conversation resumable by hand from
 * a terminal.
 */
abstract class ai_harness extends ai_anthropic
{
    public ?bool $supports_mcp_remote = false;

    public ?bool $supports_stream = true;

    public ?bool $is_harness = true;

    public ?bool $isolate_harness_config = true;

    protected array $harness_files = [];

    protected ?float $harness_costs = null;

    protected ?string $harness_run_id = null;

    protected array $payload_files = [];

    protected array $hidden_harness_tool_ids = [];

    protected array $hidden_harness_stream_tool_ids = [];

    abstract protected function binaryName(): string;

    /**
     * Build the process arguments for a single turn.
     *
     * @return array
     */
    abstract protected function buildArgs(): array;

    /**
     * Consume one decoded harness event: emit anthropic streaming chunks
     * through $emit and fill the mimicked non-stream result.
     *
     * @param array $event
     * @param object $result
     * @param \Closure|null $emit
     * @return void
     */
    abstract protected function handleEvent(array $event, object $result, ?\Closure $emit): void;

    public function fetchModelsFromProvider(): array
    {
        return $this->models;
    }

    public function ping(): bool
    {
        if (!$this->isRemote()) {
            return $this->resolveBinary() !== null;
        }
        $probe = array_merge($this->sshCommand(), [
            $this->remoteShell('command -v ' . escapeshellarg($this->binaryName()) . ' >/dev/null')
        ]);
        exec(implode(' ', array_map('escapeshellarg', $probe)) . ' 2>/dev/null', $output, $status);
        return $status === 0;
    }

    /**
     * Both clis install outside the default PATH — the native installer uses
     * ~/.local/bin, npm uses nvm. The shell init that would add them only runs
     * for interactive shells, so a bare shell (ssh command, php-fpm, cron)
     * sees neither and would wrongly report the binary as missing.
     *
     * @return string
     */
    protected function shellPrelude(): string
    {
        // sourcing nvm only sets a PATH when a default alias exists, so npm-global clis
        // (codex) stay invisible over ssh — the newest installed version is added directly
        return 'export PATH="$HOME/.opencode/bin:$HOME/.local/bin:$PATH"; ' .
            '. "$HOME/.nvm/nvm.sh" >/dev/null 2>&1; ' .
            'nvm_bin=$(ls -d "$HOME"/.nvm/versions/node/*/bin 2>/dev/null | sort -V | tail -1); ' .
            '[ -n "$nvm_bin" ] && export PATH="$PATH:$nvm_bin"; ';
    }

    protected function remoteShell(string $command): string
    {
        return $this->shellPrelude() . $command;
    }

    /**
     * The harness runs its own tool loop, so the local one of the parent must
     * stay dormant — an empty tool list keeps mcp_servers_tools_map empty.
     *
     * @param string $schema_key
     * @param bool $wrap_function_type
     * @param array $strip_schema_keys
     * @return array
     */
    protected function buildLocalToolsArgs(
        string $schema_key = 'parameters',
        bool $wrap_function_type = false,
        array $strip_schema_keys = []
    ): array {
        return [];
    }

    /**
     * The harness reports the effective price of the turn, so the token price
     * table of the parent would only ever be an approximation.
     *
     * @param mixed $response
     * @param array $return
     * @return void
     */
    protected function addCosts(mixed $response, array &$return): void
    {
        if ($this->harness_costs === null) {
            parent::addCosts($response, $return);
            return;
        }
        $return['costs'] += round($this->harness_costs, 5);
        if (!isset($return['output_tokens'])) {
            $return['output_tokens'] = 0;
        }
        $return['output_tokens'] += (int) ($response?->result?->usage?->output_tokens ?? 0);
    }

    protected function resolveBinary(): ?string
    {
        if ($this->isRemote()) {
            return $this->binaryName();
        }
        $found = trim(
            (string) shell_exec(
                $this->shellPrelude() . 'command -v ' . escapeshellarg($this->binaryName()) . ' 2>/dev/null'
            )
        );
        return $found !== '' && is_executable($found) ? $found : null;
    }

    protected function workspace(): string
    {
        $dir = $this->workdir;
        if ($dir === null || trim($dir) === '') {
            $dir =
                sys_get_temp_dir() .
                '/aihelper-harness/' .
                preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $this->session_id);
        }
        $dir = rtrim($dir, '/');
        if ($this->isRemote()) {
            return $dir;
        }
        if (!is_dir($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException('harness: failed to create workspace ' . $dir);
        }
        return $dir;
    }

    // a single exec argument cannot exceed MAX_ARG_STRLEN (128 KiB on linux) and a remote
    // run squeezes the whole invocation into one of them, so system prompts and mcp configs
    // travel as files. never the workdir: that one belongs to the user's project
    protected function payloadFile(string $name, string $content): string
    {
        $path =
            rtrim(sys_get_temp_dir(), '/') .
            '/aihelper-payload/' .
            preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $this->session_id) .
            '/' .
            $name;
        // a name may carry a subdirectory, so the parent is what has to exist
        $dir = dirname($path);
        $cache_key = $path . ':' . md5($content);
        if (isset($this->payload_files[$cache_key])) {
            return $path;
        }
        // an mcp config carries bearer tokens, so nothing here may be world readable
        if (!$this->isRemote()) {
            if (!is_dir($dir) && !mkdir($dir, 0700, true) && !is_dir($dir)) {
                throw new \RuntimeException('harness: failed to create payload directory ' . $dir);
            }
            if (file_put_contents($path, $content) === false) {
                throw new \RuntimeException('harness: failed to write ' . $path);
            }
            chmod($path, 0600);
            $this->payload_files[$cache_key] = true;
            return $path;
        }
        $this->remoteCommand(
            'umask 077 && mkdir -p ' . escapeshellarg($dir) . ' && cat > ' . escapeshellarg($path),
            $content,
            'transfer ' . $name
        );
        $this->payload_files[$cache_key] = true;
        return $path;
    }

    // every cli discovers skills from its own directory layout, so the caller only hands
    // over name => SKILL.md pairs and each harness places them where its binary looks.
    // returns the directory holding the skill folders, or null when there are none
    protected function placeSkills(string $prefix): ?string
    {
        $dir = null;
        foreach ($this->cli_skills as $skills__key => $skills__value) {
            $name = preg_replace('/[^a-z0-9_-]/', '', strtolower((string) $skills__key));
            if ($name === '' || trim((string) $skills__value) === '') {
                continue;
            }
            $dir = dirname(dirname($this->payloadFile($prefix . '/' . $name . '/SKILL.md', (string) $skills__value)));
        }
        return $dir;
    }

    protected function remoteCommand(string $command, string $stdin, string $description): void
    {
        $process = proc_open(
            array_merge($this->sshCommand(), [$command]),
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        if (!is_resource($process)) {
            throw new \RuntimeException('harness: failed to ' . $description);
        }
        fwrite($pipes[0], $stdin);
        fclose($pipes[0]);
        fclose($pipes[1]);
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        if (proc_close($process) !== 0) {
            throw new \RuntimeException(
                'harness: failed to ' . $description . ': ' . (trim((string) $error) ?: 'unknown SSH error')
            );
        }
    }

    protected function isRemote(): bool
    {
        return $this->ssh_host !== null && trim($this->ssh_host) !== '';
    }

    /**
     * The ssh invocation without the remote command. Non-interactive defaults
     * are forced: an unattended run must never block on a prompt.
     *
     * @return array
     */
    protected function sshCommand(): array
    {
        $controlPath =
            '/tmp/aihelper-ssh-' .
            substr(
                hash(
                    'sha256',
                    implode('|', [
                        (string) $this->ssh_host,
                        (string) $this->ssh_user,
                        (string) $this->ssh_port,
                        (string) $this->ssh_key
                    ])
                ),
                0,
                32
            );
        $command = [
            'ssh',
            '-T',
            '-o',
            'BatchMode=yes',
            '-o',
            'StrictHostKeyChecking=accept-new',
            '-o',
            'ConnectTimeout=10',
            '-o',
            'ControlMaster=auto',
            '-o',
            'ControlPersist=60',
            '-o',
            'ControlPath=' . $controlPath
        ];
        if ($this->ssh_key !== null && trim($this->ssh_key) !== '') {
            $command[] = '-i';
            $command[] = $this->ssh_key;
        }
        if ($this->ssh_port !== null) {
            $command[] = '-p';
            $command[] = (string) $this->ssh_port;
        }
        $command[] =
            $this->ssh_user !== null && trim($this->ssh_user) !== ''
                ? $this->ssh_user . '@' . $this->ssh_host
                : (string) $this->ssh_host;
        return $command;
    }

    /**
     * Environment the harness itself needs, without the inherited one.
     *
     * @return array
     */
    protected function harnessEnvironmentOverrides(): array
    {
        return [];
    }

    public function ask(?string $prompt = null, mixed $files = null): array
    {
        $this->harness_files = $files === null ? [] : (is_array($files) ? $files : [$files]);
        return parent::ask($prompt, $files);
    }

    protected function readCliAuthFile(string $path): ?string
    {
        if (!$this->isRemote()) {
            return parent::readCliAuthFile($path);
        }
        $command = array_merge($this->sshCommand(), ['cat ' . escapeshellarg($path)]);
        exec(implode(' ', array_map('escapeshellarg', $command)) . ' 2>/dev/null', $output, $status);
        return $status === 0 && $output !== [] ? implode(PHP_EOL, $output) : null;
    }

    protected function harnessFilePaths(?string $kind = null): array
    {
        $paths = [];
        foreach ($this->harness_files as $file) {
            if (!is_string($file) || $file === '' || preg_match('#^https?://#i', $file) === 1) {
                continue;
            }
            // a remote path belongs to the other machine and cannot be checked here
            if (!$this->isRemote() && !is_file($file)) {
                continue;
            }
            $is_image = in_array(
                strtolower(pathinfo($file, PATHINFO_EXTENSION)),
                ['png', 'jpg', 'jpeg', 'gif', 'webp'],
                true
            );
            if (($kind === 'image' && !$is_image) || ($kind === 'other' && $is_image)) {
                continue;
            }
            $paths[] = $file;
        }
        return $paths;
    }

    // paths no argument of this cli can carry — named in the prompt instead
    protected function harnessPromptFilePaths(): array
    {
        return $this->harnessFilePaths();
    }

    protected function appendFileNote(string $prompt): string
    {
        $paths = $this->harnessPromptFilePaths();
        return $paths === [] ? $prompt : $prompt . PHP_EOL . PHP_EOL . 'Attached files: ' . implode(', ', $paths);
    }

    protected function harnessMcpServers(): array
    {
        $servers = [];
        foreach ($this->mcp_servers ?? [] as $servers__key => $servers__value) {
            if (empty($servers__value['url'])) {
                continue;
            }
            $name = $servers__value['id'] ?? ($servers__value['name'] ?? 'mcp-server-' . ($servers__key + 1));
            $servers[$name] = [
                'url' => rtrim($servers__value['url'], '/') . '/',
                'token' => $servers__value['authorization_token'] ?? null,
                'required' => ($servers__value['required'] ?? false) === true
            ];
        }
        return $servers;
    }

    protected function harnessMcpTokenVariable(string $name): string
    {
        return 'AIHELPER_MCP_TOKEN_' . strtoupper(preg_replace('/[^a-zA-Z0-9]/', '_', $name) ?? '');
    }

    protected function harnessMcpTokenEnvironment(): array
    {
        $environment = [];
        foreach ($this->harnessMcpServers() as $name => $server) {
            if (($server['token'] ?? null) === null || trim((string) $server['token']) === '') {
                continue;
            }
            $environment[$this->harnessMcpTokenVariable($name)] = $server['token'];
        }
        return $environment;
    }

    protected function isHarnessSkillTool(string $name, mixed $input): bool
    {
        if (strtolower($name) === 'skill') {
            return true;
        }
        $serialized = json_encode($input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return
            is_string($serialized) &&
            str_contains($serialized, '/aihelper-payload/') &&
            str_contains($serialized, '/skills/') &&
            str_contains($serialized, '/SKILL.md');
    }

    protected function harnessEnvironment(): ?array
    {
        $overrides = $this->harnessEnvironmentOverrides();
        return $overrides === [] ? null : array_merge(getenv(), $overrides);
    }

    protected function harnessInput(string $prompt): string
    {
        return $this->appendFileNote($prompt);
    }

    protected function emitAnthropicEvent(?\Closure $emit, array $event): void
    {
        if ($emit === null) {
            return;
        }
        $emit(
            'event: ' .
                ($event['type'] ?? 'message_delta') .
                "\n" .
                'data: ' .
                json_encode($event, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) .
                "\n\n"
        );
    }

    protected function makeApiCall(?array $args = null): mixed
    {
        $binary = $this->resolveBinary();
        if ($binary === null) {
            throw new \RuntimeException('harness: binary "' . $this->binaryName() . '" not found.');
        }

        $prompt = '';
        foreach (array_reverse($args['messages'] ?? []) as $message) {
            if (($message['role'] ?? null) !== 'user') {
                continue;
            }
            $content = $message['content'] ?? '';
            if (is_string($content)) {
                $prompt = $content;
                break;
            }
            if (is_array($content)) {
                $texts = [];
                foreach ($content as $block) {
                    $text = is_array($block) ? $block['text'] ?? null : $block->text ?? null;
                    if ($text !== null) {
                        $texts[] = $text;
                    }
                }
                $prompt = implode(PHP_EOL, $texts);
                break;
            }
        }
        if (trim($prompt) === '') {
            throw new \RuntimeException('harness: no user prompt to hand over.');
        }

        if ($this->isRemote()) {
            $preflight = array_merge($this->sshCommand(), ['true']);
            $preflightStatus = 1;
            $preflightOutput = [];
            for ($attempt = 1; $attempt <= 3; $attempt++) {
                $preflightOutput = [];
                exec(
                    implode(' ', array_map('escapeshellarg', $preflight)) . ' 2>&1',
                    $preflightOutput,
                    $preflightStatus
                );
                if ($preflightStatus === 0) {
                    break;
                }
                if ($attempt < 3) {
                    sleep($attempt);
                }
            }
            if ($preflightStatus !== 0) {
                throw new \RuntimeException(
                    'harness: SSH connection failed: ' .
                        (trim(implode(PHP_EOL, $preflightOutput)) ?: 'unknown SSH error')
                );
            }
        }

        $this->harness_run_id = md5(uniqid('', true));
        if ($this->isRemote()) {
            // the whole invocation becomes one remote shell line: the run id
            // tags the process tree so an abort can find it again, and the
            // working directory is created on the machine that owns it
            $inner = array_merge([$binary], $this->buildArgs());
            $script =
                'mkdir -p ' .
                escapeshellarg($this->workspace()) .
                ' && cd ' .
                escapeshellarg($this->workspace()) .
                ' && exec ' .
                implode(' ', array_map('escapeshellarg', $inner));
            $exports = ['AIHELPER_RUN_ID=' . escapeshellarg((string) $this->harness_run_id)];
            foreach ($this->harnessEnvironmentOverrides() as $name => $value) {
                $exports[] = $name . '=' . escapeshellarg((string) $value);
            }
            $command = array_merge($this->sshCommand(), [
                implode(' ', $exports) . ' setsid --wait bash -c ' . escapeshellarg($this->remoteShell($script))
            ]);
        } else {
            $command = array_merge(['setsid', '--wait', $binary], $this->buildArgs());
        }
        // the mcp config carries bearer tokens and must not reach the log
        $loggable = $command;
        foreach ($loggable as $loggable__key => $loggable__value) {
            if ($loggable__value === '--mcp-config' && isset($loggable[$loggable__key + 1])) {
                $loggable[$loggable__key + 1] = '(redacted)';
            }
        }
        $loggable = implode(' ', $loggable);
        // a remote run carries the environment inside one shell line, so the
        // secrets in it have to be replaced by value rather than by position
        foreach ($this->harnessEnvironmentOverrides() as $secrets__key => $secrets__value) {
            if (!is_string($secrets__value) || $secrets__value === '') {
                continue;
            }
            if (preg_match('/TOKEN|KEY|SECRET|PASSWORD/i', (string) $secrets__key) !== 1) {
                continue;
            }
            $loggable = str_replace($secrets__value, '(redacted)', $loggable);
        }
        $this->log($loggable, 'harness command');

        $process = proc_open(
            $command,
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            $this->isRemote() ? null : $this->workspace(),
            $this->isRemote() ? null : $this->harnessEnvironment()
        );
        if (!is_resource($process)) {
            throw new \RuntimeException('harness: failed to spawn ' . $binary);
        }
        $pid = (int) (proc_get_status($process)['pid'] ?? 0);

        fwrite($pipes[0], $this->harnessInput($prompt));
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $this->harness_costs = null;
        $emit = $this->getStreamCallback();
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

        $last_activity_at = time();
        $buffer = '';
        $errors = '';
        $exit_code = null;
        $drain_until = null;
        $sensitiveValues = array_values($this->harnessMcpTokenEnvironment());
        $redact = function (mixed $value) use (&$redact, $sensitiveValues): mixed {
            if (is_array($value)) {
                foreach ($value as $key => $item) {
                    if (
                        is_string($key) &&
                        preg_match(
                            '/^(?:authorization|authorization_token|access_token|refresh_token|id_token|client_secret|token|secret|password|passwd|passphrase|api[_-]?key|private[_-]?key|access[_-]?key|cookie)$/i',
                            $key
                        ) === 1
                    ) {
                        $value[$key] = '***';
                        continue;
                    }
                    $value[$key] = $redact($item);
                }
                return $value;
            }
            if (!is_string($value)) {
                return $value;
            }
            if ($sensitiveValues !== []) {
                $value = str_replace($sensitiveValues, '***', $value);
            }
            return preg_replace(
                '/(?i)(\bAIHELPER_MCP_TOKEN_[A-Z0-9_]+\s*=\s*)(?:"(?:\\.|[^"])*"|\'(?:\\.|[^\'])*\'|[^\s,;]+)/',
                '$1***',
                $value
            ) ?? $value;
        };
        while (true) {
            $read = [];
            if (!feof($pipes[1])) {
                $read[] = $pipes[1];
            }
            if (!feof($pipes[2])) {
                $read[] = $pipes[2];
            }
            if ($read === []) {
                break;
            }
            $write = null;
            $except = null;
            if (stream_select($read, $write, $except, 1) === false) {
                break;
            }
            foreach ($read as $stream) {
                $chunk = fread($stream, 65536);
                if ($chunk === false || $chunk === '') {
                    continue;
                }
                $last_activity_at = time();
                if ($stream === $pipes[2]) {
                    $errors .= $chunk;
                    continue;
                }
                $buffer .= $chunk;
                while (($position = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $position));
                    $buffer = substr($buffer, $position + 1);
                    if ($line === '' || $line[0] !== '{') {
                        continue;
                    }
                    $event = json_decode($line, true);
                    if (!is_array($event)) {
                        continue;
                    }
                    $event = $redact($event);
                    $this->handleEvent($event, $result, $emit);
                }
            }
            // a lingering grandchild can hold the pipes open after the harness
            // itself is gone, so the process state ends the loop, not eof
            if ($drain_until === null) {
                $status = proc_get_status($process);
                if ($status['running'] === false) {
                    $exit_code = (int) $status['exitcode'];
                    $drain_until = microtime(true) + 2;
                }
            } elseif (microtime(true) >= $drain_until) {
                break;
            }
            if (time() - $last_activity_at >= (int) $this->timeout) {
                $this->terminateProcess($process, $pid);
                throw new \RuntimeException('harness: inactivity timeout after ' . $this->timeout . ' seconds.');
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);
        $closed = proc_close($process);
        if ($exit_code === null) {
            $exit_code = $closed;
        }

        if (
            $result->result->stop_reason === null &&
            empty($result->result->content) &&
            ($result->result->error ?? null) === null
        ) {
            $message = trim($errors) !== '' ? trim($errors) : 'harness exited with code ' . $exit_code;
            $result->result->error = (object) ['message' => $message];
            $this->log($message, 'harness failed');
        }

        // the parent short-circuits into its own tool loop on a trailing
        // "tool_use" stop reason — the harness has already run its tools
        if ($this->stream === true && ($this->stream_response?->result ?? null) !== null) {
            if ($result->result->stop_reason !== null) {
                $this->stream_response->result->stop_reason = $result->result->stop_reason;
            }
            if (($result->result->error ?? null) !== null && empty($this->stream_response->result->content)) {
                $this->stream_response->result->error = $result->result->error;
            }
            // the streamed response is what the caller keeps, and it carries no tool results at
            // all — a native stream only ever describes the assistant side. the recorded calls
            // are merged in so the session documents what the cli actually did
            if ($this->hidden_harness_stream_tool_ids !== []) {
                $this->stream_response->result->content = array_values(
                    array_filter(
                        $this->stream_response->result->content ?? [],
                        fn($block) => ($block->type ?? null) !== 'tool_use' ||
                            !isset($this->hidden_harness_stream_tool_ids[(string) ($block->id ?? '')])
                    )
                );
            }
            $streamed = [];
            foreach ($this->stream_response->result->content ?? [] as $block) {
                if (($block->type ?? null) === 'tool_use') {
                    $streamed[(string) ($block->id ?? '')] = true;
                }
            }
            foreach ($result->result->content as $block) {
                if (!in_array($block->type ?? null, ['tool_use', 'tool_result'], true)) {
                    continue;
                }
                if ($block->type === 'tool_use' && isset($streamed[(string) ($block->id ?? '')])) {
                    continue;
                }
                $this->stream_response->result->content[] = $block;
            }
        }

        return $result;
    }

    protected function terminateProcess(mixed $process, int $pid): void
    {
        if ($this->isRemote() && $this->harness_run_id !== null) {
            $tag = 'AIHELPER_RUN_ID=' . $this->harness_run_id;
            foreach (['TERM', 'KILL'] as $signal) {
                $kill = array_merge($this->sshCommand(), [
                    'pkill -' . $signal . ' -f ' . escapeshellarg($tag)
                ]);
                exec(implode(' ', array_map('escapeshellarg', $kill)) . ' >/dev/null 2>&1');
                if ($signal === 'TERM') {
                    usleep(500000);
                }
            }
        }
        $group = $pid > 0 ? posix_getpgid($pid) : false;
        if ($group !== false && $group > 0 && $group !== posix_getpgrp()) {
            posix_kill(-$group, SIGTERM);
            usleep(500000);
            posix_kill(-$group, SIGKILL);
        }
        proc_terminate($process, SIGKILL);
        proc_close($process);
    }
}

class ai_claudecode extends ai_harness
{
    public ?string $provider = 'Anthropic';

    public ?string $title = 'Claude Code';

    public ?string $name = 'claudecode';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path fill="currentColor" d="M17.304 3.541h-3.672l6.696 16.918H24Zm-10.608 0L0 20.459h3.744l1.37-3.553h7.005l1.369 3.553h3.744L10.536 3.541Zm-.371 10.223L8.616 7.82l2.291 5.945Z"/></svg>
    SVG;

    protected ?string $url = null;

    public array $models = [
        [
            'name' => 'claude-opus-5',
            'context_length' => 200000,
            'max_output_tokens' => 64000,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.0000005, 'output' => 0.000025],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => true
        ],
        [
            'name' => 'claude-sonnet-5',
            'context_length' => 200000,
            'max_output_tokens' => 64000,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.0000003, 'output' => 0.000015],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false
        ],
        [
            'name' => 'claude-haiku-4-5-20251001',
            'context_length' => 200000,
            'max_output_tokens' => 64000,
            'costs' => ['input' => 0.000001, 'input_cached' => 0.0000001, 'output' => 0.000005],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false
        ]
    ];

    protected function binaryName(): string
    {
        return 'claude';
    }

    /**
     * Route the harness through a configured gateway (e.g. cliproxyapi).
     * Without one the cli uses its own login and inherits the environment
     * unchanged — replacing it wholesale could strip what the cli needs.
     *
     * @return array|null
     */
    protected function harnessEnvironmentOverrides(): array
    {
        // without this claude code refuses to skip permissions as root. tool search keeps
        // large mcp sets out of the context: it looks schemas up on demand once they grow
        // past 5% of the context window. the bundled skills (code-review, security-review,
        // dataviz, ...) belong to the cli, not to the caller — they only add noise here and
        // could fire on an unrelated task. plugin skills are not affected by the flag
        $overrides = [
            'IS_SANDBOX' => '1',
            'ENABLE_TOOL_SEARCH' => 'auto:5',
            'MCP_TIMEOUT' => '300000',
            'CLAUDE_CODE_DISABLE_BUNDLED_SKILLS' => '1'
        ];
        if ($this->url !== null && trim($this->url) !== '') {
            // claude code appends "/v1" itself, while gateway urls are usually
            // configured with it — keeping both would request "/v1/v1/messages"
            $overrides['ANTHROPIC_BASE_URL'] = preg_replace('#/v1/?$#', '', rtrim($this->url, '/'));
        }
        if ($this->api_key !== null && trim($this->api_key) !== '') {
            $overrides['ANTHROPIC_AUTH_TOKEN'] = $this->api_key;
        }
        return $overrides;
    }

    protected function buildArgs(): array
    {
        $this->dropFailedTurns();
        // "--continue" picks the newest conversation of the working directory
        // and falls back to a fresh one when the directory has none yet
        $args = [
            '-p',
            '--verbose',
            '--input-format',
            'stream-json',
            '--output-format',
            'stream-json',
            '--continue'
        ];
        if ($this->stream === true) {
            $args[] = '--include-partial-messages';
        }
        if ($this->model !== null) {
            $args[] = '--model';
            $args[] = $this->model;
        }
        if (in_array($this->effort, ['low', 'medium', 'high', 'xhigh', 'max'], true)) {
            $args[] = '--effort';
            $args[] = $this->effort;
        }
        $args[] = '--dangerously-skip-permissions';
        // a plugin is the only way to hand claude code skills for a single run;
        // the manifest is what makes the directory loadable at all
        $skills = $this->placeSkills('plugin/skills');
        if ($skills !== null) {
            $plugin = dirname($skills);
            $this->payloadFile(
                'plugin/.claude-plugin/plugin.json',
                json_encode(
                    ['name' => 'skills', 'version' => '1.0.0', 'description' => 'Harness skills'],
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                )
            );
            $args[] = '--plugin-dir';
            $args[] = $plugin;
        }
        if ($this->isolate_harness_config === true) {
            // an empty source list drops CLAUDE.md and project skills
            $args[] = '--setting-sources';
            $args[] = '';
            // this one drops the user and built-in ones too, but "all skills" is
            // literal — it would take the plugin skills above down with them
            if ($skills === null) {
                $args[] = '--disable-slash-commands';
            }
        }
        if ($this->system_prompt !== null) {
            // append, never replace: the default prompt carries the tool
            // instructions the cli needs to work at all
            $args[] = '--append-system-prompt-file';
            $args[] = $this->payloadFile('system-prompt.md', $this->system_prompt);
        }

        $servers = [];
        foreach ($this->harnessMcpServers() as $name => $server) {
            $entry = ['type' => 'http', 'url' => $server['url'], 'timeout' => 300000];
            if ($server['token'] !== null && trim((string) $server['token']) !== '') {
                $entry['headers'] = ['Authorization' => 'Bearer ' . $server['token']];
            }
            $servers[$name] = $entry;
        }
        if ($servers !== []) {
            $args[] = '--mcp-config';
            $args[] = $this->payloadFile(
                'mcp-config.json',
                json_encode(['mcpServers' => $servers], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
            $args[] = '--strict-mcp-config';
        }

        return $args;
    }

    /**
     * Cut the conversation back to the last turn that got a real answer.
     *
     * A failed request is persisted as a synthetic "API Error" assistant message, and every
     * "--continue" from then on replays it — the conversation keeps failing long after the
     * outage that caused it, on every model and with every prompt. Nothing after that first
     * failure is worth keeping: it is only the failed retries.
     */
    private function dropFailedTurns(): void
    {
        // claude code stores a conversation under its working directory with every
        // character outside [a-z0-9-] folded into a dash
        $project = preg_replace('/[^A-Za-z0-9]/', '-', $this->workspace());
        $command =
            'd="${HOME:-/root}/.claude/projects/' .
            $project .
            '"; f=$(ls -t "$d"/*.jsonl 2>/dev/null | head -1); [ -n "$f" ] || exit 0; ' .
            'awk \'index($0, "<synthetic>") && index($0, "API Error") { exit } { print }\' "$f" > "$f.heal" || exit 0; ' .
            'if [ -s "$f.heal" ] && [ "$(wc -c < "$f.heal")" -lt "$(wc -c < "$f")" ]; ' .
            'then mv "$f.heal" "$f"; else rm -f "$f.heal"; fi';
        if ($this->isRemote()) {
            $this->remoteCommand($command, '', 'drop failed turns');
            return;
        }
        exec('sh -c ' . escapeshellarg($command) . ' 2>/dev/null');
    }

    protected function harnessInput(string $prompt): string
    {
        // claude code has no attachment flag, but it can read any path itself —
        // naming them is enough, and unlike base64 blocks it costs no context
        $prompt = $this->appendFileNote($prompt);
        return
            json_encode(
                [
                    'type' => 'user',
                    'message' => [
                        'role' => 'user',
                        'content' => [['type' => 'text', 'text' => $prompt]]
                    ]
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
            ) . "\n";
    }

    protected function handleEvent(array $event, object $result, ?\Closure $emit): void
    {
        $type = $event['type'] ?? null;

        if ($type === 'system' && ($event['subtype'] ?? null) === 'init' && !empty($event['session_id'])) {
            $this->log((string) $event['session_id'], 'harness session');
            return;
        }

        // claude code forwards the literal anthropic streaming events, so the
        // parser of the parent can consume them unchanged
        if ($type === 'stream_event' && isset($event['event']['type'])) {
            $this->emitAnthropicEvent($emit, $event['event']);
            return;
        }

        // the cli runs its tools itself, so the calls only become visible to the
        // caller when they are written into the session like a native provider does
        if (in_array($type, ['assistant', 'user'], true) && is_array($event['message']['content'] ?? null)) {
            foreach ($event['message']['content'] as $content__value) {
                $content_type = $content__value['type'] ?? null;
                if ($type === 'assistant' && $content_type === 'text' && isset($content__value['text'])) {
                    $result->result->content[] = (object) ['type' => 'text', 'text' => $content__value['text']];
                }
                if ($type === 'assistant' && $content_type === 'tool_use') {
                    $tool_id = (string) ($content__value['id'] ?? '');
                    if (
                        $this->isHarnessSkillTool(
                            (string) ($content__value['name'] ?? ''),
                            $content__value['input'] ?? []
                        )
                    ) {
                        $this->hidden_harness_tool_ids[$tool_id] = true;
                        $this->hidden_harness_stream_tool_ids[$tool_id] = true;
                        $skillInput = json_encode(
                            $content__value['input'] ?? [],
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        ) ?: '';
                        preg_match('#/skills/([^/]+)/SKILL\.md#', $skillInput, $skillMatch);
                        $this->emitTranscript(
                            $tool_id,
                            'Loaded skill ' . ($skillMatch[1] ?? 'instructions'),
                            'running'
                        );
                        $result->result->content[] = (object) $content__value;
                        continue;
                    }
                    $this->emitTranscript(
                        $tool_id,
                        $this->toolTranscriptLabel(
                            (string) ($content__value['name'] ?? 'tool'),
                            $content__value['input'] ?? []
                        ),
                        'running',
                        $content__value['input'] ?? []
                    );
                }
                if (
                    $type === 'user' &&
                    $content_type === 'tool_result' &&
                    isset($this->hidden_harness_tool_ids[(string) ($content__value['tool_use_id'] ?? '')])
                ) {
                    $toolId = (string) $content__value['tool_use_id'];
                    $this->emitTranscript(
                        $toolId,
                        '',
                        ($content__value['is_error'] ?? false) === true ? 'error' : 'completed'
                    );
                    unset($this->hidden_harness_tool_ids[$toolId]);
                    $result->result->content[] = (object) $content__value;
                    continue;
                }
                if ($type === 'user' && $content_type === 'tool_result') {
                    $this->emitTranscript(
                        (string) ($content__value['tool_use_id'] ?? ''),
                        '',
                        ($content__value['is_error'] ?? false) === true ? 'error' : 'completed',
                        $content__value['content'] ?? ''
                    );
                }
                if ($content_type === ($type === 'assistant' ? 'tool_use' : 'tool_result')) {
                    $result->result->content[] = (object) $content__value;
                }
            }
            return;
        }

        if ($type !== 'result') {
            return;
        }
        $result->result->stop_reason = $event['stop_reason'] ?? 'end_turn';
        $result->result->usage = (object) [
            'input_tokens' => (int) ($event['usage']['input_tokens'] ?? 0),
            'cache_creation_input_tokens' => (int) ($event['usage']['cache_creation_input_tokens'] ?? 0),
            'cache_read_input_tokens' => (int) ($event['usage']['cache_read_input_tokens'] ?? 0),
            'output_tokens' => (int) ($event['usage']['output_tokens'] ?? 0)
        ];
        $this->harness_costs = (float) ($event['total_cost_usd'] ?? 0);
        if (($event['is_error'] ?? false) === true) {
            $result->result->error = (object) [
                'message' => trim((string) ($event['result'] ?? 'harness run failed'))
            ];
        }
    }
}

class ai_codex extends ai_harness
{
    public ?string $provider = 'OpenAI';

    public ?string $title = 'Codex';

    public ?string $name = 'codex';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 260"><path d="M239.184 106.203a64.72 64.72 0 0 0-5.576-53.103C219.452 28.459 191 15.784 163.213 21.74A65.586 65.586 0 0 0 52.096 45.22a64.72 64.72 0 0 0-43.23 31.36c-14.31 24.602-11.061 55.634 8.033 76.74a64.67 64.67 0 0 0 5.525 53.102c14.174 24.65 42.644 37.324 70.446 31.36a64.72 64.72 0 0 0 48.754 21.744c28.481.025 53.714-18.361 62.414-45.481a64.77 64.77 0 0 0 43.229-31.36c14.137-24.558 10.875-55.423-8.083-76.483m-97.56 136.338a48.4 48.4 0 0 1-31.105-11.255l1.535-.87l51.67-29.825a8.6 8.6 0 0 0 4.247-7.367v-72.85l21.845 12.636c.218.111.37.32.409.563v60.367c-.056 26.818-21.783 48.545-48.601 48.601M37.158 197.93a48.35 48.35 0 0 1-5.781-32.589l1.534.921l51.722 29.826a8.34 8.34 0 0 0 8.441 0l63.181-36.425v25.221a.87.87 0 0 1-.358.665l-52.335 30.184c-23.257 13.398-52.97 5.431-66.404-17.803M23.549 85.38a48.5 48.5 0 0 1 25.58-21.333v61.39a8.29 8.29 0 0 0 4.195 7.316l62.874 36.272l-21.845 12.636a.82.82 0 0 1-.767 0L41.353 151.53c-23.211-13.454-31.171-43.144-17.804-66.405zm179.466 41.695l-63.08-36.63L161.73 77.86a.82.82 0 0 1 .768 0l52.233 30.184a48.6 48.6 0 0 1-7.316 87.635v-61.391a8.54 8.54 0 0 0-4.4-7.213m21.742-32.69l-1.535-.922l-51.619-30.081a8.39 8.39 0 0 0-8.492 0L99.98 99.808V74.587a.72.72 0 0 1 .307-.665l52.233-30.133a48.652 48.652 0 0 1 72.236 50.391zM88.061 139.097l-21.845-12.585a.87.87 0 0 1-.41-.614V65.685a48.652 48.652 0 0 1 79.757-37.346l-1.535.87l-51.67 29.825a8.6 8.6 0 0 0-4.246 7.367zm11.868-25.58L128.067 97.3l28.188 16.218v32.434l-28.086 16.218l-28.188-16.218z"/></svg>
    SVG;

    protected ?string $url = null;

    protected ?string $shared_codex_home = null;

    public array $models = [
        [
            'name' => 'gpt-5.6-sol',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => true
        ],
        [
            'name' => 'gpt-5.5-codex',
            'context_length' => 400000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 0.00000125, 'input_cached' => 0.000000125, 'output' => 0.00001],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'default' => false
        ]
    ];

    protected function binaryName(): string
    {
        return 'codex';
    }

    protected function harnessPromptFilePaths(): array
    {
        // "--image" only takes images; anything else is named in the prompt
        return $this->harnessFilePaths('other');
    }

    protected function buildArgs(): array
    {
        // "--last" is filtered by working directory and starts a fresh thread
        // when the directory has none yet; the bypass flag (alias "--yolo") is
        // required because codex sandboxes via bubblewrap, which needs a user
        // namespace that a default docker container does not grant
        $options = ['--json', '--skip-git-repo-check', '--dangerously-bypass-approvals-and-sandbox'];
        if ($this->model !== null) {
            $options[] = '--model';
            $options[] = $this->model;
        }
        if (in_array($this->effort, ['minimal', 'low', 'medium', 'high', 'xhigh'], true)) {
            $options[] = '-c';
            $options[] = 'model_reasoning_effort="' . $this->effort . '"';
        }
        // with a system prompt the run gets its own config and skill scope (see the
        // environment overrides), which already carries these keys
        if ($this->isolate_harness_config === true && $this->system_prompt === null && $this->cli_skills === []) {
            // drops config.toml and the AGENTS.md files found from the working
            // directory upwards; the one in CODEX_HOME is out of reach here
            $options[] = '--ignore-user-config';
            $options[] = '-c';
            $options[] = 'project_doc_max_bytes=0';
        }
        // without an own CODEX_HOME the prompt travels as an argument again, which caps it
        // at MAX_ARG_STRLEN — the only way left when the user's config must stay in play
        if ($this->system_prompt !== null && $this->isolate_harness_config !== true) {
            $options[] = '-c';
            $options[] = 'instructions=' . $this->tomlString($this->system_prompt);
        }
        foreach ($this->harnessFilePaths('image') as $path) {
            $options[] = '--image';
            $options[] = $path;
        }
        if (array_key_exists('imagegen', $this->harnessMcpServers())) {
            $options[] = '-c';
            $options[] = 'features.image_generation=false';
        }
        foreach ($this->harnessMcpServers() as $name => $server) {
            $key = 'mcp_servers.' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
            $options[] = '-c';
            // toml has no "\/" escape, so the slashes must survive unescaped
            $options[] = $key . '.url=' . json_encode($server['url'], JSON_UNESCAPED_SLASHES);
            $options[] = '-c';
            $options[] = $key . '.startup_timeout_sec=300';
            if ($server['required'] === true) {
                $options[] = '-c';
                $options[] = $key . '.required=true';
            }
            if ($server['token'] === null || trim((string) $server['token']) === '') {
                continue;
            }
            $options[] = '-c';
            $options[] =
                $key . '.bearer_token_env_var=' . json_encode($this->harnessMcpTokenVariable($name), JSON_UNESCAPED_SLASHES);
        }
        return array_merge(['exec', 'resume', '--last'], $options);
    }

    private function tomlString(string $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    protected function harnessEnvironmentOverrides(): array
    {
        $overrides = $this->harnessMcpTokenEnvironment();
        // the prompt is too large for an argument, so an isolated config.toml carries it.
        // config and skills stay run-specific while auth, sessions and sqlite state remain
        // in the user's native codex home, making the conversation resumable from the cli
        if (($this->system_prompt === null && $this->cli_skills === []) || $this->isolate_harness_config !== true) {
            return $overrides;
        }
        $payload_home = dirname($this->payloadFile('codex/.root', ''));
        $this->placeSkills('codex/skills');
        if ($this->shared_codex_home === null) {
            $user_home = getenv('HOME') ?: '/root';
            if ($this->isRemote()) {
                $remote_home_command = array_merge($this->sshCommand(), ['printf %s "$HOME"']);
                $remote_home_output = [];
                $remote_home_status = 1;
                exec(
                    implode(' ', array_map('escapeshellarg', $remote_home_command)) . ' 2>/dev/null',
                    $remote_home_output,
                    $remote_home_status
                );
                $user_home = $remote_home_status === 0 ? trim(implode(PHP_EOL, $remote_home_output)) : '';
            }
            if ($user_home === '') {
                throw new \RuntimeException('harness: failed to resolve codex home.');
            }
            $native_home = rtrim($user_home, '/') . '/.codex';
            $shared_codex_home =
                $native_home .
                '/charly/' .
                preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string) $this->session_id);
            $links = [
                $shared_codex_home . '/auth.json' => $native_home . '/auth.json',
                $shared_codex_home . '/config.toml' => $payload_home . '/config.toml',
                $shared_codex_home . '/skills' => $payload_home . '/skills'
            ];
            if ($this->isRemote()) {
                $commands = [
                    'umask 077 && mkdir -p ' .
                    escapeshellarg($native_home) .
                    ' ' .
                    escapeshellarg($shared_codex_home) .
                    ' && chmod 700 ' .
                    escapeshellarg($shared_codex_home)
                ];
                foreach ($links as $link => $target) {
                    $commands[] =
                        'if [ -L ' . escapeshellarg($link) . ' ]; then ln -sfn ' .
                        escapeshellarg($target) . ' ' . escapeshellarg($link) .
                        '; elif [ -e ' . escapeshellarg($link) . ' ]; then exit 1; else ln -s ' .
                        escapeshellarg($target) . ' ' . escapeshellarg($link) . '; fi';
                }
                $this->remoteCommand(
                    implode(' && ', $commands),
                    '',
                    'prepare shared codex home'
                );
            } else {
                foreach ([$native_home, $shared_codex_home] as $directory) {
                    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
                        throw new \RuntimeException('harness: failed to create ' . $directory);
                    }
                }
                chmod($shared_codex_home, 0700);
                foreach ($links as $link => $target) {
                    if (is_link($link) && readlink($link) === $target) {
                        continue;
                    }
                    if (is_link($link)) {
                        unlink($link);
                    }
                    if (file_exists($link)) {
                        throw new \RuntimeException('harness: refusing to replace ' . $link);
                    }
                    if (!symlink($target, $link)) {
                        throw new \RuntimeException('harness: failed to link ' . $link);
                    }
                }
            }
            $this->shared_codex_home = $shared_codex_home;
        }
        $config = 'project_doc_max_bytes = 0' . PHP_EOL .
            'sqlite_home = ' . $this->tomlString(dirname(dirname($this->shared_codex_home))) . PHP_EOL;
        if ($this->system_prompt !== null) {
            $config =
                'instructions = ' . $this->tomlString($this->system_prompt) . PHP_EOL . $config;
        }
        $this->payloadFile('codex/config.toml', $config);
        $overrides['CODEX_HOME'] = $this->shared_codex_home;
        return $overrides;
    }

    protected function handleEvent(array $event, object $result, ?\Closure $emit): void
    {
        $type = $event['type'] ?? null;

        if ($type === 'thread.started' && !empty($event['thread_id'])) {
            $this->log((string) $event['thread_id'], 'harness session');
            $this->emitAnthropicEvent($emit, [
                'type' => 'message_start',
                'message' => [
                    'type' => 'message',
                    'role' => 'assistant',
                    'model' => (string) $this->model,
                    'content' => [],
                    'stop_reason' => null,
                    'usage' => ['input_tokens' => 0, 'output_tokens' => 0]
                ]
            ]);
            return;
        }

        if (in_array($type, ['item.started', 'item.completed'], true)) {
            $item = is_array($event['item'] ?? null) ? $event['item'] : [];
            $item_type = (string) ($item['type'] ?? '');
            $supportedItemTypes = ['mcp_tool_call', 'command_execution', 'file_change', 'web_search', 'todo_list'];
            if (in_array($item_type, $supportedItemTypes, true)) {
                $isMcp = $item_type === 'mcp_tool_call';
                $tool_name = $isMcp
                    ? (string) (($item['server'] ?? '') . '__' . ($item['tool'] ?? ''))
                    : ($item_type === 'command_execution' ? 'shell' : $item_type);
                $tool_input = match ($item_type) {
                    'mcp_tool_call' => $item['arguments'] ?? [],
                    'command_execution' => ['command' => $item['command'] ?? ''],
                    'file_change' => ['changes' => $item['changes'] ?? []],
                    'web_search' => ['query' => $item['query'] ?? ''],
                    'todo_list' => ['items' => $item['items'] ?? []]
                };
                $hiddenSkill = $this->isHarnessSkillTool($tool_name, $tool_input);
                if ($hiddenSkill) {
                    if ($type !== 'item.completed') {
                        return;
                    }
                }

                $label = $this->toolTranscriptLabel($tool_name, $tool_input);
                if ($item_type === 'file_change') {
                    $paths = [];
                    foreach ($item['changes'] ?? [] as $change) {
                        if (is_array($change) && trim((string) ($change['path'] ?? '')) !== '') {
                            $paths[] = (string) $change['path'];
                        }
                    }
                    $label = $paths === [] ? 'Changed files' : 'Changed ' . implode(', ', $paths);
                }
                if ($item_type === 'web_search') {
                    $label = trim((string) ($item['query'] ?? '')) === ''
                        ? 'Searched the web'
                        : 'Searched ' . trim((string) $item['query']);
                }
                if ($item_type === 'todo_list') {
                    $label = 'Updated plan';
                }

                $toolOutput = '';
                $transcriptOutput = '';
                if ($isMcp) {
                    $mcpResult = is_array($item['result'] ?? null) ? $item['result'] : [];
                    $outputParts = [];
                    $hasNonTextContent = false;
                    foreach ($mcpResult['content'] ?? [] as $block) {
                        if (isset($block['text'])) {
                            $outputParts[] = (string) $block['text'];
                        }
                        if (($block['type'] ?? 'text') !== 'text') {
                            $hasNonTextContent = true;
                        }
                    }
                    $transcriptOutput = $outputParts !== []
                        ? implode(PHP_EOL, $outputParts)
                        : (string) ($item['error'] ?? '');
                    $additionalResultData = array_diff_key(
                        $mcpResult,
                        ['content' => true, 'structuredContent' => true, 'structured_content' => true]
                    );
                    $hasStructuredContent = ($mcpResult['structuredContent'] ?? $mcpResult['structured_content'] ?? null) !== null;
                    $toolOutput = $hasNonTextContent || $hasStructuredContent || $additionalResultData !== []
                        ? (json_encode(
                            $mcpResult,
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
                        ) ?: $transcriptOutput)
                        : $transcriptOutput;
                }
                if ($item_type === 'command_execution') {
                    $toolOutput = (string) ($item['aggregated_output'] ?? '');
                    $transcriptOutput = $toolOutput;
                }
                if (in_array($item_type, ['file_change', 'web_search', 'todo_list'], true)) {
                    $toolOutput = json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
                    $transcriptOutput = $toolOutput;
                }
                $toolFailed =
                    ($item['status'] ?? 'completed') !== 'completed' ||
                    ($item_type === 'command_execution' && (int) ($item['exit_code'] ?? 0) !== 0);
                if ($type === 'item.started') {
                    $ready = $item_type !== 'command_execution' || trim((string) ($item['command'] ?? '')) !== '';
                    if ($ready) {
                        $this->emitTranscript(
                            (string) ($item['id'] ?? ''),
                            $label,
                            'running',
                            $isMcp ? $tool_input : null
                        );
                    }
                    return;
                }
                if ($hiddenSkill) {
                    $skillInput = json_encode($tool_input, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
                    preg_match('#/skills/([^/]+)/SKILL\.md#', $skillInput, $skillMatch);
                    $this->emitTranscript(
                        (string) ($item['id'] ?? ''),
                        'Loaded skill ' . ($skillMatch[1] ?? 'instructions'),
                        $toolFailed ? 'error' : 'completed',
                        $toolFailed ? $transcriptOutput : null
                    );
                }
                if (!$hiddenSkill) {
                    $this->emitTranscript(
                        (string) ($item['id'] ?? ''),
                        $label,
                        $toolFailed ? 'error' : 'completed',
                        $transcriptOutput
                    );
                }

                // the cli runs its tools itself, so every completed operation is
                // retained in the canonical session even when the visible transcript is shortened
                if ($tool_input === []) {
                    $tool_input = new \stdClass();
                }
                $result->result->content[] = (object) [
                    'type' => 'tool_use',
                    'id' => (string) ($item['id'] ?? ''),
                    'name' => $tool_name,
                    'input' => $tool_input
                ];
                $result->result->content[] = (object) [
                    'type' => 'tool_result',
                    'tool_use_id' => (string) ($item['id'] ?? ''),
                    'is_error' => $toolFailed,
                    'content' => $toolOutput
                ];
                return;
            }
        }

        // codex reports whole completed items instead of token deltas, so one
        // item becomes one closed content block
        if ($type === 'item.completed') {
            $item_type = $event['item']['type'] ?? null;
            $text = (string) ($event['item']['text'] ?? '');
            if ($text === '' || !in_array($item_type, ['agent_message', 'reasoning'], true)) {
                return;
            }
            $thinking = $item_type === 'reasoning';
            $this->emitAnthropicEvent($emit, [
                'type' => 'content_block_start',
                'index' => 0,
                'content_block' => $thinking ? ['type' => 'thinking', 'thinking' => ''] : ['type' => 'text', 'text' => '']
            ]);
            $this->emitAnthropicEvent($emit, [
                'type' => 'content_block_delta',
                'index' => 0,
                'delta' => $thinking
                    ? ['type' => 'thinking_delta', 'thinking' => $text]
                    : ['type' => 'text_delta', 'text' => $text]
            ]);
            $this->emitAnthropicEvent($emit, ['type' => 'content_block_stop', 'index' => 0]);
            if (!$thinking) {
                $result->result->content[] = (object) ['type' => 'text', 'text' => $text];
            }
            return;
        }

        if ($type === 'turn.completed') {
            $result->result->stop_reason = 'end_turn';
            $result->result->usage = (object) [
                'input_tokens' => (int) ($event['usage']['input_tokens'] ?? 0),
                'cache_creation_input_tokens' => (int) ($event['usage']['cache_write_input_tokens'] ?? 0),
                'cache_read_input_tokens' => (int) ($event['usage']['cached_input_tokens'] ?? 0),
                'output_tokens' => (int) ($event['usage']['output_tokens'] ?? 0)
            ];
            $this->emitAnthropicEvent($emit, [
                'type' => 'message_delta',
                'delta' => ['stop_reason' => 'end_turn', 'stop_sequence' => null],
                'usage' => (array) $result->result->usage
            ]);
            $this->emitAnthropicEvent($emit, ['type' => 'message_stop']);
            return;
        }

        if ($type === 'turn.failed' || $type === 'error') {
            $result->result->error = (object) [
                'message' => (string) ($event['error']['message'] ?? ($event['message'] ?? 'codex turn failed'))
            ];
        }
    }
}

class ai_opencode extends ai_harness
{
    public ?string $provider = 'OpenCode';

    public ?string $title = 'OpenCode';

    public ?string $name = 'opencode';

    public ?string $icon = <<<'SVG'
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><rect width="512" height="512" fill="#131010"/><path d="M320 224v128H192V224h128Z" fill="#5A5858"/><path fill="white" fill-rule="evenodd" d="M384 416H128V96h256v320Zm-64-256H192v192h128V160Z" clip-rule="evenodd"/></svg>
    SVG;

    protected ?string $url = null;

    protected bool $message_started = false;

    protected int $content_block_index = 0;

    public array $models = [
        [
            'name' => 'opencode-go/kimi-k3',
            'context_length' => 1048576,
            'max_output_tokens' => 131072,
            'costs' => ['input' => 3E-6, 'input_cached' => 3E-7, 'output' => 1.5E-5],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => true
        ],
        [
            'name' => 'opencode-go/grok-4.5',
            'context_length' => 500000,
            'max_output_tokens' => 500000,
            'costs' => ['input' => 2E-6, 'input_cached' => 5E-7, 'output' => 6E-6],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/glm-5.2',
            'context_length' => 1000000,
            'max_output_tokens' => 131072,
            'costs' => ['input' => 1.4E-6, 'input_cached' => 2.6E-7, 'output' => 4.4E-6],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/qwen3.7-max',
            'context_length' => 1000000,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 2.5E-6, 'input_cached' => 5E-7, 'output' => 7.5E-6],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/minimax-m3',
            'context_length' => 1000000,
            'max_output_tokens' => 131072,
            'costs' => ['input' => 3E-7, 'input_cached' => 6E-8, 'output' => 1.2E-6],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/deepseek-v4-pro',
            'context_length' => 1000000,
            'max_output_tokens' => 384000,
            'costs' => ['input' => 4.35E-7, 'input_cached' => 3.625E-9, 'output' => 8.7E-7],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/kimi-k2.6',
            'context_length' => 262144,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 9.499999999999999E-7, 'input_cached' => 1.6E-7, 'output' => 4E-6],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/mimo-v2.5-pro',
            'context_length' => 1048576,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 4.35E-7, 'input_cached' => 3.625E-9, 'output' => 8.7E-7],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/kimi-k2.7-code',
            'context_length' => 262144,
            'max_output_tokens' => 262144,
            'costs' => ['input' => 9.499999999999999E-7, 'input_cached' => 1.9E-7, 'output' => 4E-6],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/hy3',
            'context_length' => 256000,
            'max_output_tokens' => 64000,
            'costs' => ['input' => 1.4E-7, 'input_cached' => 3.5E-8, 'output' => 5.8E-7],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/deepseek-v4-flash',
            'context_length' => 1000000,
            'max_output_tokens' => 384000,
            'costs' => ['input' => 1.4E-7, 'input_cached' => 2.8E-9, 'output' => 2.8E-7],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/glm-5.1',
            'context_length' => 202752,
            'max_output_tokens' => 32768,
            'costs' => ['input' => 1.4E-6, 'input_cached' => 2.6E-7, 'output' => 4.4E-6],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/qwen3.6-plus',
            'context_length' => 1000000,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 5E-7, 'input_cached' => 5.0000000000000004E-8, 'output' => 3E-6],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/qwen3.7-plus',
            'context_length' => 1000000,
            'max_output_tokens' => 65536,
            'costs' => ['input' => 4.0000000000000003E-7, 'input_cached' => 4E-8, 'output' => 1.6000000000000001E-6],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/minimax-m2.7',
            'context_length' => 204800,
            'max_output_tokens' => 131072,
            'costs' => ['input' => 3E-7, 'input_cached' => 6E-8, 'output' => 1.2E-6],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => false,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ],
        [
            'name' => 'opencode-go/mimo-v2.5',
            'context_length' => 1000000,
            'max_output_tokens' => 128000,
            'costs' => ['input' => 1.4E-7, 'input_cached' => 2.8E-9, 'output' => 2.8E-7],
            'supports_temperature' => false,
            'supports_tools' => true,
            'supports_text_to_image' => false,
            'supports_text_to_audio' => false,
            'supports_image_to_text' => true,
            'supports_audio_to_text' => false,
            'supports_effort' => true,
            'efforts' => ['minimal', 'low', 'medium', 'high', 'max'],
            'default' => false
        ]
    ];

    protected function binaryName(): string
    {
        return 'opencode';
    }

    public function fetchModels(): array
    {
        return $this->normalizeAndEnrichModels($this->models);
    }

    public function getCliUsageLimits(): ?array
    {
        $now = time();
        $limits = [
            '5-hour' => ['from' => $now - 5 * 60 * 60, 'limit_usd' => 12.0],
            // calibrated against a real exhaustion: the gateway refused at $23.31 local spend
            'weekly' => ['from' => $now - 7 * 24 * 60 * 60, 'limit_usd' => 20.0],
            'monthly' => ['from' => $now - 30 * 24 * 60 * 60, 'limit_usd' => 60.0]
        ];
        $messages = $this->readOpenCodeUsageMessages($limits['monthly']['from'] * 1000);
        if ($messages === null) {
            return null;
        }

        foreach ($limits as $type => $limit) {
            $limits[$type] = [
                'type' => $type,
                'scope' => null,
                'percent used' => 0.0,
                'resets_at' => null,
                'estimated' => true,
                'used_usd' => 0.0,
                'limit_usd' => $limit['limit_usd'],
                'requests' => 0,
                'tokens' => [
                    'total' => 0,
                    'input' => 0,
                    'output' => 0,
                    'reasoning' => 0,
                    'cache_read' => 0,
                    'cache_write' => 0
                ],
                'models' => [],
                'from' => $limit['from']
            ];
        }

        foreach ($messages as $message) {
            $data = json_decode((string) ($message['data'] ?? ''), true);
            if (!is_array($data)) {
                continue;
            }
            $createdAt = (int) floor((float) ($message['time_created'] ?? 0) / 1000);
            $tokens = is_array($data['tokens'] ?? null) ? $data['tokens'] : [];
            $cache = is_array($tokens['cache'] ?? null) ? $tokens['cache'] : [];
            foreach ($limits as &$limit) {
                if ($createdAt < $limit['from']) {
                    continue;
                }
                $limit['requests']++;
                $limit['used_usd'] += (float) ($data['cost'] ?? 0);
                foreach (['total', 'input', 'output', 'reasoning'] as $tokenType) {
                    $limit['tokens'][$tokenType] += (int) ($tokens[$tokenType] ?? 0);
                }
                $limit['tokens']['cache_read'] += (int) ($cache['read'] ?? 0);
                $limit['tokens']['cache_write'] += (int) ($cache['write'] ?? 0);
                $model = trim((string) ($data['modelID'] ?? ''));
                if ($model !== '' && !in_array($model, $limit['models'], true)) {
                    $limit['models'][] = $model;
                }
            }
            unset($limit);
        }

        $serverLimits = $this->fetchOpenCodeServerLimits();
        foreach ($limits as &$limit) {
            unset($limit['from']);
            $limit['used_usd'] = round($limit['used_usd'], 8);
            // the local spend is only a guess at where the window stands, so it must never
            // claim the window is spent — a wrong 100 would take the model out of a caller's
            // rotation for nothing. only the gateway below is allowed to say that
            $limit['percent used'] = round(min(99, $limit['used_usd'] / $limit['limit_usd'] * 100), 2);
            if (isset($serverLimits[$limit['type']])) {
                $limit['percent used'] = $serverLimits[$limit['type']]['percent used'];
                $limit['resets_at'] = $serverLimits[$limit['type']]['resets_at'];
                $limit['estimated'] = false;
            }
            sort($limit['models']);
        }
        unset($limit);
        return array_values($limits);
    }

    /**
     * Read the assistant messages the local spend estimate is built from.
     *
     * The database sits next to the cli, so a remote harness cannot open the file at all and
     * has to let the other machine run the query. Returns null when no database was reachable
     * (which is not the same as an empty window: that one legitimately means "nothing spent").
     *
     * @return array|null
     */
    private function readOpenCodeUsageMessages(int $from): ?array
    {
        $query =
            "SELECT time_created, data FROM message
             WHERE json_extract(data, '$.role') = 'assistant'
               AND json_extract(data, '$.providerID') = 'opencode-go'
               AND time_created >= {$from}
             ORDER BY time_created ASC";
        if ($this->isRemote()) {
            $command = array_merge($this->sshCommand(), [
                'for candidate in "$XDG_DATA_HOME/opencode/opencode.db" "$HOME/.local/share/opencode/opencode.db" ' .
                '/root/.local/share/opencode/opencode.db; do if [ -f "$candidate" ]; then ' .
                'exec sqlite3 -readonly -json "$candidate" ' .
                escapeshellarg($query) .
                '; fi; done; exit 1'
            ]);
            exec(implode(' ', array_map('escapeshellarg', $command)) . ' 2>/dev/null', $output, $status);
            if ($status !== 0) {
                return null;
            }
            $rows = json_decode(implode(PHP_EOL, $output), true);
            return is_array($rows) ? $rows : [];
        }
        $database = self::getOpenCodeDatabasePath();
        if ($database === null) {
            return null;
        }
        try {
            $connection = new \PDO('sqlite:' . $database, options: [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
            return $connection->query($query)->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\PDOException) {
            return null;
        }
    }

    /**
     * Read exact dashboard limits when configured, otherwise ask the gateway whether a window is spent.
     *
     * There is no usage endpoint — the reset time exists only in the "Retry-After" header of the
     * 429 the completions endpoint answers with. The limit is checked before the payload is
     * validated, so an intentionally invalid zero-token request is enough to trigger it and nothing is ever generated.
     *
     * @return array<string,array{'percent used': float, resets_at: string}>
     */
    private function fetchOpenCodeServerLimits(): array
    {
        $authCookie = trim((string) getenv('OPENCODE_GO_AUTH_COOKIE'));
        $cacheIdentity = $authCookie !== ''
            ? substr(hash('sha256', $authCookie), 0, 16)
            : 'local';
        $cache_file =
            rtrim(sys_get_temp_dir(), '/') .
            '/aihelper-opencode-limit-' .
            (function_exists('posix_geteuid') ? posix_geteuid() : getmyuid()) .
            '-' .
            $cacheIdentity .
            '.json';
        $cached = is_file($cache_file) ? json_decode((string) file_get_contents($cache_file), true) : null;
        if (is_array($cached) && ($cached['checked_at'] ?? 0) > time() - 60) {
            if (is_array($cached['limits'] ?? null)) {
                return $cached['limits'];
            }
            $legacyLimit = is_array($cached['limit'] ?? null) ? $cached['limit'] : null;
            return $legacyLimit === null
                ? []
                : [
                    $legacyLimit['type'] => [
                        'percent used' => 100.0,
                        'resets_at' => $legacyLimit['resets_at']
                    ]
                ];
        }

        if ($authCookie !== '') {
            $curl = curl_init('https://opencode.ai/go');
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html',
                    'Cookie: auth=' . $authCookie,
                    'User-Agent: Mozilla/5.0'
                ]
            ]);
            $workspaceHtml = curl_exec($curl);
            $workspaceStatus = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            preg_match_all('/\bwrk_[A-Z0-9]+\b/i', is_string($workspaceHtml) ? $workspaceHtml : '', $workspaceMatches);
            $workspaceIds = array_values(array_unique($workspaceMatches[0] ?? []));
            $workspaceId = $workspaceStatus === 200 && count($workspaceIds) === 1 ? $workspaceIds[0] : null;
        }
        if (isset($workspaceId)) {
            $curl = curl_init(
                'https://opencode.ai/workspace/' . rawurlencode($workspaceId) . '/go'
            );
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html',
                    'Cookie: auth=' . $authCookie,
                    'User-Agent: Mozilla/5.0'
                ]
            ]);
            $html = curl_exec($curl);
            $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($status === 200 && is_string($html)) {
                $dashboardLimits = self::parseOpenCodeDashboardLimits($html);
                if ($dashboardLimits !== []) {
                    file_put_contents(
                        $cache_file,
                        json_encode(['checked_at' => time(), 'limits' => $dashboardLimits]),
                        LOCK_EX
                    );
                    chmod($cache_file, 0600);
                    return $dashboardLimits;
                }
            }
        }

        $key = null;
        foreach (
            [getenv('XDG_DATA_HOME') ?: null, (getenv('HOME') ?: '/root') . '/.local/share', '/root/.local/share']
            as $base
        ) {
            // a remote harness keeps its credentials on the other machine
            $auth = $base === null ? null : $this->readCliAuthFile(rtrim($base, '/') . '/opencode/auth.json');
            if ($auth === null) {
                continue;
            }
            $key = json_decode($auth, true)['opencode-go']['key'] ?? null;
            if (is_string($key) && trim($key) !== '') {
                break;
            }
        }
        if (!is_string($key) || trim($key) === '') {
            return [];
        }

        $curl = curl_init('https://opencode.ai/zen/go/v1/chat/completions');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_POST => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key, 'Content-Type: application/json'],
            // the models carry an "opencode-go/" prefix internally, the gateway rejects it
            CURLOPT_POSTFIELDS => json_encode([
                'model' => basename($this->model ?? '') ?: 'glm-5.2',
                'messages' => [['role' => 'user', 'content' => '']],
                'max_tokens' => 0
            ])
        ]);
        $raw = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = (int) curl_getinfo($curl, CURLINFO_HEADER_SIZE);

        $limits = [];
        if ($status === 429 && is_string($raw)) {
            $body = json_decode(substr($raw, $header_size), true);
            $type = trim((string) ($body['metadata']['limitName'] ?? ''));
            preg_match('/^retry-after:\s*(\d+)/mi', substr($raw, 0, $header_size), $match);
            if ($type !== '' && isset($match[1])) {
                $limits[$type] = [
                    'percent used' => 100.0,
                    'resets_at' => date('c', time() + (int) $match[1])
                ];
            }
        }
        // a failed probe must not be cached as "not exhausted" — only a conclusive answer counts
        if ($status !== 0) {
            @file_put_contents(
                $cache_file,
                json_encode(['checked_at' => time(), 'limits' => $limits]),
                LOCK_EX
            );
            @chmod($cache_file, 0600);
        }
        return $limits;
    }

    /**
     * @return array<string,array{'percent used': float, resets_at: string}>
     */
    private static function parseOpenCodeDashboardLimits(string $html): array
    {
        $limits = [];
        foreach (['rolling' => '5-hour', 'weekly' => 'weekly', 'monthly' => 'monthly'] as $source => $type) {
            if (preg_match('/' . $source . 'Usage:\$R\[\d+\]=\{([^}]+)\}/', $html, $usageMatch) !== 1) {
                continue;
            }
            if (
                preg_match('/usagePercent:(-?\d+(?:\.\d+)?)/', $usageMatch[1], $percentMatch) !== 1 ||
                preg_match('/resetInSec:(-?\d+(?:\.\d+)?)/', $usageMatch[1], $resetMatch) !== 1
            ) {
                continue;
            }
            $limits[$type] = [
                'percent used' => max(0.0, min(100.0, (float) $percentMatch[1])),
                'resets_at' => date('c', time() + max(0, (int) round((float) $resetMatch[1])))
            ];
        }
        return $limits;
    }

    protected function harnessEnvironmentOverrides(): array
    {
        $overrides = array_merge(
            [
                'IS_SANDBOX' => '1',
                'OPENCODE_DISABLE_CLAUDE_CODE' => 'true',
                'OPENCODE_DISABLE_EXTERNAL_SKILLS' => 'true'
            ],
            $this->harnessMcpTokenEnvironment()
        );
        if ($this->isolate_harness_config === true) {
            $overrides['OPENCODE_DISABLE_CLAUDE_CODE_SKILLS'] = 'true';
            $overrides['OPENCODE_DISABLE_PROJECT_CONFIG'] = 'true';
            $overrides['OPENCODE_DISABLE_DEFAULT_PLUGINS'] = 'true';
            $overrides['OPENCODE_PURE'] = '1';
        }

        $config = [];
        foreach ($this->harnessMcpServers() as $name => $server) {
            $entry = ['type' => 'remote', 'url' => $server['url'], 'enabled' => true, 'timeout' => 300000];
            if ($server['token'] !== null && trim((string) $server['token']) !== '') {
                // opencode interpolates "{env:VAR}" itself, so the token stays
                // out of the config string that is passed on the command line
                $entry['headers'] = ['Authorization' => 'Bearer {env:' . $this->harnessMcpTokenVariable($name) . '}'];
            }
            $config['mcp'][$name] = $entry;
        }
        if ($this->isolate_harness_config === true) {
            $config['instructions'] = [];
        }
        if ($this->system_prompt !== null) {
            $config['agent']['build']['prompt'] = $this->system_prompt;
        }
        $skills = $this->placeSkills('skills');
        if ($skills !== null) {
            $config['skills']['paths'] = [$skills];
        }
        if ($config !== []) {
            $config['$schema'] = 'https://opencode.ai/config.json';
            // the config carries the whole system prompt, which is far too large for an
            // environment value — it counts against the same limit as the arguments
            $overrides['OPENCODE_CONFIG'] = $this->payloadFile(
                'opencode.json',
                json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        }
        return $overrides;
    }

    protected function harnessPromptFilePaths(): array
    {
        return [];
    }

    protected function buildArgs(): array
    {
        $args = ['run', '--continue', '--format', 'json', '--auto'];
        foreach ($this->harnessFilePaths() as $path) {
            $args[] = '--file';
            $args[] = $path;
        }
        if ($this->model !== null) {
            $args[] = '--model';
            $args[] = $this->model;
        }
        if (in_array($this->effort, ['minimal', 'low', 'medium', 'high', 'max'], true)) {
            $args[] = '--variant';
            $args[] = $this->effort;
        }
        return $args;
    }

    protected function handleEvent(array $event, object $result, ?\Closure $emit): void
    {
        $type = $event['type'] ?? null;
        if ($type === 'step_start') {
            if (!empty($event['sessionID'])) {
                $this->log((string) $event['sessionID'], 'harness session');
            }
            if ($this->message_started === false) {
                $this->message_started = true;
                $this->content_block_index = 0;
                $this->emitAnthropicEvent($emit, [
                    'type' => 'message_start',
                    'message' => [
                        'type' => 'message',
                        'role' => 'assistant',
                        'model' => (string) $this->model,
                        'content' => [],
                        'stop_reason' => null,
                        'usage' => ['input_tokens' => 0, 'output_tokens' => 0]
                    ]
                ]);
            }
            return;
        }

        if (in_array($type, ['text', 'reasoning'], true)) {
            $text = (string) ($event['part']['text'] ?? '');
            if (trim($text) === '') {
                return;
            }
            $thinking = $type === 'reasoning';
            $index = $this->content_block_index++;
            $this->emitAnthropicEvent($emit, [
                'type' => 'content_block_start',
                'index' => $index,
                'content_block' => $thinking ? ['type' => 'thinking', 'thinking' => ''] : ['type' => 'text', 'text' => '']
            ]);
            $this->emitAnthropicEvent($emit, [
                'type' => 'content_block_delta',
                'index' => $index,
                'delta' => $thinking
                    ? ['type' => 'thinking_delta', 'thinking' => $text]
                    : ['type' => 'text_delta', 'text' => $text]
            ]);
            $this->emitAnthropicEvent($emit, ['type' => 'content_block_stop', 'index' => $index]);
            if (!$thinking) {
                $result->result->content[] = (object) ['type' => 'text', 'text' => $text];
            }
            return;
        }

        // the cli runs its tools itself, so the calls only become visible to the
        // caller when they are written into the session like a native provider does
        if ($type === 'tool') {
            $call_id = (string) ($event['part']['callID'] ?? '');
            $state = is_array($event['part']['state'] ?? null) ? $event['part']['state'] : [];
            $status = (string) ($state['status'] ?? '');
            $toolName = (string) ($event['part']['tool'] ?? 'tool');
            $hiddenSkill = $this->isHarnessSkillTool($toolName, $state['input'] ?? []);
            if (
                $call_id !== '' &&
                (isset($state['input']) || in_array($status, ['completed', 'error'], true)) &&
                !$hiddenSkill
            ) {
                $this->emitTranscript(
                    $call_id,
                    $this->toolTranscriptLabel($toolName, $state['input'] ?? []),
                    $status === 'error' ? 'error' : ($status === 'completed' ? 'completed' : 'running'),
                    in_array($status, ['completed', 'error'], true)
                        ? ($state['output'] ?? ($state['error'] ?? ''))
                        : ($state['input'] ?? null)
                );
            }
            // opencode reports the part on every state change, so only the terminal one is recorded
            if ($call_id === '' || !in_array($status, ['completed', 'error'], true)) {
                return;
            }
            if ($hiddenSkill) {
                $skillInput = json_encode($state['input'] ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
                preg_match('#/skills/([^/]+)/SKILL\.md#', $skillInput, $skillMatch);
                $this->emitTranscript(
                    $call_id,
                    'Loaded skill ' . ($skillMatch[1] ?? 'instructions'),
                    $status === 'error' ? 'error' : 'completed',
                    $status === 'error' ? ($state['error'] ?? '') : null
                );
            }
            $toolOutput = $state['output'] ?? ($state['error'] ?? '');
            if (!is_string($toolOutput)) {
                $toolOutput = json_encode(
                    $toolOutput,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
                ) ?: '';
            }
            $result->result->content[] = (object) [
                'type' => 'tool_use',
                'id' => $call_id,
                'name' => (string) ($event['part']['tool'] ?? ''),
                'input' => $state['input'] ?? []
            ];
            $result->result->content[] = (object) [
                'type' => 'tool_result',
                'tool_use_id' => $call_id,
                'is_error' => $status === 'error',
                'content' => $toolOutput
            ];
            return;
        }

        if ($type === 'step_finish') {
            $tokens = is_array($event['part']['tokens'] ?? null) ? $event['part']['tokens'] : [];
            $cache = is_array($tokens['cache'] ?? null) ? $tokens['cache'] : [];
            $result->result->usage->input_tokens += (int) ($tokens['input'] ?? 0);
            $result->result->usage->cache_creation_input_tokens += (int) ($cache['write'] ?? 0);
            $result->result->usage->cache_read_input_tokens += (int) ($cache['read'] ?? 0);
            $result->result->usage->output_tokens += (int) ($tokens['output'] ?? 0);
            $this->harness_costs = ($this->harness_costs ?? 0) + (float) ($event['part']['cost'] ?? 0);
            if (($event['part']['reason'] ?? null) !== 'stop') {
                return;
            }
            $result->result->stop_reason = 'end_turn';
            $this->emitAnthropicEvent($emit, [
                'type' => 'message_delta',
                'delta' => ['stop_reason' => 'end_turn', 'stop_sequence' => null],
                'usage' => (array) $result->result->usage
            ]);
            $this->emitAnthropicEvent($emit, ['type' => 'message_stop']);
            $this->message_started = false;
            return;
        }

        if ($type === 'error') {
            $error = $event['error'] ?? null;
            $message = is_array($error)
                ? (string) ($error['data']['message'] ?? ($error['message'] ?? ($error['name'] ?? 'OpenCode turn failed')))
                : (string) ($error ?? 'OpenCode turn failed');
            $result->result->error = (object) ['message' => $message];
            $this->message_started = false;
        }
    }
}
