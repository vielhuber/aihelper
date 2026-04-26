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

    function isCi()
    {
        return ($_SERVER['CI'] ?? '') == 'true' ||
            ($_ENV['CI'] ?? '') == 'true' ||
            getenv('CI') == 'true' ||
            getenv('ACT_TOOLSDIRECTORY') != '';
    }

    function test__ai_all()
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

    function test__ai_anthropic(&$stats = [], $force = false)
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        $this->ai_test_prepare('anthropic', $_SERVER['ANTHROPIC_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_google(&$stats = [], $force = false)
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        $this->ai_test_prepare('google', $_SERVER['GOOGLE_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_openai(&$stats = [], $force = false)
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        $this->ai_test_prepare('openai', $_SERVER['OPENAI_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_xai(&$stats = [], $force = false)
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        $this->ai_test_prepare('xai', $_SERVER['XAI_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_deepseek(&$stats = [], $force = false)
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        $this->ai_test_prepare('deepseek', $_SERVER['DEEPSEEK_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_openrouter(&$stats = [], $force = false)
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        $this->ai_test_prepare('openrouter', $_SERVER['OPENROUTER_API_KEY'] ?? null, null, $stats);
    }

    function test__ai_llamacpp(&$stats = [], $force = false)
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        $this->ai_test_prepare('llamacpp', $_SERVER['LLM_API_KEY'] ?? null, $_SERVER['LLM_URL'] ?? null, $stats);
    }

    function test__ai_lmstudio(&$stats = [], $force = false)
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        $this->ai_test_prepare('lmstudio', $_SERVER['LLM_API_KEY'] ?? null, $_SERVER['LLM_URL'] ?? null, $stats);
    }

    function test__ai_test(&$stats = [], $force = false)
    {
        if ($this->isCi() && $force !== true) {
            $this->markTestSkipped('Skipped.');
        }
        $this->ai_test_prepare('test', null, null, $stats);
    }

    function test__auto_compact()
    {
        $session_id = 'auto-compact-test-' . mt_rand(100000, 999999);
        $cache_file =
            sys_get_temp_dir() . '/aihelper-cache/' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $session_id) . '.txt';
        @unlink($cache_file);

        // fabricate a session that exceeds ~70% of test provider's ctx (128k).
        // ~90k tokens ≈ ~360k chars. use a compact mixture of roles.
        $bloat = str_repeat('Dies ist ein langer Gesprächsverlauf mit vielen Details. ', 400);
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
        // tail: 4 recent messages that must stay verbatim
        $tail_marker_user = 'TAIL_USER_MARKER_' . mt_rand(1000, 9999);
        $tail_marker_asst = 'TAIL_ASSISTANT_MARKER_' . mt_rand(1000, 9999);
        $history[] = ['role' => 'user', 'content' => $tail_marker_user . ' frage 1'];
        $history[] = ['role' => 'assistant', 'content' => $tail_marker_asst . ' antwort 1'];
        $history[] = ['role' => 'user', 'content' => $tail_marker_user . ' frage 2'];
        $history[] = ['role' => 'assistant', 'content' => $tail_marker_asst . ' antwort 2'];

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
        // head (10) + summary (1) + tail (4) == 15
        $this->assertSame(15, count($session_after), 'head(10) + summary(1) + tail(4) = 15 expected');

        // head is preserved verbatim
        for ($i = 0; $i < 10; $i++) {
            $this->assertSame(
                '# SKILL ' . $i . "\n\nInstruktionen für den Assistenten.",
                $session_after[$i]['content']
            );
        }
        // tail is preserved verbatim (last 4 messages unchanged)
        $tail_after = array_slice($session_after, -4);
        $this->assertStringContainsString($tail_marker_user, $tail_after[0]['content']);
        $this->assertStringContainsString($tail_marker_asst, $tail_after[1]['content']);
        $this->assertStringContainsString($tail_marker_user, $tail_after[2]['content']);
        $this->assertStringContainsString($tail_marker_asst, $tail_after[3]['content']);

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

        @unlink($cache_file);
    }

    function ai_test_prepare($provider, $api_key = null, $url = null, &$stats = [])
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

    function ai_test($provider, $model, $api_key, $url)
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
            'lmstudio'
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
            'lmstudio'
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
            'lmstudio'
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
            'lmstudio'
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
            'lmstudio'
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
            'lmstudio'
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

        $supported = in_array($provider, ['anthropic', 'openai', 'openrouter', 'llamacpp', 'lmstudio', 'test']);
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

        $supported = in_array($provider, ['anthropic', 'openai', 'openrouter', 'llamacpp', 'lmstudio']);
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
                while (($_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') != '') {
                    $mcp_servers[] = [
                        'url' => $_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_URL'],
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
                    ($_SERVER['MCP_SERVER_TEST_PROMPT_' . str_pad($i_prompt, 2, '0', STR_PAD_LEFT)] ?? '') != '' &&
                    ($_SERVER['MCP_SERVER_TEST_ANSWER_' . str_pad($i_prompt, 2, '0', STR_PAD_LEFT)] ?? '') != ''
                ) {
                    $return = $ai_mcp->ask(
                        $_SERVER['MCP_SERVER_TEST_PROMPT_' . str_pad($i_prompt, 2, '0', STR_PAD_LEFT)]
                    );
                    $success_this =
                        $return['success'] &&
                        count($ai_mcp->getSessionContent()) === $i_prompt * 2 &&
                        stripos(
                            $return['response'],
                            $_SERVER['MCP_SERVER_TEST_ANSWER_' . str_pad($i_prompt, 2, '0', STR_PAD_LEFT)]
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

        $this->assertTrue($fail_count <= 3);
        return [$costs, $success_count, $fail_count];
    }

    function test__ai_wrong_api_key()
    {
        $providers = aihelper::getProviders();
        foreach ([false, true] as $streams__value) {
            foreach ($providers as $providers__value) {
                if (in_array($providers__value['name'], ['test', 'llamacpp', 'lmstudio'], true)) {
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

    function test__ai_mcp_meta_tools()
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
        while (($_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') != '') {
            $status = aihelper::getMcpOnlineStatus(
                $_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_URL'],
                $return->result->access_token
            );
            $this->assertTrue(is_bool($status));
            $this->assertTrue($status);

            $meta = aihelper::getMcpMetaInfo(
                $_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_URL'],
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

            if (($_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_TOOL'] ?? '') != '') {
                $tool_response = aihelper::callMcpTool(
                    $_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_TOOL'],
                    null,
                    $_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_URL'],
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

    function test__ai_mcp_response_times()
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
            while (($_SERVER['MCP_SERVER_TEST_' . str_pad($i_cur, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') != '') {
                $mcp_servers_all[] = $_SERVER['MCP_SERVER_TEST_' . str_pad($i_cur, 2, '0', STR_PAD_LEFT) . '_URL'];
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

    function test__ai_mcp_response_format()
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

        $mcp_servers = [];
        $i_cur = 1;
        while (($_SERVER['MCP_SERVER_TEST_' . str_pad($i_cur, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') != '') {
            $url = $_SERVER['MCP_SERVER_TEST_' . str_pad($i_cur, 2, '0', STR_PAD_LEFT) . '_URL'];
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

    function test__ai_mcp_long_running_task()
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
        while (($_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') != '') {
            $mcp_servers[] = [
                'url' => $_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_URL'],
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

    function test__ai_mcp_servers_call_type()
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
        while (($_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_URL'] ?? '') != '') {
            $url = $_SERVER['MCP_SERVER_TEST_' . str_pad($i_url, 2, '0', STR_PAD_LEFT) . '_URL'];
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
        while (($_SERVER['MCP_SERVER_TEST_PROMPT_' . str_pad($i_prompt, 2, '0', STR_PAD_LEFT)] ?? '') != '') {
            $prompts[] = [
                'prompt' => $_SERVER['MCP_SERVER_TEST_PROMPT_' . str_pad($i_prompt, 2, '0', STR_PAD_LEFT)],
                'answer' => $_SERVER['MCP_SERVER_TEST_ANSWER_' . str_pad($i_prompt, 2, '0', STR_PAD_LEFT)] ?? ''
            ];
            $i_prompt++;
        }

        $providers = [
            [
                'provider' => 'anthropic',
                'model' => 'claude-haiku-4-5',
                'api_key' => $_SERVER['ANTHROPIC_API_KEY'] ?? '',
                'url' => null,
                'call_types' => ['local']
            ],
            [
                'provider' => 'openai',
                'model' => 'gpt-4.1-mini',
                'api_key' => $_SERVER['OPENAI_API_KEY'] ?? '',
                'url' => null,
                'call_types' => ['local']
            ],
            [
                'provider' => 'google',
                'model' => 'gemini-2.5-flash',
                'api_key' => $_SERVER['GOOGLE_API_KEY'] ?? '',
                'url' => null,
                'call_types' => ['local']
            ],
            [
                'provider' => 'llamacpp',
                'model' => 'qwen3.5-27b-ud',
                'api_key' => $_SERVER['LLM_API_KEY'] ?? '',
                'url' => $_SERVER['LLM_URL'] ?? null,
                'call_types' => ['local']
            ],
            [
                'provider' => 'lmstudio',
                'model' => 'qwen3.5-27b-ud',
                'api_key' => $_SERVER['LLM_API_KEY'] ?? '',
                'url' => $_SERVER['LLM_URL'] ?? null,
                'call_types' => ['local']
            ]
        ];

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

    function test__ai_missing_or_wrong_models()
    {
        $providers = aihelper::getProviders();
        $success = true;
        foreach ($providers as $providers__value) {
            if (in_array($providers__value['name'], ['openrouter', 'llamacpp', 'lmstudio', 'test'])) {
                continue;
            }
            $modelsApi = array_map(function ($m) {
                return $m['name'];
            }, aihelper::create(
                provider: $providers__value['name'],
                api_key: $_SERVER[mb_strtoupper($providers__value['name']) . '_API_KEY'] ?? null,
                url: $_SERVER[mb_strtoupper($providers__value['name']) . '_API_URL'] ?? null,
                log: 'tests/aihelper.log'
            )->fetchModels());
            $modelsStatic = array_map(function ($models__value) {
                return $models__value['name'];
            }, $providers__value['models']);
            foreach ($modelsApi as $models__value) {
                if (!in_array($models__value, $modelsStatic)) {
                    $this->log(
                        '⛔ Model ' .
                            $models__value .
                            ' is available via API but not listed in static array for provider ' .
                            $providers__value['name']
                    );
                    $success = false;
                }
            }
            foreach ($modelsStatic as $models__value) {
                if (!in_array($models__value, $modelsApi)) {
                    $this->log(
                        '⛔ Model ' .
                            $models__value .
                            ' is listed in static array but not available via API for provider ' .
                            $providers__value['name']
                    );
                    $success = false;
                }
            }
            foreach ($providers__value['models'] as $models__value) {
                for ($i = 1; $i <= 3; $i++) {
                    $ai = aihelper::create(
                        provider: $providers__value['name'],
                        model: $models__value['name'],
                        temperature: 1.0,
                        api_key: $_SERVER[mb_strtoupper($providers__value['name']) . '_API_KEY'] ?? null,
                        url: $_SERVER[mb_strtoupper($providers__value['name']) . '_API_URL'] ?? null,
                        log: 'tests/aihelper.log',
                        max_tries: 2
                    );
                    $return = $ai->ask('Hallo!');
                    if ($return['success'] === true) {
                        $this->log('✅ ' . $models__value['name']);
                        break;
                    } else {
                        $temp =
                            stripos($return['response'] ?? '', 'try again later') !== false ||
                            stripos($return['response'] ?? '', 'exhausted') !== false ||
                            stripos($return['response'] ?? '', 'overloaded') !== false;
                        $this->log(
                            ($temp === true ? '⚠️' : '⛔') .
                                ' Model ' .
                                $models__value['name'] .
                                ' of provider ' .
                                $providers__value['name'] .
                                ' is not responding to API calls (' .
                                json_encode($return) .
                                ').'
                        );
                        if ($temp === true) {
                            break;
                        }
                        if ($i === 3 && $temp === false) {
                            $success = false;
                        }
                    }
                }
            }
        }
        $this->assertTrue($success);
    }
}
