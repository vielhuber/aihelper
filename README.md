[![build status](https://github.com/vielhuber/aihelper/actions/workflows/ci.yml/badge.svg)](https://github.com/vielhuber/aihelper/actions)

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
    provider: 'claude', // claude|gemini|chatgpt|grok|deepseek
    model: 'claude-opus-4-1', // claude-opus-4-1|gemini-2.5-pro|gpt-5|grok-4|deepseek-chat|...
    temperature: 1.0, // controls the randomness of the text generated
    api_key: '**API Key**',
    log: 'output.log',
    max_tries: 3,
    mcp_servers: [
        [
            'name' => 'example-mcp',
            'url' => 'https://modelcontextprotocol.io/mcp',
            'authorization_token' => '...'
        ]
    ],
    session_id: null, // submit session to continue a conversation (get with $ai->getSessionId())
    history: null, // submit messages (get with $ai->getSessionContent()),
    stream: false
);

$ai->ask(prompt: 'Wer wurde 2018 Fußball-Weltmeister?');
// ['response' => 'Frankreich.', 'success' => true, 'content' => [...], 'costs' => 0.001]

$ai->ask(prompt: 'Was ist auf dem Bild zu sehen?', files: 'lorem.jpg');
// ['response' => 'Auf dem Bild ist eine Katze zu sehen.', 'success' => true, 'content' => [...], 'costs' => 0.001]

$ai->ask(prompt: 'Wie lautet das erste Wort in der PDF?', files: 'lorem.pdf');
// ['response' => 'Das erste Wort lautet "Lorem".', 'success' => true, 'content' => [...], 'costs' => 0.001]

$ai->ask(prompt: 'Fasse die folgenden Dokumente zusammen.', files: ['1.pdf', '2.jpg']);
// ['response' => '...', 'success' => true, 'content' => [...], 'costs' => 0.001]

$ai->ask(prompt: 'Was habe ich vorher gefragt?');
// ['response' => 'Du hast gefragt: "Wie lautet das erste Wort in der PDF?"', 'success' => true, 'content' => [...], 'costs' => 0.001]

aihelper::getProviders() // gets overview of providers and models
$ai->getSessionId() // get current session id
$ai->getSessionContent() // gets messages in chat history
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
// $result = ['response' => 'Frankreich.', 'success' => true, 'content' => [...], 'costs' => 0.001]
```

if streaming stutters on apache2 with php‑fpm, be sure that gzip is disabled for the streaming route and also adjust your virtualhost so fastcgi forwards packets immediately (no buffering):

**before**

```conf
<VirtualHost ...>
  ...
  <FilesMatch \.php$>
    SetHandler "proxy:unix:/var/run/php/php8.3-fpm.sock|fcgi://localhost/"
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
      SetHandler "proxy:unix:/var/run/php/php8.3-fpm.sock|fcgi://localhost-stream/"
      SetEnv no-gzip 1
      RequestHeader unset Accept-Encoding
    </If>
    <Else>
      SetHandler "proxy:unix:/var/run/php/php8.3-fpm.sock|fcgi://localhost/"
    </Else>
  </FilesMatch>
  ...
</VirtualHost>
```
