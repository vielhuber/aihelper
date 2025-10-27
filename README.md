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
    service: 'chatgpt', // chatgpt|gemini|claude|grok|deepseek
    model: 'gpt-5', // gpt-5|gemini-2.5-pro|claude-opus-4-1|grok-4|deepseek-chat|...
    temperature: 1.0, // controls the randomness of the text generated
    api_key: '**API Key**',
    session_id: null, // submit session to continue a conversation (see $ai->getSessionId())
    log: 'output.log',
    max_tries = 3,
    mcp_servers: [
        [
            'name' => 'example-mcp',
            'url' => 'https://modelcontextprotocol.io/mcp',
            'authorization_token' => '...'
        ]
    ]
);

$ai->ask('Wer wurde 2018 Fußball-Weltmeister?');
// ['response' => 'Frankreich.', 'success' => true, 'content' => [...], 'costs' => 0.001]

$ai->ask('Was ist auf dem Bild zu sehen?', 'lorem.jpg');
// ['response' => 'Auf dem Bild ist eine Katze zu sehen.', 'success' => true, 'content' => [...], 'costs' => 0.001]

$ai->ask('Wie lautet das erste Wort in der PDF?', 'lorem.pdf');
// ['response' => 'Das erste Wort lautet "Lorem".', 'success' => true, 'content' => [...], 'costs' => 0.001]

$ai->ask('Fasse die folgenden Dokumente zusammen.', ['1.pdf', '2.jpg']);
// ['response' => '...', 'success' => true, 'content' => [...], 'costs' => 0.001]

$ai->ask('Was habe ich vorher gefragt?');
// ['response' => 'Du hast gefragt: "Wie lautet das erste Wort in der PDF?"', 'success' => true, 'content' => [...], 'costs' => 0.001]

$ai->cleanup(); // (remotely) deletes the data of the current session

$ai->cleanup_all(); // (remotely) deletes all data
```

### streaming

```php
$ai = aihelper::create(
    /* ... */
    stream: true
    /* ... */
);

$ai->ask('Wer wurde 2018 Fußball-Weltmeister?');
/* ... */
// echo stream
/* ... */
// ['response' => 'Frankreich.', 'success' => true, 'content' => [...], 'costs' => 0.001]
```

aihelper can stream model output to a browser using server‑sent events (see). in this mode the php backend connects to the model provider with http streaming and forwards chunks to the client as sse events in real time. see an example implementation at [/tests/stream/index.html](tests/stream/index.html).

if streaming stutters on apache2 with php‑fpm, be sure that gzip is disabled for the streaming route and also adjust your virtualhost so fastcgi forwards packets immediately (no buffering):

```conf
<Proxy "fcgi://localhost-stream/" enablereuse=on flushpackets=on>
</Proxy>
<LocationMatch "stream\.php$">
  SetHandler "proxy:unix:/var/run/php/phpX.X-fpm.sock|fcgi://localhost-stream/"
</LocationMatch>
```
