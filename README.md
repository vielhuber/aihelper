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
    provider: 'anthropic', // anthropic|google|openai|xai|deepseek|openrouter|cliproxyapi|elevenlabs|nvidia|llamacpp|lmstudio|claudecode|codex|opencode
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
            'allowed_tools' => ['tool_name_1', 'tool_name_2'], // optional: restrict to specific tools (null or omit = all tools allowed)
            'default_tool_arguments' => ['tool_name_1' => ['optional_argument' => 'default']],
            'forced_tool_arguments' => ['tool_name_1' => ['fixed_argument' => 'value']]
        ]
    ],
    mcp_servers_call_type: 'remote', // remote = provider calls mcp servers directly, local = client-side tool loop via aihelper
    session_id: null, // submit session to continue a conversation (get with $ai->getSessionId())
    cli_session_id: null, // cli harness only: resume this exact native session (get with $ai->getCliSessionId())
    cli_resume_latest: true, // cli harness only: without cli_session_id, continue the newest session in cli_workdir instead of starting fresh
    cli_native_memory: true, // cli harness only: let the harness load and maintain its own automatic long-term memory
    history: null, // submit messages (get with $ai->getSessionContent()),
    system_prompt: null, // works with every provider — cli harnesses receive it as a real system prompt, everyone else gets it prepended to the session (same as writing it into `history` yourself or calling $ai->setSystemPrompt() later)
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

### cli harnesses

the providers `claudecode`, `codex` and `opencode` drive the locally installed cli agent instead of a chat completion endpoint. they own their system prompt, tools and history, so only the newest user turn is handed over. install and log them in once:

```
npm i -g @anthropic-ai/claude-code @openai/codex
curl -fsSL https://opencode.ai/install | bash
claude auth login
codex login --device-auth
opencode auth login
```

by default every turn continues the newest thread of `cli_workdir` and opens a new one only when that directory has none yet.
set `cli_resume_latest: false` for an explicitly fresh thread, then persist `$ai->getCliSessionId()` and pass it as
`cli_session_id` on later calls to resume that exact native thread. an explicit `cli_session_id` always takes precedence.
codex keeps injected config and skills isolated per aihelper session while storing its threads in the native
`~/.codex` state. to include threads originally started by aihelper's non-interactive `codex exec`, resume from
an interactive terminal with `codex resume --last --include-non-interactive`.

set `cli_native_memory: false` when the caller supplies its own long-term memory. claude code then disables auto
memory while retaining `CLAUDE.md`, plugins and skills; codex neither generates nor loads its native memories.
opencode currently has no equivalent automatic semantic memory layer, so the option does not alter its sessions
or instruction files.

set `cli_ssh_host` to run the agent on another machine instead of locally — the working directory, the login and the threads are then that machine's:

```php
$ai = aihelper::create(
    provider: 'claudecode',
    system_prompt: 'Du bist ein Assistent für Tabellen und Tickets.',
    cli_workdir: '/var/www/project', // optional, defaults to a throwaway directory per session
    cli_ssh_host: 'host.docker.internal', // optional, runs locally when omitted
    cli_ssh_user: 'root', // optional
    cli_ssh_port: 22, // optional
    cli_ssh_key: '/root/.ssh/id_ed25519', // optional
    cli_skills: [
        'excel' =>
            "---\nname: excel\ndescription: create and read xlsx files. use for tables and grades.\n---\n\nuse the excel_create_file tool ...",
        'jira' =>
            "---\nname: jira\ndescription: read jira issues via jql. use for tickets, sprints, backlogs.\n---\n\n..."
    ]
);
// get cli usage limits for claude code, codex, opencode and antigravity
// to get exact opencode go usage limits, sign in at `https://opencode.ai`,
// set OPENCODE_GO_AUTH_COOKIE in .env to the value of cookie "auth"
$ai->getCliUsageLimits()
// [
//     ['type' => '5-hour', 'scope' => null, 'percent used' => 20, 'resets_at' => '2026-06-29T17:59:00+02:00'],
//     ['type' => 'weekly', 'scope' => null, 'percent used' => 10, 'resets_at' => '2026-07-06T03:03:00+02:00'],
//     ['type' => 'weekly', 'scope' => 'Fable', 'percent used' => 93, 'resets_at' => '2026-07-06T03:03:00+02:00']
// ]

// get manually redeemable codex reset credits
$ai->getCliUsageResetCredits()
// ['available_count' => 1, 'credits' => [['title' => 'Full reset', 'expires_at' => '...']]]

// redeem the next available codex reset credit
$ai->triggerCliUsageReset()
// ['success' => true, 'status' => 'reset', 'windows_reset' => 2]

// get all requests from a local clis
aihelper::getCliApiRequests(
    limit: 100, // null: all
    date_from: '2026-07-01 00:00:00', // null: all
    date_until: '2026-07-31 23:59:59', // null: all
    include_body: false,
    group_by: false // true = collapse per project
);

// delete request logs only
aihelper::purgeCliApiRequestLogs(
    date_from: null, // null: all
    date_until: null // null: all
);
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

`event: reasoning` contains native model reasoning and a provider-independent, redacted process transcript for tool, shell, file, search, plan and skill activity. Large details are shortened only in this visible stream; `getSessionContent()` retains the complete structured tool history. if streaming stutters on apache2 with php‑fpm, be sure that gzip is disabled for the streaming route and also adjust your virtualhost so fastcgi forwards packets immediately (no buffering):

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

### aborting a request

pass an `abort_callback` to stop a request that is already running. it is asked between chunks, so keep it cheap — a file check or a flag, never a database round trip. hand it over on `create()` or later with `setAbortCallback()`, which also accepts `null` to clear it again.

```php
$ai = aihelper::create(
    /* ... */
    abort_callback: fn(): bool => is_file('/tmp/cancel-' . $id)
);

$result = $ai->ask('Wer wurde 2018 Fußball-Weltmeister?');
// $result = ['response' => null, 'success' => false, 'costs' => 0.0, 'aborted' => true]
```

with a chat completion endpoint the http stream is cancelled mid-transfer. a cli harness receives `SIGINT` first so it can close its append-only session file the way ctrl+c would, and is terminated on the local and the remote side only afterwards; the interrupted turn stays in the native thread and is resumed by the next call. check `$result['aborted']` to tell a stop apart from a failure — a stopped request has no answer, but nothing went wrong, and it is never retried. a callback that throws counts as
"keep going", so an unreachable cancel signal cannot kill a healthy request.
