<?php
use vielhuber\aihelper\aihelper;
use vielhuber\stringhelper\__;

class Test extends \PHPUnit\Framework\TestCase
{
    protected $run_count = 3;

    public static function setUpBeforeClass(): void
    {
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
        }
    }

    function log($msg)
    {
        if (!is_string($msg)) {
            $msg = serialize($msg);
        }
        fwrite(STDERR, print_r($msg . PHP_EOL, true));
    }

    function test__ai_all()
    {
        $stats = [];
        file_put_contents('tests/ai.log', '');
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_claude($stats);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_gemini($stats);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_chatgpt($stats);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_grok($stats);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_deepseek($stats);
        }
        for ($i = 1; $i <= $this->run_count; $i++) {
            $this->log('run ' . $i . '/' . $this->run_count . '...');
            $this->test__ai_test($stats);
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

    function test__ai_claude(&$stats = [])
    {
        $this->ai_test_prepare('claude', @$_SERVER['CLAUDE_API_KEY'], $stats);
    }

    function test__ai_gemini(&$stats = [])
    {
        $this->ai_test_prepare('gemini', @$_SERVER['GEMINI_API_KEY'], $stats);
    }

    function test__ai_chatgpt(&$stats = [])
    {
        $this->ai_test_prepare('chatgpt', @$_SERVER['CHATGPT_API_KEY'], $stats);
    }

    function test__ai_grok(&$stats = [])
    {
        $this->ai_test_prepare('grok', @$_SERVER['GROK_API_KEY'], $stats);
    }

    function test__ai_deepseek(&$stats = [])
    {
        $this->ai_test_prepare('deepseek', @$_SERVER['DEEPSEEK_API_KEY'], $stats);
    }

    function test__ai_test(&$stats = [])
    {
        $this->ai_test_prepare('test', null, $stats);
    }

    function ai_test_prepare($provider, $api_key, &$stats = [])
    {
        $models = aihelper::create(provider: $provider)->getTestModels();
        foreach ($models as $models__value) {
            __::log_begin('ai');
            [$costs, $success_count, $fail_count] = $this->ai_test($provider, $models__value, $api_key);
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

    function ai_test($provider, $model, $api_key)
    {
        $this->log('Testing ' . $provider . ' (' . $model . ')...');

        $ai = aihelper::create(
            provider: $provider,
            model: $model,
            temperature: 1.0,
            api_key: $api_key,
            session_id: null,
            log: 'tests/ai.log'
        );

        $costs = 0;
        $fail_count = 0;
        $success_count = 0;

        $supported = in_array($provider, ['claude', 'gemini', 'chatgpt', 'grok', 'deepseek']);
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
                $this->log($return);
            }
        }

        $supported = in_array($provider, ['claude', 'gemini', 'chatgpt', 'grok', 'deepseek']);
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
                $this->log($return);
            }
        }

        $supported = in_array($provider, ['claude', 'gemini', 'chatgpt', 'grok', 'deepseek']);
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
                $this->log($return);
            }
        }

        $supported = in_array($provider, ['claude', 'gemini', 'chatgpt', 'grok', 'deepseek']);
        if ($supported === true) {
            $return = $ai->ask('Ich heiße David mit Vornamen. Bitte merk Dir das!');
            //$this->log($return);
            $ai = aihelper::create(
                provider: $provider,
                model: $model,
                temperature: 1.0,
                api_key: $api_key,
                session_id: $ai->getSessionId(),
                log: 'tests/ai.log'
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
                $this->log($return);
            }
        }

        $supported = in_array($provider, ['claude', 'gemini', 'chatgpt', 'grok', 'deepseek']);
        if ($supported === true) {
            $ai = aihelper::create(
                provider: $provider,
                model: $model,
                temperature: 1.0,
                api_key: $api_key,
                history: $ai->getSessionContent(),
                log: 'tests/ai.log'
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
                $this->log($return);
            }
        }

        $supported = in_array($provider, ['claude', 'gemini', 'chatgpt', 'grok']);
        if ($supported === true) {
            $return = $ai->ask('Was ist auf dem Bild zu sehen?', 'tests/assets/iptc_write.jpg');

            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 15 &&
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
                $this->log($return);
            }
        }

        $supported = in_array($provider, ['claude', 'gemini', 'chatgpt', 'grok']);
        if ($supported === true) {
            $return = $ai->ask('Welches Bild habe ich im Gesprächsverlauf hochgeladen?');

            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 17 &&
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
                $this->log($return);
            }
        }

        $supported = in_array($provider, ['claude', 'gemini', 'chatgpt']);
        if ($supported === true) {
            $return = $ai->ask(
                'Wie lautet die Kundennummer (Key: customer_nr)? Wann wurde der Brief verfasst (Key: date)? Von wem wurde der Brief verfasst (Key: author)? Bitte antworte nur im JSON-Format. Wenn Du unsicher bist, gib den wahrscheinlichsten Wert zurück. Wenn Du einen Wert gar nicht findest, gib einen leeren String zurück.',
                'tests/assets/lorem.pdf'
            );
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($ai->getSessionContent()) === 20 &&
                in_array($return['response']->customer_nr ?? '', ['F123465789']) &&
                in_array($return['response']->date ?? '', ['31. Oktober 2018', 'Oktober 2018', '2018-10-31']) &&
                in_array($return['response']->author ?? '', ['David Vielhuber']);
            if ($success_this) {
                $success_count++;
            } else {
                $fail_count++;
            }
            $costs += $return['costs'];
            $this->log(($success_this ? '✅' : '⛔') . ' #8 (pdf)');
            if ($success_this === false) {
                $this->log($return);
            }
        }

        $supported = in_array($provider, ['claude', 'gemini', 'chatgpt']);
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
                count($ai->getSessionContent()) === 25 &&
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
                $this->log($return);
            }
        }

        $supported = in_array($provider, ['claude', 'chatgpt', 'test']);
        if ($supported === true) {
            $ai_stream = aihelper::create(
                provider: $provider,
                model: $model,
                temperature: 1.0,
                api_key: $api_key,
                session_id: null,
                log: 'tests/ai.log',
                max_tries: 1,
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
                $this->log($return);
            }
        }

        $supported = in_array($provider, ['claude', 'chatgpt']);
        if ($supported === true) {
            $ai_stream = aihelper::create(
                provider: $provider,
                model: $model,
                temperature: 1.0,
                api_key: $api_key,
                session_id: null,
                log: 'tests/ai.log',
                max_tries: 1,
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
                $this->log($return);
            }
        }

        if (@$_SERVER['MCP_SERVER_TEST'] == '1') {
            $supported = in_array($provider, ['claude', 'chatgpt']);
            if ($supported === true) {
                $return = __::curl(
                    @$_SERVER['MCP_SERVER_TEST_AUTH_URL'],
                    [
                        'client_id' => @$_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_ID'],
                        'client_secret' => @$_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_SECRET'],
                        'audience' => @$_SERVER['MCP_SERVER_TEST_AUTH_AUDIENCE'],
                        'grant_type' => 'client_credentials'
                    ],
                    'POST'
                );
                //$this->log('token: ' . $return->result->access_token);
                $i_url = 1;
                $i_prompt = 1;
                $mcp_servers = [];
                while (@$_SERVER['MCP_SERVER_TEST_URL_' . $i_url] != '') {
                    $mcp_servers[] = [
                        'url' => $_SERVER['MCP_SERVER_TEST_URL_' . $i_url],
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
                    log: 'tests/ai.log',
                    max_tries: 1,
                    mcp_servers: $mcp_servers
                );
                while (
                    @$_SERVER['MCP_SERVER_TEST_PROMPT_' . $i_prompt] != '' &&
                    @$_SERVER['MCP_SERVER_TEST_ANSWER_' . $i_prompt] != ''
                ) {
                    $return = $ai_mcp->ask($_SERVER['MCP_SERVER_TEST_PROMPT_' . $i_prompt]);
                    $success_this =
                        $return['success'] &&
                        count($ai_mcp->getSessionContent()) === $i_prompt * 2 &&
                        stripos($return['response'], $_SERVER['MCP_SERVER_TEST_ANSWER_' . $i_prompt]) !== false;
                    if ($success_this) {
                        $success_count++;
                    } else {
                        $fail_count++;
                    }
                    $costs += $return['costs'];
                    $this->log(($success_this ? '✅' : '⛔') . ' #11 (mcp nr ' . $i_prompt . ')');
                    if ($success_this === false) {
                        $this->log($return);
                    }
                    $i_prompt++;
                }
            }
        }

        $this->assertTrue(true);
        return [$costs, $success_count, $fail_count];
    }

    function test__ai_wrong_api_key()
    {
        $providers = aihelper::getProviders();
        foreach ([false, true] as $streams__value) {
            foreach ($providers as $providers__value) {
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
                            log: 'tests/ai.log',
                            stream: $streams__value
                        );
                        $return = $ai->ask('Test!');
                        $this->assertSame($return['success'], false);
                        $this->assertMatchesRegularExpression('/api/i', $return['response'] ?? '');
                    }
                }
            }
        }
    }

    function test__ai_get_mcp_meta_info()
    {
        if (@$_SERVER['MCP_SERVER_TEST'] == '1') {
            $return = __::curl(
                @$_SERVER['MCP_SERVER_TEST_AUTH_URL'],
                [
                    'client_id' => @$_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_ID'],
                    'client_secret' => @$_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_SECRET'],
                    'audience' => @$_SERVER['MCP_SERVER_TEST_AUTH_AUDIENCE'],
                    'grant_type' => 'client_credentials'
                ],
                'POST'
            );
            //$this->log('token: ' . $return->result->access_token);
            $i_url = 1;
            while (@$_SERVER['MCP_SERVER_TEST_URL_' . $i_url] != '') {
                $status = aihelper::getMcpOnlineStatus(
                    $_SERVER['MCP_SERVER_TEST_URL_' . $i_url],
                    $return->result->access_token
                );
                $this->assertTrue(is_bool($status));
                $this->assertTrue($status);

                $meta = aihelper::getMcpMetaInfo(
                    $_SERVER['MCP_SERVER_TEST_URL_' . $i_url],
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
        }
    }
}
