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
    provider: 'claude', // claude|gemini|chatgpt|grok|deepseek|lmstudio
    model: 'claude-opus-4-1', // claude-opus-4-1|gemini-2.5-pro|gpt-5|grok-4|deepseek-chat|qwen/qwen3-coder-next|...
    temperature: 1.0, // controls the randomness of the text generated
    api_key: '**API Key**',
    log: 'output.log',
    max_tries: 1,
    timeout: 300, // maximum timeout (increase for long tasks)
    mcp_servers: [
        [
            'name' => 'example-mcp',
            'url' => 'https://modelcontextprotocol.io/mcp',
            'authorization_token' => '...'
        ]
    ],
    session_id: null, // submit session to continue a conversation (get with $ai->getSessionId())
    history: null, // submit messages (get with $ai->getSessionContent()),
    stream: false,
    url: null // overwrite connection url (e.g. for lmstudio or ollama)
);

$ai->ask(prompt: 'Wer wurde 2018 Fußball-Weltmeister?');
// ['response' => 'Frankreich.', 'success' => true, 'costs' => 0.001]

$ai->ask(prompt: 'Was ist auf dem Bild zu sehen?', files: 'lorem.jpg');
// ['response' => 'Auf dem Bild ist eine Katze zu sehen.', 'success' => true, 'costs' => 0.001]

$ai->ask(prompt: 'Wie lautet das erste Wort in der PDF?', files: 'lorem.pdf');
// ['response' => 'Das erste Wort lautet "Lorem".', 'success' => true, 'costs' => 0.001]

$ai->ask(prompt: 'Fasse die folgenden Dokumente zusammen.', files: ['1.pdf', '2.jpg']);
// ['response' => '...', 'success' => true, 'costs' => 0.001]

$ai->ask(prompt: 'Was habe ich vorher gefragt?');
// ['response' => 'Du hast gefragt: "Wie lautet das erste Wort in der PDF?"', 'success' => true, 'costs' => 0.001]

aihelper::getProviders() // gets overview of providers and models with costs

aihelper::create(provider: '...', api_key: '...')->fetchModels() // dynamically get models of provider via api

$ai->ping() // gets health, returns true|false

$ai->getSessionId() // get current session id

$ai->getSessionContent() // gets messages in chat history

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
// ['name' => '...', 'online' => true, 'instructions' => '...', 'tools' => ['...']]
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

## lm studio / runpod

### helper script

#### usage

- `cd ./runpod`
- `./runpod.sh status`
- `./runpod.sh create`
- `./runpod.sh load`
- `./runpod.sh unload`
- `./runpod.sh delete`

#### installation

- `ssh-keygen -t ed25519 -C "name@tld.com"`
- `wget https://github.com/Run-Pod/runpodctl/releases/download/v1.14.3/runpodctl-linux-amd64 -O runpodctl`
- `chmod +x runpodctl`
- `mv runpodctl /usr/bin/runpodctl`
- `runpodctl config --apiKey <RUNPOD_API_KEY>`
- `vi ./runpod/runpod.json`

### recommended models

| GPU      | HDD    | Model                | Context length |
| -------- | ------ | -------------------- | -------------- |
| RTX 5090 | 100 GB | Qwen3.5-35B-A3B-GGUF | 65536          |
| H200 SXM | 200 GB | MiniMax-M2.1-GGUF    | 131072         |
| B200     | 200 GB | MiniMax-M2.1-GGUF    | 131072         |

### manual deployment

- [https://www.runpod.io](https://www.runpod.io) > Pods > Deploy
- Pod template > Edit
- Expose HTTP ports (comma separated): `1234`
- Container Disk: `100 GB`
- Copy: SSH over exposed TCP
- `ssh root@xxxxxxxxxx -p xxxxx`

```sh
curl -fsSL https://lmstudio.ai/install.sh | bash
export PATH="/root/.lmstudio/bin:$PATH"
# this is unreliable
#lms get -y qwen/qwen3-coder-next
mkdir -p ~/.lmstudio/models/unsloth/MiniMax-M2.1-GGUF
cd ~/.lmstudio/models/unsloth/MiniMax-M2.1-GGUF
wget -c https://huggingface.co/unsloth/MiniMax-M2.1-GGUF/resolve/main/MiniMax-M2.1-UD-TQ1_0.gguf
mkdir -p ~/.lmstudio/models/lmstudio-community/Qwen3.5-35B-A3B-GGUF
cd ~/.lmstudio/models/lmstudio-community/Qwen3.5-35B-A3B-GGUF
wget -c https://huggingface.co/lmstudio-community/Qwen3.5-35B-A3B-GGUF/resolve/main/Qwen3.5-35B-A3B-Q4_K_M.gguf
lms server start --port 1234 --bind 0.0.0.0
```

### more commands

- `curl http://localhost:1234/v1/models`
- `lms --help`
- `lms status`
- `lms server stop`
- Copy: HTTP services > URL

```sh
curl https://xxxxxxxxx-1234.proxy.runpod.net/v1/responses \
  -X POST \
  -H "Content-Type: application/json" \
  -d '{
    "model": "xxxxxxxxxxxxx",
    "messages": [
        {"role": "user", "content": [{"type": "input_text", "text": "hi"}]}
    ],
    "temperature": 1.0,
    "stream": true
  }'
```
