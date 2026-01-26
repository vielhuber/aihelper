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

    protected $session_id = null;
    protected static $sessions = [];

    public static function create(
        $provider,
        $model = null,
        $temperature = null,
        $timeout = null,
        $api_key = null,
        $log = null,
        $max_tries = null,
        $mcp_servers = null,
        $session_id = null,
        $history = null,
        $stream = null
    ) {
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
                stream: $stream
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
                stream: $stream
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
                stream: $stream
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
                stream: $stream
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
                stream: $stream
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
                stream: $stream
            );
        }
        return null;
    }

    public static function getProviders()
    {
        $data = [];
        foreach (
            [new ai_claude(), new ai_gemini(), new ai_chatgpt(), new ai_grok(), new ai_deepseek(), new ai_test()]
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

    public static function getMcpOnlineStatus($url = null, $authorization_token = null)
    {
        try {
            // add trailing slash to avoid 307 redirect
            if (substr($url, -1) !== '/') {
                $url .= '/';
            }

            // use mcp ping endpoint
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
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

    public static function getMcpMetaInfo($url = null, $authorization_token = null)
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
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

    public static function callMcpTool($name = null, $args = [], $url = null, $authorization_token = null)
    {
        try {
            // add trailing slash to avoid 307 redirect
            if (substr($url, -1) !== '/') {
                $url .= '/';
            }

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
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

    protected function getDefaultModel()
    {
        foreach ($this->models as $models__value) {
            if ($models__value['default'] === true) {
                return $models__value['name'];
            }
        }
        return null;
    }

    public function __construct(
        $model = null,
        $temperature = null,
        $timeout = null,
        $api_key = null,
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
        if ($timeout === null) {
            $timeout = 300;
        }
        if ($log !== null) {
            $this->log = $log;
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
                prev_output_text: null
            );
            $this->log($return, 'return');
            $max_tries--;
        }
        return $return;
    }

    abstract protected function askThis(
        $prompt = null,
        $files = null,
        $add_prompt_to_session = true,
        $prev_output_text = null
    );

    abstract protected function makeApiCall($args = null);

    protected function trimPrompt($prompt)
    {
        return __::trim_whitespace(__::trim_every_line($prompt));
    }

    abstract protected function bringPromptInFormat($prompt, $files = null);

    abstract protected function addResponseToSession($response);

    protected function truncateMcpToolResultContent($content, $max_length = 500)
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
                    if (
                        isset($content_item__value['type']) &&
                        $content_item__value['type'] === 'text' &&
                        isset($content_item__value['text']) &&
                        is_string($content_item__value['text']) &&
                        mb_strlen($content_item__value['text']) > $max_length
                    ) {
                        $original_length = mb_strlen($content_item__value['text']);
                        $truncated = mb_substr($content_item__value['text'], 0, $max_length);
                        $truncated .= "\n\n[... content truncated: $original_length chars reduced to $max_length chars ...]";
                        $content[$i]->content[$content_item__key]['text'] = $truncated;
                    }
                }
            }
        }

        return $content;
    }

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

    public function prependPromptToSession($prompt, $files = null)
    {
        $prompt = $this->trimPrompt($prompt);
        array_unshift(self::$sessions[$this->session_id], $this->bringPromptInFormat($prompt, $files));
    }

    public function appendPromptToSession($prompt, $files = null)
    {
        $prompt = $this->trimPrompt($prompt);
        self::$sessions[$this->session_id][] = $this->bringPromptInFormat($prompt, $files);
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
        //$this->log($response, 'add costs');
        $this->log('response with length ' . strlen(json_encode($response)), 'add costs');

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
        $this->stream_current_block_type = null;

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
                        $this->stream_response->result->error = (object) ['message' => @$parsed['error']['message']];
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
                                            @ob_flush();
                                        }
                                        @flush();
                                    }
                                }
                                // add the full content block from the API
                                if (isset($parsed['content_block'])) {
                                    $this->stream_response->result->content[] = (object) $parsed['content_block'];
                                }
                                $this->stream_current_block_type = @$parsed['content_block']['type'];
                            }

                            // stream delta content
                            if (isset($parsed['type']) && $parsed['type'] === 'content_block_delta') {
                                $index = $parsed['index'] ?? 0;
                                if (isset($this->stream_response->result->content[$index])) {
                                    $block = &$this->stream_response->result->content[$index];

                                    // handle text delta
                                    if (isset($parsed['delta']['text'])) {
                                        $text = $parsed['delta']['text'];
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
                                            @ob_flush();
                                        }
                                        @flush();
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
                                            @ob_flush();
                                        }
                                        @flush();
                                    }
                                }
                            }

                            if (isset($parsed['usage'])) {
                                $this->stream_response->result->usage->input_tokens += @$parsed['usage'][
                                    'input_tokens'
                                ];
                                $this->stream_response->result->usage->cache_creation_input_tokens += @$parsed['usage'][
                                    'cache_creation_input_tokens'
                                ];
                                $this->stream_response->result->usage->cache_read_input_tokens += @$parsed['usage'][
                                    'cache_read_input_tokens'
                                ];
                                $this->stream_response->result->usage->output_tokens += @$parsed['usage'][
                                    'output_tokens'
                                ];
                            }

                            if (isset($parsed['type']) && $parsed['type'] === 'message_stop') {
                                // only send [DONE] if not pause_turn (because stream continues)
                                if ($this->stream_response->result->stop_reason !== 'pause_turn') {
                                    // finally sleep to ensure all chunks arrive
                                    sleep(2);
                                    echo "data: [DONE]\n\n";
                                    if (ob_get_level() > 0) {
                                        @ob_flush();
                                    }
                                    @flush();
                                }
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

        if ($this->name === 'chatgpt') {
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
                        $this->stream_response->result->error = (object) ['message' => @$parsed['error']['message']];
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
                                $this->stream_response->result->usage->cache_creation_input_tokens += @$parsed[
                                    'response'
                                ]['usage']['input_tokens_details']['cached_tokens'];
                                $this->stream_response->result->usage->cache_read_input_tokens += 0;
                                $this->stream_response->result->usage->output_tokens += @$parsed['response']['usage'][
                                    'output_tokens'
                                ];
                            }

                            if (isset($parsed['type']) && $parsed['type'] === 'response.completed') {
                                $this->stream_response->result->id = @$parsed['response']['id'];
                                // finally sleep to ensure all chunks arrive
                                sleep(2);
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
    public $provider = 'OpenAI';

    public $title = 'ChatGPT';

    public $name = 'chatgpt';

    protected $url = 'https://api.openai.com/v1';

    public $support_mcp = true;

    public $support_stream = true;

    public $models = [
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

    protected function bringPromptInFormat($prompt, $files = null)
    {
        $content = [];

        // add text content
        $content[] = [
            'type' => 'input_text',
            'text' => $prompt
        ];

        // add files
        if (__::x(@$files)) {
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

    protected function addResponseToSession($response)
    {
        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->output)) {
            foreach ($response->result->output as $output__value) {
                if (
                    __::x(@$output__value->type) &&
                    $output__value->type === 'message' &&
                    __::x(@$output__value->content)
                ) {
                    $content = $output__value->content;

                    $content = $this->truncateMcpToolResultContent($content);

                    self::$sessions[$this->session_id][] = [
                        'role' => 'assistant',
                        'content' => $content
                    ];
                }
            }
        }
    }

    protected function askThis($prompt = null, $files = null, $add_prompt_to_session = true, $prev_output_text = null)
    {
        $return = ['response' => null, 'success' => false, 'costs' => 0.0];

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

        $this->log((int) round(strlen(json_encode($args)) / 3.5), 'ask with input token length');
        $this->log($args, 'ask');
        $response = $this->makeApiCall($args);
        if ($this->stream === true) {
            $response = $this->stream_response;
        }
        $this->log(@$response->result, 'response');
        $this->addCosts($response, $return);

        $output_text = $prev_output_text !== null ? $prev_output_text : '';
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
                                    $output_text .= PHP_EOL . PHP_EOL;
                                }
                                $output_text .= __::trim_whitespace($content__value->text);
                            }
                        }
                    }
                }
            }
        }

        if (__::nx(@$output_text)) {
            $this->log($response, 'failed');
            if (
                __::x(@$response) &&
                __::x(@$response->result) &&
                __::x(@$response->result->error) &&
                __::x(@$response->result->error->message) &&
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

    protected function makeApiCall($args = null)
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
            'name' => 'claude-3-7-sonnet',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.000003, 'input_cached' => 0.000003, 'output' => 0.000015],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-3-5-haiku',
            'max_tokens' => 8192,
            'costs' => ['input' => 0.0000008, 'input_cached' => 0.0000008, 'output' => 0.000004],
            'default' => false,
            'test' => false
        ],
        [
            'name' => 'claude-3-haiku-20240307',
            'max_tokens' => 4096,
            'costs' => ['input' => 0.00000025, 'input_cached' => 0.00000025, 'output' => 0.00000125],
            'default' => false,
            'test' => false
        ]
    ];

    protected function bringPromptInFormat($prompt, $files = null)
    {
        $content = [];

        // add text content
        $content[] = [
            'type' => 'text',
            'text' => $prompt
        ];

        // add files
        if (__::x(@$files)) {
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

    protected function addResponseToSession($response)
    {
        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->content)) {
            $content = $response->result->content;

            // fix mcp_tool_use blocks with empty array inputs (should be empty objects)
            if (is_array($content)) {
                for ($i = 0; $i < count($content); $i++) {
                    if (
                        isset($content[$i]->type) &&
                        $content[$i]->type === 'mcp_tool_use' &&
                        isset($content[$i]->input) &&
                        is_array($content[$i]->input) &&
                        count($content[$i]->input) === 0
                    ) {
                        $content[$i]->input = new \stdClass();
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

    protected function askThis($prompt = null, $files = null, $add_prompt_to_session = true, $prev_output_text = null)
    {
        $return = ['response' => null, 'success' => false, 'costs' => 0.0];

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

        $this->log((int) round(strlen(json_encode($args)) / 3.5), 'ask with input token length');
        $this->log($args, 'ask');
        $response = $this->makeApiCall($args);
        if ($this->stream === true) {
            $response = $this->stream_response;
        }
        $this->log(@$response->result, 'response');
        $this->addCosts($response, $return);

        $output_text = $prev_output_text !== null ? $prev_output_text : '';
        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->content)) {
            foreach ($response->result->content as $content__value) {
                if (__::x(@$content__value->text)) {
                    if (__::x(@$output_text)) {
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
            __::x(@$response) &&
            __::x(@$response->result) &&
            ((__::x(@$response->result->stop_reason) && $response->result->stop_reason === 'pause_turn') ||
                (__::nx(@$response->result->stop_reason) && __::x(@$response->result->content)))
        ) {
            $this->log('pause_turn / empty stop_reason detected');

            // throttle
            /*
            if (__::x(@$response->result->usage) && __::x(@$response->result->usage->input_tokens)) {
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
                prev_output_text: $output_text
            );
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
            if (
                __::x(@$response) &&
                __::x(@$response->result) &&
                __::x(@$response->result->error) &&
                __::x(@$response->result->error->message) &&
                is_string($response->result->error->message)
            ) {
                $return['response'] = $response->result->error->message;
            }
            if (
                __::x(@$response) &&
                __::x(@$response->result) &&
                __::x(@$response->result->error) &&
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

    protected function makeApiCall($args = null)
    {
        return __::curl(
            url: $this->url . '/messages',
            data: $args,
            method: 'POST',
            headers: [
                'x-api-key' => $this->api_key,
                'anthropic-version' => '2023-06-01',
                'anthropic-beta' => 'mcp-client-2025-04-04'
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

    protected function bringPromptInFormat($prompt, $files = null)
    {
        $parts = [];

        // add text content
        $parts[] = [
            'text' => $prompt
        ];

        // add files
        if (__::x(@$files)) {
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

    protected function addResponseToSession($response)
    {
        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->candidates)) {
            foreach ($response->result->candidates as $candidates__value) {
                if (__::x(@$candidates__value->content) && __::x(@$candidates__value->content->parts)) {
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

    protected function askThis($prompt = null, $files = null, $add_prompt_to_session = true, $prev_output_text = null)
    {
        $return = ['response' => null, 'success' => false, 'costs' => 0.0];

        if (__::nx($this->model) || __::nx($this->session_id) || __::nx($prompt)) {
            $return['response'] = 'data missing.';
            return $return;
        }

        $prompt = $this->trimPrompt($prompt);

        if ($add_prompt_to_session === true) {
            $this->appendPromptToSession($prompt, $files);
        }

        $args = [
            'contents' => self::$sessions[$this->session_id],
            'generationConfig' => [
                'temperature' => $this->temperature
            ]
        ];
        $this->log((int) round(strlen(json_encode($args)) / 3.5), 'ask with input token length');
        $this->log($args, 'ask');
        $response = $this->makeApiCall($args);
        $this->log(@$response->result, 'response');
        $this->addCosts($response, $return);

        $output_text = $prev_output_text !== null ? $prev_output_text : '';
        if (__::x(@$response) && __::x(@$response->result) && __::x(@$response->result->candidates)) {
            foreach ($response->result->candidates as $candidates__value) {
                if (__::x(@$candidates__value->content) && __::x(@$candidates__value->content->parts)) {
                    foreach ($candidates__value->content->parts as $parts__value) {
                        if (__::x(@$parts__value->text)) {
                            if (__::x(@$output_text)) {
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
                __::x(@$response) &&
                __::x(@$response->result) &&
                __::x(@$response->result->error) &&
                __::x(@$response->result->error->message) &&
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

    protected function makeApiCall($args = null)
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
            'default' => true,
            'test' => true
        ]
    ];

    protected function makeApiCall($args = null)
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
