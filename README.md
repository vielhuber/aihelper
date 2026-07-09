[![build status](https://github.com/vielhuber/aihelper/actions/workflows/ci.yml/badge.svg)](https://github.com/vielhuber/aihelper/actions)
[![GitHub Tag](https://img.shields.io/github/v/tag/vielhuber/aihelper)](https://github.com/vielhuber/aihelper/tags)
[![Code Style](https://img.shields.io/badge/code_style-psr--12-ff69b4.svg)](https://www.php-fig.org/psr/psr-12/)
[![License](https://img.shields.io/github/license/vielhuber/aihelper)](https://github.com/vielhuber/aihelper/blob/main/LICENSE.md)
[![Last Commit](https://img.shields.io/github/last-commit/vielhuber/aihelper)](https://github.com/vielhuber/aihelper/commits)
[![PHP Version Support](https://img.shields.io/packagist/php-v/vielhuber/aihelper)](https://packagist.org/packages/vielhuber/aihelper)
[![Packagist Downloads](https://img.shields.io/packagist/dt/vielhuber/aihelper)](https://packagist.org/packages/vielhuber/aihelper)

# 🤖 aihelper 🤖

aihelper provides a single, consistent php interface for multiple ai providers. it supports chat and vision use cases, session-aware conversations, robust retry logic, logging, simple cost tracking, and optional model context protocol (mcp) integration — all behind one method.

## installation

```
composer require vielhuber/aihelper
```

## usage

```php
use vielhuber\aihelper\aihelper;

$ai = aihelper::create(
    provider: 'anthropic', // anthropic|google|openai|xai|deepseek|openrouter|cliproxyapi|elevenlabs|nvidia|llamacpp|lmstudio
    model: 'claude-opus-4-1', // claude-opus-4-1|gemini-2.5-pro|gpt-5|grok-4|deepseek-chat|qwen/qwen3-coder-next|...
    effort: null, // null|none|minimal|low|medium|high|xhigh|max — reasoning effort, ignored when the provider/model has no supported reasoning control
    temperature: 1.0, // controls the randomness of the text generated
    api_key: '**API Key**',
    log: 'output.log',
    max_tries: 1,
    timeout: 300, // maximum timeout (increase for long tasks)
    mcp_servers: [
        [
            'name' => 'example-mcp',
            'url' => 'https://modelcontextprotocol.io/mcp',
            'authorization_token' => '...',
            'allowed_tools' => ['tool_name_1', 'tool_name_2'] // optional: restrict to specific tools (null or omit = all tools allowed)
        ]
    ],
    mcp_servers_call_type: 'remote', // remote = provider calls mcp servers directly, local = client-side tool loop via aihelper
    session_id: null, // submit session to continue a conversation (get with $ai->getSessionId())
    history: null, // submit messages (get with $ai->getSessionContent()),
    stream: false,
    url: null, // overwrite connection url (e.g. for llamacpp/lmstudio)
    enable_thinking: null, // true|false|null — force reasoning/thinking on/off; null = provider default (see below)
    auto_compact: false // true = transparently compact the session when it approaches the model's context window
);

$ai->ask(prompt: 'Wer wurde 2018 Fußball-Weltmeister?');
// ['response' => 'Frankreich.', 'success' => true, 'costs' => 0.001]

$ai->ask(prompt: 'Was ist auf dem Bild zu sehen?', files: 'lorem.jpg');
// ['response' => 'Auf dem Bild ist eine Katze zu sehen.', 'success' => true, 'costs' => 0.001]

$ai->ask(prompt: 'Was wird in der Audiodatei gesagt?', files: 'lorem.mp3');
// ['response' => 'In der Aufnahme wird gesagt: "Hallo, wie geht es dir?"', 'success' => true, 'costs' => 0.001]

$ai->ask(prompt: 'Wie lautet das erste Wort in der PDF?', files: 'lorem.pdf');
// ['response' => 'Das erste Wort lautet "Lorem".', 'success' => true, 'costs' => 0.001]

$ai->ask(prompt: 'Fasse die folgenden Dokumente zusammen.', files: ['1.pdf', '2.jpg']);
// ['response' => '...', 'success' => true, 'costs' => 0.001]

$ai->ask(prompt: 'Was habe ich vorher gefragt?');
// ['response' => 'Du hast gefragt: "Wie lautet das erste Wort in der PDF?"', 'success' => true, 'costs' => 0.001]

$ai = aihelper::create(provider: 'openai', model: 'gpt-image-1', api_key: '**API Key**');

$ai->image(
    prompt: 'a red cat on a blue couch',  // text description of the desired image
    size: '1024x1024',                    // e.g. '512x512'|'1024x1024'|'1792x1024'
    n: 1,                                 // number of images to generate
    input_file: null,                     // path|url|base64 — switches to edit/variation mode
    output_file: null                     // path — when set, file is written and the path is returned instead base64
);
// ['response' => 'iVBORw0KGgo...', 'success' => true, 'costs' => 0.04]

$ai->image(prompt: 'a red cat on a blue couch', output_file: '/tmp/cat.png');
// ['response' => '/tmp/cat.png', 'success' => true, 'costs' => 0.04]

$ai->image(prompt: 'a red cat on a blue couch', n: 3, output_file: '/tmp/cat.png');
// ['response' => ['/tmp/cat-1.png', '/tmp/cat-2.png', '/tmp/cat-3.png'], 'success' => true, 'costs' => 0.12]

$ai->image(prompt: 'add a hat', input_file: 'cat.png'); // edit / variation

$ai = aihelper::create(provider: 'openai', model: 'gpt-4o-mini-tts', api_key: '**API Key**');

$ai->audio(
    prompt: 'Hallo, wie geht es dir?', // text to synthesize
    voice: 'alloy',                    // provider voice id (e.g. 'alloy'|'echo'|'nova')
    speed: null,                       // optional, e.g 1.2
    output_file: null                  // path — when set, file is written and the path is returned instead base64
);
// ['response' => 'SUQzBAA...', 'success' => true, 'costs' => 0.001]

$ai->audio(prompt: 'Hallo, wie geht es dir?', output_file: '/tmp/hi.mp3');
// ['response' => '/tmp/hi.mp3', 'success' => true, 'costs' => 0.001]

aihelper::getProviders() // gets overview of providers and models with costs and additional infos

aihelper::create(provider: '...', api_key: '...')->fetchModels() // get resolved model catalog

$ai->ping() // gets health, returns true|false

$ai->getSessionId() // get current session id

$ai->getSessionContent() // gets messages in chat history

$ai->getCliUsageLimits() // get cli usage limits for claude code, codex and antigravity
// [
//     ['type' => '5-hour', 'percent used' => 20, 'resets_at' => '2026-06-29T17:59:00+02:00'],
//     ['type' => 'weekly', 'percent used' => 10, 'resets_at' => '2026-07-06T03:03:00+02:00']
// ]

aihelper::getCliApiRequests( // get all requests from a local clis
    limit: 100, // null: all
    date_from: '2026-07-01 00:00:00', // null: all
    date_until: '2026-07-31 23:59:59', // null: all
    include_body: false,
    group_by: false // true = collapse per project
);

aihelper::purgeCliApiRequestLogs( // delete request logs only
    date_from: null, // null: all
    date_until: null // null: all
);

// manually populate history
$ai = aihelper::create(...);
$ai->prependPromptToSession(prompt: '...', files: [...]);
$ai->appendPromptToSession(prompt: '...', files: [...]);

aihelper::getMcpOnlineStatus(
    url: 'https://modelcontextprotocol.io/mcp',
    authorization_token: '...'
);
// true|false

aihelper::getMcpMetaInfo(
    url: 'https://modelcontextprotocol.io/mcp',
    authorization_token: '...'
);
// ['name' => '...', 'online' => true, 'instructions' => '...', 'tools' => ['...']]

aihelper::callMcpTool(
    name: 'foo-123',
    args: ['foo' => 'bar'],
    url: 'https://modelcontextprotocol.io/mcp',
    authorization_token: '...'
);
// ['jsonrpc' => '2.0', 'id' => 123, 'result' => ['content' => [['type' => 'text', 'text' => '...']]]]
```

### streaming

aihelper can stream model output to a browser using server‑sent events (see). in this mode the php backend connects to the model provider with http streaming and forwards chunks to the client as sse events in real time. see an example implementation at [/tests/stream/index.html](tests/stream/index.html).

```php
$ai = aihelper::create(
    /* ... */
    stream: true
    /* ... */
);

$result = $ai->ask('Wer wurde 2018 Fußball-Weltmeister?');
/* ... */
// echoes stream
/* ... */
// $result = ['response' => 'Frankreich.', 'success' => true, 'costs' => 0.001]
```

if streaming stutters on apache2 with php‑fpm, be sure that gzip is disabled for the streaming route and also adjust your virtualhost so fastcgi forwards packets immediately (no buffering):

**before**

```conf
<VirtualHost ...>
  ...
  <FilesMatch \.php$>
    SetHandler "proxy:unix:/var/run/php/php8.5-fpm.sock|fcgi://localhost/"
  </FilesMatch>
  ...
</VirtualHost>
```

**after**

```conf
<VirtualHost ...>
  ...
  <Proxy "fcgi://localhost-stream/" enablereuse=on flushpackets=on>
  </Proxy>
  <FilesMatch \.php$>
    <If "%{HTTP:Accept} -strmatch '*text/event-stream*'">
      SetHandler "proxy:unix:/var/run/php/php8.5-fpm.sock|fcgi://localhost-stream/"
      SetEnv no-gzip 1
      RequestHeader unset Accept-Encoding
    </If>
    <Else>
      SetHandler "proxy:unix:/var/run/php/php8.5-fpm.sock|fcgi://localhost/"
    </Else>
  </FilesMatch>
  ...
</VirtualHost>
```
