<?php
namespace vielhuber\aihelper;

use vielhuber\stringhelper\__;

abstract class aihelper
{
    protected $provider = null;
    protected $name = null;
    protected $url = null;
    protected $models = [];

    protected $model = null;
    protected $temperature = null;
    protected $api_key = null;
    protected $log = null;
    protected $max_tries = null;
    protected $mcp_servers = null;

    protected $stream = null;
    protected $stream_response = null;
    protected $stream_event = null;
    protected $stream_buffer_in = null;
    protected $stream_buffer_data = null;

    protected $session_id = null;
    protected static $sessions = [];

    public static function create(
        $service,
        $model = null,
        $temperature = null,
        $api_key = null,
        $log = null,
        $max_tries = null,
        $mcp_servers = null,
        $session_id = null,
        $history = null,
        $stream = null
    ) {
        if ($service === 'chatgpt') {
            return new ai_chatgpt(
                $model,
                $temperature,
                $api_key,
                $log,
                $max_tries,
                $mcp_servers,
                $session_id,
                $history,
                $stream
            );
        }
        if ($service === 'claude') {
            return new ai_claude(
                $model,
                $temperature,
                $api_key,
                $log,
                $max_tries,
                $mcp_servers,
                $session_id,
                $history,
                $stream
            );
        }
        if ($service === 'gemini') {
            return new ai_gemini(
                $model,
                $temperature,
                $api_key,
                $log,
                $max_tries,
                $mcp_servers,
                $session_id,
                $history,
                $stream
            );
        }
        if ($service === 'grok') {
            return new ai_grok(
                $model,
                $temperature,
                $api_key,
                $log,
                $max_tries,
                $mcp_servers,
                $session_id,
                $history,
                $stream
            );
        }
        if ($service === 'deepseek') {
            return new ai_deepseek(
                $model,
                $temperature,
                $api_key,
                $log,
                $max_tries,
                $mcp_servers,
                $session_id,
                $history,
                $stream
            );
        }
        return null;
    }

    public function __construct(
        $model,
        $temperature,
        $api_key,
        $log = null,
        $max_tries = null,
        $mcp_servers = null,
        $session_id = null,
        $history = null,
        $stream = null
    ) {
        if ($model === null) {
            $model = $this->getDefaultModel();
        }
        if ($temperature === null) {
            $temperature = 1.0;
        }
        if ($log !== null) {
            $this->log = $log;
        }
        $this->max_tries = $max_tries !== null ? $max_tries : 1;
        if ($mcp_servers !== null) {
            if (is_array(current($mcp_servers))) {
                $this->mcp_servers = $mcp_servers;
            } else {
                $this->mcp_servers = [$mcp_servers];
            }
        }
        $this->stream = $stream === true ? true : false;

        $this->model = $model;
        $this->temperature = $temperature;
        $this->api_key = $api_key;
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

    public function ask($prompt = null, $files = null)
    {
        $return = ['response' => null, 'success' => false, 'content' => [], 'costs' => 0.0];
        $max_tries = $this->max_tries;
        while ($return['success'] === false && $max_tries > 0) {
            if ($max_tries < $this->max_tries) {
                $this->log('⚠️ tries left: ' . $max_tries);
            }
            $return = $this->askThis($prompt, $files, $max_tries === $this->max_tries);
            $this->log($return, 'return');
            $max_tries--;
        }
        return $return;
    }

    abstract protected function askThis($prompt = null, $files = null, $add_prompt_to_session = true);

    protected function parseJson($msg)
    {
        if (strpos(trim($msg), '```json') === 0 || __::string_is_json($msg)) {
            $msg = json_decode(trim(rtrim(ltrim(ltrim(trim($msg), '```json'), '```'), '```')));
        }
        return $msg;
    }

    public function enable_log($filename)
    {
        $this->log = $filename;
    }

    public function disable_log()
    {
        $this->log = null;
    }

    public function getSessionId()
    {
        return $this->session_id;
    }

    public function getSessionContent()
    {
        return self::$sessions[$this->session_id];
    }

    public function log($msg, $prefix = null)
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
                \DateTime::createFromFormat('U.u', microtime(true))->format('Y-m-d H:i:s.u') .
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

    public function getTestModels()
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

    public function getDefaultModel()
    {
        foreach ($this->models as $models__value) {
            if ($models__value['default'] === true) {
                return $models__value['name'];
            }
        }
        return null;
    }

    protected function getMaxTokensForModel()
    {
        foreach ($this->models as $models__value) {
            if ($models__value['name'] === $this->model) {
                return $models__value['max_tokens'];
            }
        }
        return 4096;
    }

    protected function addCosts($response, &$return)
    {
        $this->log($response, 'add costs');

        $input_tokens = 0;
        if (
            __::x(@$response) &&
            __::x(@$response->result) &&
            __::x(@$response->result->usage) &&
            __::x(@$response->result->usage->input_tokens)
        ) {
            $input_tokens += $response->result->usage->input_tokens;
        }
        if (
            __::x(@$response) &&
            __::x(@$response->result) &&
            __::x(@$response->result->usageMetadata) &&
            __::x(@$response->result->usageMetadata->promptTokenCount)
        ) {
            $input_tokens += $response->result->usageMetadata->promptTokenCount;
        }

        $input_cached_tokens = 0;
        if (
            __::x(@$response) &&
            __::x(@$response->result) &&
            __::x(@$response->result->usage) &&
            __::x(@$response->result->usage->input_tokens_details) &&
            __::x(@$response->result->usage->input_tokens_details->cached_tokens)
        ) {
            $input_cached_tokens += $response->result->usage->input_tokens_details->cached_tokens;
        }
        if (
            __::x(@$response) &&
            __::x(@$response->result) &&
            __::x(@$response->result->usage) &&
            __::x(@$response->result->usage->cache_creation_input_tokens)
        ) {
            $input_cached_tokens += $response->result->usage->cache_creation_input_tokens;
        }
        if (
            __::x(@$response) &&
            __::x(@$response->result) &&
            __::x(@$response->result->usage) &&
            __::x(@$response->result->usage->cache_read_input_tokens)
        ) {
            $input_cached_tokens += $response->result->usage->cache_read_input_tokens;
        }

        $output_tokens = 0;
        if (
            __::x(@$response) &&
            __::x(@$response->result) &&
            __::x(@$response->result->usage) &&
            __::x(@$response->result->usage->output_tokens)
        ) {
            $output_tokens += $response->result->usage->output_tokens;
        }
        if (
            __::x(@$response) &&
            __::x(@$response->result) &&
            __::x(@$response->result->usageMetadata) &&
            __::x(@$response->result->usageMetadata->candidatesTokenCount)
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
        $return['costs'] += (float) round($costs, 5);
    }

    protected function getStreamCallback()
    {
        if ($this->stream === false) {
            return null;
        }

        $this->stream_event = null;
        $this->stream_buffer_in = '';
        $this->stream_buffer_data = '';

        if ($this->provider === 'anthropic') {
            // mimic non stream result
            $this->stream_response = (object) [
                'result' => (object) [
                    'content' => [
                        (object) [
                            'type' => 'text',
                            'text' => ''
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

                // parse line by line
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
                            $this->stream_buffer_data === '' ? $dataLine : $this->stream_buffer_data . "\n" . $dataLine;
                        continue;
                    }

                    if ($line === '' && $this->stream_event !== null && $this->stream_buffer_data !== '') {
                        $parsed = json_decode($this->stream_buffer_data, true);

                        if (isset($parsed['type']) && $parsed['type'] === 'content_block_delta') {
                            if (isset($parsed['delta']['text'])) {
                                $text = $parsed['delta']['text'];
                                $this->stream_response->result->content[0]->text .= $text;

                                echo 'data: ' .
                                    json_encode([
                                        'id' => uniqid(),
                                        'choices' => [['delta' => ['content' => $text]]]
                                    ]) .
                                    "\n\n";
                                if (ob_get_level() > 0) {
                                    @ob_flush();
                                }
                                @flush();
                            }
                        }

                        if (isset($parsed['usage'])) {
                            $this->stream_response->result->usage->input_tokens += @$parsed['usage']['input_tokens'];
                            $this->stream_response->result->usage->cache_creation_input_tokens += @$parsed['usage'][
                                'cache_creation_input_tokens'
                            ];
                            $this->stream_response->result->usage->cache_read_input_tokens += @$parsed['usage'][
                                'cache_read_input_tokens'
                            ];
                            $this->stream_response->result->usage->output_tokens += @$parsed['usage']['output_tokens'];
                        }

                        if (isset($parsed['type']) && $parsed['type'] === 'message_stop') {
                            echo "data: [DONE]\n\n";
                            if (ob_get_level() > 0) {
                                @ob_flush();
                            }
                            @flush();
                        }
                    }

                    if ($line === '') {
                        $this->stream_event = null;
                        $this->stream_buffer_data = '';
                        continue;
                    }
                }
                return strlen($chunk);
            };
        }

        if ($this->provider === 'openai') {
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

                // parse line by line
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
                            $this->stream_buffer_data === '' ? $dataLine : $this->stream_buffer_data . "\n" . $dataLine;
                        continue;
                    }

                    if ($line === '' && $this->stream_event !== null && $this->stream_buffer_data !== '') {
                        $parsed = json_decode($this->stream_buffer_data, true);

                        if (isset($parsed['type']) && $parsed['type'] === 'response.output_text.delta') {
                            if (isset($parsed['delta'])) {
                                $text = $parsed['delta'];
                                $this->stream_response->result->output[0]->content[0]->text .= $text;

                                echo 'data: ' .
                                    json_encode([
                                        'id' => uniqid(),
                                        'choices' => [['delta' => ['content' => $text]]]
                                    ]) .
                                    "\n\n";
                                if (ob_get_level() > 0) {
                                    @ob_flush();
                                }
                                @flush();
                            }
                        }

                        if (isset($parsed['response']) && isset($parsed['response']['usage'])) {
                            $this->stream_response->result->usage->input_tokens += @$parsed['response']['usage'][
                                'input_tokens'
                            ];
                            $this->stream_response->result->usage->cache_creation_input_tokens += @$parsed['response'][
                                'usage'
                            ]['input_tokens_details']['cached_tokens'];
                            $this->stream_response->result->usage->cache_read_input_tokens += 0;
                            $this->stream_response->result->usage->output_tokens += @$parsed['response']['usage'][
                                'output_tokens'
                            ];
                        }

                        if (isset($parsed['type']) && $parsed['type'] === 'response.completed') {
                            $this->stream_response->result->id = @$parsed['response']['id'];
                            echo "data: [DONE]\n\n";
                            if (ob_get_level() > 0) {
                                @ob_flush();
                            }
                            @flush();
                        }
                    }

                    if ($line === '') {
                        $this->stream_event = null;
                        $this->stream_buffer_data = '';
                        continue;
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
            @ob_end_clean();
        }
        // set php settings
        @ini_set('zlib.output_compression', '0');
        @ini_set('output_buffering', '0');
        @ini_set('implicit_flush', '1');
        // 2k padding (for browsers)
        @ob_implicit_flush(true);
        echo ': pad ' . str_repeat(' ', 2048) . "\n\n";
        if (ob_get_level() > 0) {
            @ob_flush();
        }
        @flush();

        return $stream_callback;
    }
}

class ai_chatgpt extends aihelper
{
    protected $provider = 'openai';

    protected $name = 'chatgpt';

    protected $url = 'https://api.openai.com/v1';

    protected $models = [
        [
            'name' => 'gpt-5',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.000005, 'output' => 0.000015],
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'gpt-5-mini',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.0000005, 'input_cached' => 0.0000005, 'output' => 0.0000015],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-5-nano',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.00000025, 'input_cached' => 0.00000025, 'output' => 0.00000075],
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'gpt-4.1',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.000005, 'output' => 0.000015],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.000005, 'output' => 0.000015],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-mini',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.00000015, 'input_cached' => 0.00000015, 'output' => 0.0000006],
            'default' => false,
            'test' => false
        ]
    ];

    protected function askThis($prompt = null, $files = null, $add_prompt_to_session = true)
    {
        $return = ['response' => null, 'success' => false, 'content' => [], 'costs' => 0.0];

        if (__::nx($this->model) || __::nx($this->api_key) || __::nx($this->session_id)) {
            $return['response'] = 'data missing.';
            return $return;
        }

        if (__::nx($prompt)) {
            $return['response'] = 'prompt missing.';
            return $return;
        }

        // trim prompt
        $prompt = __trim_whitespace(__trim_every_line($prompt));

        if ($add_prompt_to_session === true) {
            self::$sessions[$this->session_id][] = [
                'role' => 'user',
                'content' => $prompt
            ];
        }

        if (__::x(@$files)) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $files__value) {
                if (!file_exists($files__value)) {
                    continue;
                }
                if ($add_prompt_to_session === true) {
                    if (
                        !is_array(
                            self::$sessions[$this->session_id][count(self::$sessions[$this->session_id]) - 1]['content']
                        )
                    ) {
                        self::$sessions[$this->session_id][count(self::$sessions[$this->session_id]) - 1]['content'] = [
                            [
                                'type' => 'input_text',
                                'text' =>
                                    self::$sessions[$this->session_id][count(self::$sessions[$this->session_id]) - 1][
                                        'content'
                                    ]
                            ]
                        ];
                    }
                    array_unshift(
                        self::$sessions[$this->session_id][count(self::$sessions[$this->session_id]) - 1]['content'],
                        substr($files__value, -4) === '.pdf'
                            ? [
                                'type' => 'input_file',
                                'filename' => basename($files__value),
                                'file_data' =>
                                    'data:' .
                                    mime_content_type($files__value) .
                                    ';base64,' .
                                    base64_encode(file_get_contents($files__value))
                            ]
                            : [
                                'type' => 'input_image',
                                'image_url' =>
                                    'data:' .
                                    mime_content_type($files__value) .
                                    ';base64,' .
                                    base64_encode(file_get_contents($files__value))
                            ]
                    );
                }
            }
        }

        $args = [
            'model' => $this->model,
            'temperature' => $this->temperature,
            'input' => self::$sessions[$this->session_id]
        ];

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
                if (isset($mcp__value['url']) && !isset($mcp__value['server_url'])) {
                    $mcp__value['server_url'] = $mcp__value['url'];
                    unset($mcp__value['url']);
                }
                if (!isset($mcp__value['server_label'])) {
                    $mcp__value['server_label'] = 'mcp-server-' . ($mcp__key + 1);
                }
                if (isset($mcp__value['server_url'])) {
                    $mcp__value['server_url'] = rtrim($mcp__value['server_url'], '/') . '/';
                }
                $args['tools'][] = $mcp__value;
            }
        }

        if ($this->stream === true) {
            $args['stream'] = true;
        }

        $this->log($args, 'ask');
        $response = __::curl(
            url: $this->url . '/responses',
            data: $args,
            method: 'POST',
            headers: [
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            stream_callback: $this->getStreamCallback()
        );
        if ($this->stream === true) {
            $response = $this->stream_response;
        }
        $this->log(@$response->result, 'response');
        $this->addCosts($response, $return);

        $output_text = '';
        if (
            __::x(@$response) &&
            __::x(@$response->result) &&
            __::x(@$response->result->id) &&
            __::x(@$response->result->output)
        ) {
            foreach ($response->result->output as $output__value) {
                if (__::x(@$output__value->type) && $output__value->type === 'message') {
                    if (__::x(@$output__value->content)) {
                        foreach ($output__value->content as $content__value) {
                            if (__::x(@$content__value->text)) {
                                if (__::x(@$output_text)) {
                                    $output_text .= PHP_EOL . '---' . PHP_EOL;
                                }
                                $output_text .= $content__value->text;
                            }
                        }
                    }
                }
            }
        }

        if (__::nx(@$output_text)) {
            $this->log($response, 'failed');
            return $return;
        }

        $return['response'] = $output_text;
        $return['success'] = true;
        $return['content'] = @$response->result->output;

        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->output)) {
            foreach ($response->result->output as $output__value) {
                if (__::x(@$output__value->content)) {
                    self::$sessions[$this->session_id][] = [
                        'role' => $output__value->role,
                        'content' => $output__value->content
                    ];
                }
            }
        }

        // parse json
        $return['response'] = $this->parseJson($return['response']);

        return $return;
    }
}

class ai_claude extends aihelper
{
    protected $provider = 'anthropic';

    protected $name = 'claude';

    protected $url = 'https://api.anthropic.com/v1';

    protected $models = [
        [
            'name' => 'claude-sonnet-4-5',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.000003, 'output' => 0.000015],
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'claude-haiku-4-5',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.0000008, 'input_cached' => 0.0000008, 'output' => 0.000004],
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'claude-sonnet-4-0',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.000003, 'output' => 0.000015],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-1',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.000015, 'output' => 0.000075],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-0',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000015, 'input_cached' => 0.000015, 'output' => 0.000075],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-3-7-sonnet-latest',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.000003, 'output' => 0.000015],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-3-5-haiku-latest',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.0000008, 'input_cached' => 0.0000008, 'output' => 0.000004],
            'default' => false,
            'test' => false
        ]
    ];

    protected function askThis($prompt = null, $files = null, $add_prompt_to_session = true)
    {
        $return = ['response' => null, 'success' => false, 'content' => [], 'costs' => 0.0];

        if (__::nx($this->model) || __::nx($this->api_key) || __::nx($this->session_id)) {
            $return['response'] = 'data missing.';
            return $return;
        }

        if (__::nx($prompt)) {
            $return['response'] = 'prompt missing.';
            return $return;
        }

        // trim prompt
        $prompt = __trim_whitespace(__trim_every_line($prompt));

        if ($add_prompt_to_session === true) {
            self::$sessions[$this->session_id][] = [
                'role' => 'user',
                'content' => $prompt
            ];
        }

        if (__::x(@$files)) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $files__value) {
                if (!file_exists($files__value)) {
                    continue;
                }
                if ($add_prompt_to_session === true) {
                    if (
                        !is_array(
                            self::$sessions[$this->session_id][count(self::$sessions[$this->session_id]) - 1]['content']
                        )
                    ) {
                        self::$sessions[$this->session_id][count(self::$sessions[$this->session_id]) - 1]['content'] = [
                            [
                                'type' => 'text',
                                'text' =>
                                    self::$sessions[$this->session_id][count(self::$sessions[$this->session_id]) - 1][
                                        'content'
                                    ]
                            ]
                        ];
                    }
                    array_unshift(
                        self::$sessions[$this->session_id][count(self::$sessions[$this->session_id]) - 1]['content'],
                        [
                            'type' => substr($files__value, -4) === '.pdf' ? 'document' : 'image',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => mime_content_type($files__value),
                                'data' => base64_encode(file_get_contents($files__value))
                            ]
                        ]
                    );
                }
            }
        }

        $args = [
            'model' => $this->model,
            'max_tokens' => $this->getMaxTokensForModel(),
            'messages' => self::$sessions[$this->session_id],
            'temperature' => $this->temperature
        ];

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

        $this->log($args, 'ask');
        $response = __::curl(
            url: $this->url . '/messages',
            data: $args,
            method: 'POST',
            headers: [
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01',
                'anthropic-beta' => 'mcp-client-2025-04-04'
            ],
            stream_callback: $this->getStreamCallback()
        );
        if ($this->stream === true) {
            $response = $this->stream_response;
        }
        $this->log(@$response->result, 'response');
        $this->addCosts($response, $return);

        $output_text = '';
        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->content)) {
            foreach ($response->result->content as $content__value) {
                if (__::x(@$content__value->text)) {
                    if (__::x(@$output_text)) {
                        $output_text .= PHP_EOL . '---' . PHP_EOL;
                    }
                    $output_text .= $content__value->text;
                }
            }
        }

        // handle stop reason
        if (
            __::x(@$response) &&
            __::x(@$response->result) &&
            __::x(@$response->result->stop_reason) &&
            $response->result->stop_reason === 'pause_turn'
        ) {
            $this->log('pause_turn detected');
            self::$sessions[$this->session_id][] = [
                'role' => 'assistant',
                'content' => $response->result->content
            ];
            return $this->askThis($prompt, $files, false);
        }

        if (__::nx(@$output_text)) {
            $this->log($response, 'failed');
            if (
                __::x(@$response) &&
                __::x(@$response->result) &&
                __::x(@$response->result->type) &&
                @$response->result->type === 'error' &&
                __::x(@$response->result->error) &&
                __::x(@$response->result->error->type) &&
                @$response->result->error->type === 'overloaded_error'
            ) {
                $this->log('overload detected. pausing...');
                sleep(5);
            }
            return $return;
        }

        $return['response'] = $output_text;
        $return['success'] = true;
        $return['content'] = @$response->result->content;
        self::$sessions[$this->session_id][] = [
            'role' => 'assistant',
            'content' => $response->result->content
        ];

        // parse json
        $return['response'] = $this->parseJson($return['response']);

        return $return;
    }
}

class ai_gemini extends aihelper
{
    protected $provider = 'google';

    protected $name = 'gemini';

    protected $url = 'https://generativelanguage.googleapis.com/v1beta';

    protected $models = [
        [
            'name' => 'gemini-2.5-pro',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.0000035, 'input_cached' => 0.0000035, 'output' => 0.00001],
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'gemini-2.5-flash',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.00000035, 'input_cached' => 0.00000035, 'output' => 0.00000053],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-2.5-flash-lite',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.0000001, 'output' => 0.0000002],
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'gemini-2.0-flash',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.00000035, 'input_cached' => 0.00000035, 'output' => 0.00000053],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-2.0-flash-lite',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.0000001, 'input_cached' => 0.0000001, 'output' => 0.0000002],
            'default' => false,
            'test' => false
        ]
    ];

    protected function askThis($prompt = null, $files = null, $add_prompt_to_session = true)
    {
        $return = ['response' => null, 'success' => false, 'content' => [], 'costs' => 0.0];

        if (__::nx($this->model) || __::nx($this->api_key) || __::nx($this->session_id)) {
            $return['response'] = 'data missing.';
            return $return;
        }

        if (__::nx($prompt)) {
            $return['response'] = 'prompt missing.';
            return $return;
        }

        // trim prompt
        $prompt = __trim_whitespace(__trim_every_line($prompt));

        if ($add_prompt_to_session === true) {
            self::$sessions[$this->session_id][] = [
                'role' => 'user',
                'parts' => [['text' => $prompt]]
            ];
        }

        if (__::x(@$files)) {
            if (!is_array($files)) {
                $files = [$files];
            }
            foreach ($files as $files__value) {
                if (!file_exists($files__value)) {
                    continue;
                }
                if ($add_prompt_to_session === true) {
                    self::$sessions[$this->session_id][count(self::$sessions[$this->session_id]) - 1]['parts'][] = [
                        'inline_data' => [
                            'mime_type' => mime_content_type($files__value),
                            'data' => base64_encode(file_get_contents($files__value))
                        ]
                    ];
                }
            }
        }

        $args = [
            'contents' => self::$sessions[$this->session_id],
            'generationConfig' => [
                'temperature' => $this->temperature
            ]
        ];
        $this->log($args, 'ask');
        $response = __::curl(
            $this->url . '/models/' . $this->model . ':generateContent?key=' . $this->api_key,
            $args,
            'POST',
            null
        );
        $this->log(@$response->result, 'response');
        $this->addCosts($response, $return);

        $output_text = '';
        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->candidates)) {
            foreach ($response->result->candidates as $candidates__value) {
                if (__::x(@$candidates__value->content) && __::x(@$candidates__value->content->parts)) {
                    foreach ($candidates__value->content->parts as $parts__value) {
                        if (__::x(@$parts__value->text)) {
                            if (__::x(@$output_text)) {
                                $output_text .= PHP_EOL . PHP_EOL . PHP_EOL . '---' . PHP_EOL . PHP_EOL . PHP_EOL;
                            }
                            $output_text .= $parts__value->text;
                        }
                    }
                }
            }
        }

        if (__::nx($output_text)) {
            $this->log($response, 'failed');
            return $return;
        }
        $return['response'] = $output_text;
        $return['success'] = true;
        $return['content'] = @$response->result->candidates;

        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->candidates)) {
            foreach ($response->result->candidates as $candidates__value) {
                if (__::x(@$candidates__value->content)) {
                    self::$sessions[$this->session_id][] = $candidates__value->content;
                }
            }
        }

        // parse json
        $return['response'] = $this->parseJson($return['response']);

        return $return;
    }
}

/* compatible with the anthropic api */
class ai_grok extends ai_claude
{
    protected $provider = 'xai';

    protected $name = 'grok';

    protected $url = 'https://api.x.ai/v1';

    protected $models = [
        [
            'name' => 'grok-4',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000005, 'input_cached' => 0.000005, 'output' => 0.000015],
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'grok-4-fast-reasoning',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000002, 'input_cached' => 0.000002, 'output' => 0.000006],
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'grok-4-fast-non-reasoning',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.0000015, 'input_cached' => 0.0000015, 'output' => 0.0000045],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-code-fast-1',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000001, 'input_cached' => 0.000001, 'output' => 0.000003],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-3',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000006, 'input_cached' => 0.000006, 'output' => 0.000018],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-3-mini',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000001, 'input_cached' => 0.000001, 'output' => 0.000003],
            'default' => false,
            'test' => false
        ]
    ];
}

/* compatible with the anthropic api */
class ai_deepseek extends ai_claude
{
    protected $provider = 'deepseek';

    protected $name = 'deepseek';

    protected $url = 'https://api.deepseek.com/anthropic';

    protected $models = [
        [
            'name' => 'deepseek-chat',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.00000027, 'input_cached' => 0.00000027, 'output' => 0.0000011],
            'default' => true,
            'test' => true
        ],
        [
            'name' => 'deepseek-reasoner',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.00000055, 'input_cached' => 0.00000055, 'output' => 0.00000219],
            'default' => false,
            'test' => false
        ]
    ];
}
