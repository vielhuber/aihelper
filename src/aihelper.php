<?php
namespace vielhuber\aihelper;

use vielhuber\stringhelper\__;

abstract class aihelper
{
    public $provider = null;
    public $name = null;
    public $url = null;
    public $models = [];

    public $model = null;
    public $temperature = null;
    public $api_key = null;
    public $log = null;
    public $max_tries = null;
    public $mcp_servers = null;

    public $session_id = null;
    public static $sessions = [];

    public $conversation_id = null;
    public $cleanup_data = [];

    public static function create(
        $service,
        $model = null,
        $temperature = null,
        $api_key = null,
        $session_id = null,
        $log = null,
        $max_tries = null,
        $mcp_servers = null
    ) {
        if ($service === 'chatgpt') {
            return new ai_chatgpt($model, $temperature, $api_key, $session_id, $log, $max_tries, $mcp_servers);
        }
        if ($service === 'claude') {
            return new ai_claude($model, $temperature, $api_key, $session_id, $log, $max_tries, $mcp_servers);
        }
        if ($service === 'gemini') {
            return new ai_gemini($model, $temperature, $api_key, $session_id, $log, $max_tries, $mcp_servers);
        }
        if ($service === 'grok') {
            return new ai_grok($model, $temperature, $api_key, $session_id, $log, $max_tries, $mcp_servers);
        }
        if ($service === 'deepseek') {
            return new ai_deepseek($model, $temperature, $api_key, $session_id, $log, $max_tries, $mcp_servers);
        }
        return null;
    }

    abstract public function __construct(
        $model,
        $temperature,
        $api_key,
        $session_id,
        $log = null,
        $max_tries = null,
        $mcp_servers = null
    );

    public function ask($prompt = null, $files = null)
    {
        $return = ['response' => null, 'success' => false, 'content' => [], 'costs' => 0.0];
        $max_tries = $this->max_tries;
        while ($return['success'] === false && $max_tries > 0) {
            //$this->log($this, 'ask');
            //$this->log($prompt, 'ask');
            //$this->log($prompt, 'ask');
            if ($max_tries < $this->max_tries) {
                $this->log('⚠️ tries left: ' . $max_tries);
            }
            $return = $this->askThis($prompt, $files, $max_tries === $this->max_tries);
            $max_tries--;
        }
        return $return;
    }

    abstract public function askThis($prompt = null, $files = null, $add_prompt_to_session = true);

    public function parseJson($msg)
    {
        if (strpos(trim($msg), '```json') === 0 || __::string_is_json($msg)) {
            $msg = json_decode(trim(rtrim(ltrim(ltrim(trim($msg), '```json'), '```'), '```')));
        }
        return $msg;
    }

    abstract public function cleanup();

    abstract public function cleanup_all();

    public function enable_log($filename)
    {
        $this->log = $filename;
    }

    public function disable_log()
    {
        $this->log = null;
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
                date('Y-m-d H:i:s', strtotime('now')) .
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

    public function addCosts($response, &$return)
    {
        $this->log($response);

        $input_tokens = 0;
        if (
            __::x($response) &&
            __::x($response->result) &&
            __::x($response->result->usage) &&
            __::x($response->result->usage->input_tokens)
        ) {
            $input_tokens += $response->result->usage->input_tokens;
        }
        if (
            __::x($response) &&
            __::x($response->result) &&
            __::x($response->result->usageMetadata) &&
            __::x($response->result->usageMetadata->promptTokenCount)
        ) {
            $input_tokens += $response->result->usageMetadata->promptTokenCount;
        }

        $input_cached_tokens = 0;
        if (
            __::x($response) &&
            __::x($response->result) &&
            __::x($response->result->usage) &&
            __::x($response->result->usage->input_tokens_details) &&
            __::x($response->result->usage->input_tokens_details->cached_tokens)
        ) {
            $input_cached_tokens += $response->result->usage->input_tokens_details->cached_tokens;
        }
        if (
            __::x($response) &&
            __::x($response->result) &&
            __::x($response->result->usage) &&
            __::x($response->result->usage->cache_creation_input_tokens)
        ) {
            $input_cached_tokens += $response->result->usage->cache_creation_input_tokens;
        }
        if (
            __::x($response) &&
            __::x($response->result) &&
            __::x($response->result->usage) &&
            __::x($response->result->usage->cache_read_input_tokens)
        ) {
            $input_cached_tokens += $response->result->usage->cache_read_input_tokens;
        }

        $output_tokens = 0;
        if (
            __::x($response) &&
            __::x($response->result) &&
            __::x($response->result->usage) &&
            __::x($response->result->usage->output_tokens)
        ) {
            $output_tokens += $response->result->usage->output_tokens;
        }
        if (
            __::x($response) &&
            __::x($response->result) &&
            __::x($response->result->usageMetadata) &&
            __::x($response->result->usageMetadata->candidatesTokenCount)
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
}

class ai_chatgpt extends aihelper
{
    public $provider = 'openai';

    public $name = 'chatgpt';

    public $url = 'https://api.openai.com/v1';

    public $models = [
        [
            'name' => 'gpt-5',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'gpt-5-mini',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'gpt-5-nano',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4.1',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gpt-4o-mini',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ]
    ];

    public function __construct(
        $model,
        $temperature,
        $api_key,
        $session_id,
        $log = null,
        $max_tries = null,
        $mcp_servers = null
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

        $this->model = $model;
        $this->temperature = $temperature;
        $this->api_key = $api_key;

        if ($this->api_key !== null) {
            if (__::nx($session_id)) {
                $max_tries = $this->max_tries;
                while ($max_tries > 0) {
                    $response = __::curl($this->url . '/conversations', [], 'POST', [
                        'Authorization' => 'Bearer ' . $this->api_key
                    ]);
                    if (__::nx(@$response->result) || __::nx(@$response->result->id)) {
                        $max_tries--;
                        if ($max_tries === 0) {
                            __::exception(__::v(@$response->result->error->message, 'error creating conversation'));
                        }
                        continue;
                    } else {
                        break;
                    }
                }
                $this->log(@$response->result, 'create conversation');
                $this->conversation_id = $response->result->id;
                $this->session_id = 'conversation_id=' . $this->conversation_id;
            } else {
                $this->conversation_id = explode('=', $session_id)[1];
                $this->session_id = $session_id;
            }
        }
    }

    public function askThis($prompt = null, $files = null, $add_prompt_to_session = true)
    {
        $return = ['response' => null, 'success' => false, 'content' => [], 'costs' => 0.0];

        if (
            __::nx($this->model) ||
            __::nx($this->api_key) ||
            __::nx($this->session_id) ||
            __::nx($this->conversation_id)
        ) {
            $return['response'] = 'data missing.';
            return $return;
        }

        if (__::nx($prompt)) {
            $return['response'] = 'prompt missing.';
            return $return;
        }

        // trim prompt
        $prompt = __trim_whitespace(__trim_every_line($prompt));

        $args = [
            'model' => $this->model,
            'temperature' => $this->temperature,
            'input' => [],
            'conversation' => $this->conversation_id
        ];
        $args['input'][] = [
            'role' => 'user',
            'content' => $prompt
        ];

        if (__::x($files)) {
            if (!is_array($files)) {
                $files = [$files];
            }

            $file_ids = [];

            foreach ($files as $files__value) {
                if (!file_exists($files__value)) {
                    continue;
                }
                // uppercase pdf filenames don't get recognized properly
                if (strpos($files__value, '.PDF') !== false) {
                    rename($files__value, str_replace('.PDF', '.pdf', $files__value));
                    $files__value = str_replace('.PDF', '.pdf', $files__value);
                }
                // convert to proper path
                $files__value = realpath($files__value);
                // sometimes filenames with spaces fail on windows
                $files__value_new =
                    sys_get_temp_dir() . '/' . md5(uniqid()) . '.' . __::last(explode('.', $files__value));
                copy($files__value, $files__value_new);

                $files__value = $files__value_new;
                $response = __::curl(
                    $this->url . '/files',
                    [
                        'file' => new \CURLFile($files__value),
                        'purpose' =>
                            stripos($files__value, '.jpg') !== false ||
                            stripos($files__value, '.jpeg') !== false ||
                            stripos($files__value, '.png') !== false
                                ? 'vision'
                                : 'assistants'
                    ],
                    'POST',
                    [
                        'Authorization' => 'Bearer ' . $this->api_key
                    ],
                    false,
                    false // send as json
                );
                $this->addCosts($response, $return);
                if (__::nx(@$response->result) || __::nx(@$response->result->id)) {
                    return $return;
                }
                $this->log(@$response->result, 'create file');
                $file_ids[] = ['id' => $response->result->id, 'path' => $files__value];
                $this->cleanup_data[] = ['type' => 'file', 'id' => $response->result->id];
            }

            $args['input'][0]['content'] = [];
            $args['input'][0]['content'][] = ['type' => 'input_text', 'text' => $prompt];

            foreach ($file_ids as $file_ids__value) {
                if (
                    stripos($file_ids__value['path'], '.jpg') !== false ||
                    stripos($file_ids__value['path'], '.jpeg') !== false ||
                    stripos($file_ids__value['path'], '.png') !== false
                ) {
                    $args['input'][0]['content'][] = [
                        'type' => 'input_image',
                        'file_id' => $file_ids__value['id']
                    ];
                } else {
                    $args['input'][0]['content'][] = [
                        'type' => 'input_file',
                        'file_id' => $file_ids__value['id']
                    ];
                }
            }
        }

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

        $this->log($args, 'ask');
        $response = __::curl($this->url . '/responses', $args, 'POST', [
            'Authorization' => 'Bearer ' . $this->api_key
        ]);
        $this->addCosts($response, $return);
        $this->log(@$response->result->output, 'response');

        $output_text = '';
        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->output)) {
            $this->cleanup_data[] = ['type' => 'response', 'id' => $response->result->id];
            foreach ($response->result->output as $output__value) {
                if (__::x(@$output__value->type) && $output__value->type === 'message') {
                    if (__::x(@$output__value->content)) {
                        foreach ($output__value->content as $content__value) {
                            if (__::x(@$content__value->text)) {
                                if (__::x($output_text)) {
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

        // parse json
        $return['response'] = $this->parseJson($return['response']);

        return $return;
    }

    public function cleanup()
    {
        foreach ($this->cleanup_data as $cleanup_data__value) {
            if ($cleanup_data__value['type'] === 'file') {
                $response = __::curl($this->url . '/files/' . $cleanup_data__value['id'], null, 'DELETE', [
                    'Authorization' => 'Bearer ' . $this->api_key
                ]);
            }
            if ($cleanup_data__value['type'] === 'response') {
                $response = __::curl($this->url . '/responses/' . $cleanup_data__value['id'], null, 'DELETE', [
                    'Authorization' => 'Bearer ' . $this->api_key
                ]);
            }
        }
        $response = __::curl($this->url . '/conversations/' . $this->conversation_id, null, 'DELETE', [
            'Authorization' => 'Bearer ' . $this->api_key
        ]);
    }

    public function cleanup_all()
    {
        while (1 === 1) {
            $response = __::curl($this->url . '/files', ['limit' => 10000], 'GET', [
                'Authorization' => 'Bearer ' . $this->api_key
            ]);
            if (__::x($response) && __::x($response->result) && __::x($response->result->data)) {
                foreach ($response->result->data as $res__value) {
                    if (__::x(@$res__value->id)) {
                        $response2 = __::curl($this->url . '/files/' . $res__value->id, null, 'DELETE', [
                            'Authorization' => 'Bearer ' . $this->api_key
                        ]);
                    }
                }
                $this->log('deleted ' . count($response->result->data) . ' files');
            } else {
                break;
            }
        }
    }
}

class ai_claude extends aihelper
{
    public $provider = 'anthropic';

    public $name = 'claude';

    public $url = 'https://api.anthropic.com/v1';

    public $models = [
        [
            'name' => 'claude-sonnet-4-5',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'claude-haiku-4-5',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'claude-sonnet-4-0',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-1',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-opus-4-0',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-3-7-sonnet-latest',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-3-5-haiku-latest',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ]
    ];

    public function __construct(
        $model,
        $temperature,
        $api_key,
        $session_id,
        $log = null,
        $max_tries = null,
        $mcp_servers = null
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

        $this->model = $model;
        $this->temperature = $temperature;
        $this->api_key = $api_key;
        if (__::nx($session_id)) {
            $this->session_id = md5(uniqid());
        } else {
            $this->session_id = $session_id;
        }
        if (!array_key_exists($session_id, self::$sessions)) {
            self::$sessions[$session_id] = [];
        }
    }

    public function askThis($prompt = null, $files = null, $add_prompt_to_session = true)
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

        if (__::x($files)) {
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
            'max_tokens' => 1024,
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

        $this->log($args, 'ask');
        $response = __::curl($this->url . '/messages', $args, 'POST', [
            'x-api-key' => $this->api_key,
            'anthropic-version' => '2023-06-01',
            'anthropic-beta' => 'mcp-client-2025-04-04'
        ]);
        $this->addCosts($response, $return);
        $this->log(@$response->result, 'response');

        $output_text = '';
        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->content)) {
            foreach ($response->result->content as $content__value) {
                if (__::x(@$content__value->text)) {
                    if (__::x($output_text)) {
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

    public function cleanup()
    {
        self::$sessions = [];
    }

    public function cleanup_all()
    {
        $this->cleanup();
    }
}

class ai_gemini extends aihelper
{
    public $provider = 'google';

    public $name = 'gemini';

    public $url = 'https://generativelanguage.googleapis.com/v1beta';

    public $models = [
        [
            'name' => 'gemini-2.5-pro',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'gemini-2.5-flash',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-2.5-flash-lite',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'gemini-2.0-flash',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'gemini-2.0-flash-lite',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ]
    ];

    public function __construct(
        $model,
        $temperature,
        $api_key,
        $session_id,
        $log = null,
        $max_tries = null,
        $mcp_servers = null
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

        $this->model = $model;
        $this->temperature = $temperature;
        $this->api_key = $api_key;
        if (__::nx($session_id)) {
            $this->session_id = md5(uniqid());
        } else {
            $this->session_id = $session_id;
        }
        if (!array_key_exists($session_id, self::$sessions)) {
            self::$sessions[$session_id] = [];
        }
    }

    public function askThis($prompt = null, $files = null, $add_prompt_to_session = true)
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

        if (__::x($files)) {
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
        $this->addCosts($response, $return);
        $this->log(@$response->result->candidates, 'response');

        $output_text = '';
        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->candidates)) {
            foreach ($response->result->candidates as $candidates__value) {
                if (__::x(@$candidates__value->content) && __::x(@$candidates__value->content->parts)) {
                    foreach ($candidates__value->content->parts as $parts__value) {
                        if (__::x(@$parts__value->text)) {
                            if (__::x($output_text)) {
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

    public function cleanup()
    {
        self::$sessions = [];
    }

    public function cleanup_all()
    {
        $this->cleanup();
    }
}

/* compatible with the anthropic api */
class ai_grok extends ai_claude
{
    public $provider = 'xai';

    public $name = 'grok';

    public $url = 'https://api.x.ai/v1';

    public $models = [
        [
            'name' => 'grok-4',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => true,
            'test' => false
        ],
        [
            'name' => 'grok-4-fast-reasoning',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => true
        ],
        [
            'name' => 'grok-4-fast-non-reasoning',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-code-fast-1',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-3',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'grok-3-mini',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ]
    ];
}

/* compatible with the anthropic api */
class ai_deepseek extends ai_claude
{
    public $provider = 'deepseek';

    public $name = 'deepseek';

    public $url = 'https://api.deepseek.com/anthropic';

    public $models = [
        [
            'name' => 'deepseek-chat',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => true,
            'test' => true
        ],
        [
            'name' => 'deepseek-reasoner',
            'costs' => ['input' => 0.0005, 'input_cached' => 0.0005, 'output' => 0.0005],
            'default' => false,
            'test' => false
        ]
    ];
}
