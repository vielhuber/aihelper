<?php
use vielhuber\aihelper\aihelper;
use vielhuber\stringhelper\__;

class Test extends \PHPUnit\Framework\TestCase
{
    protected $run_count = 6;

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
        $this->ai_test_prepare('gemini', @$_SERVER['GOOGLE_GEMINI_API_KEY'], $stats);
    }

    function test__ai_chatgpt(&$stats = [])
    {
        $this->ai_test_prepare('chatgpt', @$_SERVER['OPENAI_API_KEY'], $stats);
    }

    function test__ai_grok(&$stats = [])
    {
        $this->ai_test_prepare('grok', @$_SERVER['GROK_API_KEY'], $stats);
    }

    function test__ai_deepseek(&$stats = [])
    {
        $this->ai_test_prepare('deepseek', @$_SERVER['DEEPSEEK_API_KEY'], $stats);
    }

    function ai_test_prepare($service, $api_key, &$stats = [])
    {
        $models = aihelper::create(service: $service)->getTestModels();
        foreach ($models as $models__value) {
            __::log_begin('ai');
            [$costs, $success_count, $fail_count] = $this->ai_test($service, $models__value, $api_key);
            $time = __::log_end('ai', false)['time'];
            if (!isset($stats[$service])) {
                $stats[$service] = [];
            }
            if (!isset($stats[$service][$models__value])) {
                $stats[$service][$models__value] = [];
            }
            $stats[$service][$models__value][] = [
                'time' => $time,
                'costs' => $costs,
                'fail_count' => $fail_count,
                'success_count' => $success_count
            ];
        }
    }

    function ai_test($service, $model, $api_key)
    {
        $this->log('Testing ' . $service . ' (' . $model . ')...');

        $ai = aihelper::create(
            service: $service,
            model: $model,
            temperature: 1.0,
            api_key: $api_key,
            session_id: null,
            log: 'tests/ai.log'
        );

        $costs = 0;
        $fail_count = 0;
        $success_count = 0;

        $supported = true;
        if ($supported === true) {
            $return = $ai->ask('Wer wurde 2018 Fußball-Weltmeister? Antworte bitte kurz.');
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($return['content']) > 0 &&
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

        $supported = true;
        if ($supported === true) {
            $return = $ai->ask('Was habe ich vorher gefragt?');
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($return['content']) > 0 &&
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

        $supported = true;
        if ($supported === true) {
            $return = $ai->ask('Welchen Satz hast Du exakt zuvor geschrieben?');
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($return['content']) > 0 &&
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

        $supported = true;
        if ($supported === true) {
            $return = $ai->ask('Ich heiße David mit Vornamen. Bitte merk Dir das!');
            //$this->log($return);
            $ai = aihelper::create(
                service: $service,
                model: $model,
                temperature: 1.0,
                api_key: $api_key,
                session_id: $ai->getSessionId(),
                log: 'tests/ai.log'
            );
            $return = $ai->ask('Wie heiße ich mit Vornamen?');
            //$this->log($return);
            $success_this =
                $return['success'] && count($return['content']) > 0 && stripos($return['response'], 'David') !== false;
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

        $supported = true;
        if ($supported === true) {
            $ai = aihelper::create(
                service: $service,
                model: $model,
                temperature: 1.0,
                api_key: $api_key,
                history: $ai->getSessionContent(),
                log: 'tests/ai.log'
            );
            $return = $ai->ask('Wie heiße ich mit Vornamen?');
            //$this->log($return);
            $success_this =
                $return['success'] && count($return['content']) > 0 && stripos($return['response'], 'David') !== false;
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

        $supported = in_array($service, ['claude', 'gemini', 'chatgpt', 'grok']);
        if ($supported === true) {
            $return = $ai->ask('Was ist auf dem Bild zu sehen?', 'tests/assets/iptc_write.jpg');

            $success_this =
                $return['success'] &&
                count($return['content']) > 0 &&
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

        $supported = in_array($service, ['claude', 'gemini', 'chatgpt', 'grok']);
        if ($supported === true) {
            $return = $ai->ask('Welches Bild habe ich im Gesprächsverlauf hochgeladen?');

            $success_this =
                $return['success'] &&
                count($return['content']) > 0 &&
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

        $supported = in_array($service, ['claude', 'gemini', 'chatgpt']);
        if ($supported === true) {
            $return = $ai->ask(
                'Wie lautet die Kundennummer (Key: customer_nr)? Wann wurde der Brief verfasst (Key: date)? Von wem wurde der Brief verfasst (Key: author)? Bitte antworte nur im JSON-Format. Wenn Du unsicher bist, gib den wahrscheinlichsten Wert zurück. Wenn Du einen Wert gar nicht findest, gib einen leeren String zurück.',
                'tests/assets/lorem.pdf'
            );
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($return['content']) > 0 &&
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

        $supported = in_array($service, ['claude', 'gemini', 'chatgpt']);
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
                count($return['content']) > 0 &&
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

        $supported = in_array($service, ['claude']);
        if ($supported === true) {
            $ai_stream = aihelper::create(
                service: $service,
                model: $model,
                temperature: 1.0,
                api_key: $api_key,
                session_id: null,
                log: 'tests/ai.log',
                max_tries: null,
                mcp_servers: null,
                stream: true
            );
            $return = $ai_stream->ask('Wer wurde 2018 Fußball-Weltmeister? Antworte bitte kurz.');
            //$this->log($return);
            $success_this =
                $return['success'] &&
                count($return['content']) > 0 &&
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
            $supported = in_array($service, ['claude', 'chatgpt']);
            if ($supported === true) {
                $return = __::curl(
                    @$_SERVER['MCP_SERVER_TEST_AUTH_TOKEN_URL'],
                    [
                        'client_id' => @$_SERVER['MCP_SERVER_TEST_AUTH_TOKEN_CLIENT_ID'],
                        'client_secret' => @$_SERVER['MCP_SERVER_TEST_AUTH_TOKEN_CLIENT_SECRET'],
                        'audience' => @$_SERVER['MCP_SERVER_TEST_AUTH_TOKEN_AUDIENCE'],
                        'grant_type' => 'client_credentials'
                    ],
                    'POST'
                );
                //$this->log('access token: ' . $return->result->access_token);
                $mcp_servers = [];
                foreach (explode(',', @$_SERVER['MCP_SERVER_TEST_URLS']) as $urls__value) {
                    $mcp_servers[] = [
                        'url' => $urls__value,
                        'authorization_token' => $return->result->access_token
                    ];
                }
                $ai_mcp = aihelper::create(
                    service: $service,
                    model: $model,
                    temperature: 1.0,
                    api_key: $api_key,
                    session_id: null,
                    log: 'tests/ai.log',
                    max_tries: 1,
                    mcp_servers: $mcp_servers
                );

                $return = $ai_mcp->ask('
                Was ist das Gegenteil von "hell"?
                Antworte bitte kurz.
            ');
                $success_this =
                    $return['success'] &&
                    count($return['content']) > 0 &&
                    stripos($return['response'], 'dunkel') !== false;
                if ($success_this) {
                    $success_count++;
                } else {
                    $fail_count++;
                }
                $costs += $return['costs'];
                $this->log(($success_this ? '✅' : '⛔') . ' #11 (mcp1)');
                if ($success_this === false) {
                    $this->log($return);
                }

                $return = $ai_mcp->ask('
                Welche Dateien befinden sich auf meinem PC im Ordner:
                /var/www/aihelper?
                Nutze Tools!
            ');
                $success_this =
                    $return['success'] &&
                    count($return['content']) > 0 &&
                    stripos($return['response'], '.env.example') !== false;
                if ($success_this) {
                    $success_count++;
                } else {
                    $fail_count++;
                }
                $costs += $return['costs'];
                $this->log(($success_this ? '✅' : '⛔') . ' #12 (mcp2)');
                if ($success_this === false) {
                    $this->log($return);
                }

                $return = $ai_mcp->ask('
                Gibt es mehr als 1000 Kunden in der Datenbank?
                Schau in die Datenbanktabelle "customer".
                Nutze Tools!
            ');
                $success_this =
                    $return['success'] && count($return['content']) > 0 && stripos($return['response'], 'ja') !== false;
                if ($success_this) {
                    $success_count++;
                } else {
                    $fail_count++;
                }
                $costs += $return['costs'];
                $this->log(($success_this ? '✅' : '⛔') . ' #13 (mcp3)');
                if ($success_this === false) {
                    $this->log($return);
                }
            }
        }

        $this->assertTrue(true);
        return [$costs, $success_count, $fail_count];
    }
}
