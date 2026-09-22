[![build status](https://github.com/vielhuber/aihelper/actions/workflows/ci.yml/badge.svg)](https://github.com/vielhuber/aihelper/actions)
[![github tag](https://img.shields.io/github/v/tag/vielhuber/aihelper)](https://github.com/vielhuber/aihelper/tags)
[![code style](https://img.shields.io/badge/code_style-psr--12-ff69b4.svg)](https://www.php-fig.org/psr/psr-12/)
[![license](https://img.shields.io/github/license/vielhuber/aihelper)](https://github.com/vielhuber/aihelper/blob/main/LICENSE.md)
[![last commit](https://img.shields.io/github/last-commit/vielhuber/aihelper)](https://github.com/vielhuber/aihelper/commits)
[![php version support](https://img.shields.io/packagist/php-v/vielhuber/aihelper)](https://packagist.org/packages/vielhuber/aihelper)
[![packagist downloads](https://img.shields.io/packagist/dt/vielhuber/aihelper)](https://packagist.org/packages/vielhuber/aihelper)

# 🤖 aihelper 🤖

aihelper provides a single, consistent php interface for multiple ai providers. it supports chat and vision use cases, session-aware conversations, structured evaluations with typesafe, robust retry logic, logging, simple cost tracking, and optional model context protocol (mcp) integration — with a shared factory and result format.

## installation

```
composer require vielhuber/aihelper
```

## usage

```php
use vielhuber\aihelper\aihelper;

$ai = aihelper::create(
    provider: 'anthropic', // anthropic|google|openai|xai|deepseek|openrouter|cliproxyapi|elevenlabs|typesafe|nvidia|llamacpp|lmstudio|claudecode|codex|opencode
    model: 'claude-opus-4-1', // claude-opus-4-1|gemini-2.5-pro|gpt-5|grok-4|deepseek-chat|qwen/qwen3-coder-next|...
    effort: null, // null|none|minimal|low|medium|high|xhigh|max|ultra — reasoning effort, ignored when the provider/model has no supported reasoning control
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
            'forced_tool_arguments' => ['tool_name_1' => ['fixed_argument' => 'value']],
            'reuse_connection' => false // optional: cli harness only, send Connection: close so no idle socket survives a proxy that reaps them (costs one handshake per call)
        ]
    ],
    mcp_servers_call_type: 'remote', // remote = provider calls mcp servers directly, local = client-side tool loop via aihelper
    session_id: null, // submit session to continue a conversation (get with $ai->getSessionId())
    cli_session_id: null, // cli harness only: resume this exact native session (get with $ai->getCliSessionId())
    cli_resume_latest: true, // cli harness only: without cli_session_id, continue the newest session in cli_workdir instead of starting fresh
    cli_native_memory: true, // cli harness only: let the harness load and maintain its own automatic long-term memory
    cli_session_home: null, // cli harness only: persistent native history, configuration and skills for this session
    cli_auth_home: null, // cli harness only: persistent authentication profile shared by separate session homes
    history: null, // submit messages (get with $ai->getSessionContent()),
    system_prompt: null, // chat providers: cli harnesses receive a real system prompt, other chat providers prepend it to the session (same as writing it into `history` yourself or calling $ai->setSystemPrompt() later)
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

### typesafe / jev evaluations

[typesafe](https://docs.typesafe.ai/introduction) evaluates text or structured data with Jev instead of generating a chat response. use the existing factory and `evaluate(state:, questions:)`, without additional question classes:

```php
use vielhuber\aihelper\aihelper;

$ai = aihelper::create(provider: 'typesafe', model: 'jev-latest', api_key: $apiKey);

$ticket = [
    'message' => 'Meine Rechnung wurde doppelt abgebucht. Bitte sofort erstatten.',
    'order' => [
        'number' => 'A-104',
        'amount' => 49.9,
        'currency' => 'EUR'
    ]
];

$result = $ai->evaluate(
    state: $ticket,
    questions: [
        [
            'type' => 'choice',
            'key' => 'team',
            'instructions' => 'Welches Team ist zuständig?',
            'criteria' => [
                'support' => 'Technische Probleme',
                'sales' => 'Kaufberatung',
                'billing' => 'Rechnungen und Zahlungen'
            ]
        ],
        [
            'type' => 'score',
            'key' => 'urgency',
            'instructions' => 'Wie dringend ist die Anfrage?',
            'criteria' => [
                'Keine zeitliche Dringlichkeit',
                'Zeitnahe Bearbeitung erforderlich',
                'Sofortiges Handeln erforderlich'
            ]
        ],
        [
            'type' => 'noul',
            'key' => 'refund',
            'instructions' => 'Wird eine Rückerstattung verlangt?'
        ]
    ]
);

if (!$result['success']) {
    throw new RuntimeException($result['response'] ?? 'Evaluation aborted.');
}

$answers = $result['response'];
$team = $answers->team->choice;
$urgency = $answers->urgency->score;
$refund = $answers->refund->noul;
```

`questions` is a non-empty list. each entry has a `type` and a unique, non-empty string `key`. the adapter converts these keys to the native TypeSafe question IDs; the model does not see them. keep a single entry for one question, or mix any number of the three question types in one request. the response is always an object keyed by these IDs, even for one question. multiple questions are not a fourth primitive.

| type     | criteria                                                           | answer fields                                                                                              |
| -------- | ------------------------------------------------------------------ | ---------------------------------------------------------------------------------------------------------- |
| `choice` | a map of option names to descriptions (up to 255 options)          | `choice`: string; `confidence`: float from 0 to 1; `probabilities`: object with every option's probability |
| `score`  | an ordered list of 2–10 level descriptions                         | `score`: float from 0 to the last level index; `confidence`, `probabilities`, `legend`                     |
| `noul`   | optional descriptions under the string keys `'true'` and `'false'` | `noul`: float from 0 to 1, the probability of yes, **not a boolean**                                       |

a three-level score ranges from 0 to 2 and can fall between levels. score `probabilities` and `legend` use zero-based level keys, accessible as `$answers->urgency->probabilities->{'0'}`. confidence is separate from the winning option's probability and does not guarantee correctness. no automatic thresholds, rounding or boolean conversion are applied.

`state` accepts a string or JSON-compatible PHP arrays/objects, including nested records and message lists. paths and URLs are only text, not attachments to fetch. convert images, audio, video and binary files to text or structured data first. instructions and individual criterion descriptions also support structured arrays/objects. instructions, choice descriptions and noul descriptions may be `null`; score level descriptions must not be `null`. the live API rejects null score levels with HTTP 422, matching the [HTTP reference](https://docs.typesafe.ai/api), despite the broader nullable `EntryType` claim in the [advanced documentation](https://docs.typesafe.ai/primitives/advanced). aihelper rejects these levels before sending a request. for a noul, optional criteria can be added as follows (use string keys, not PHP booleans):

```php
'criteria' => [
    'true' => 'An explicit request to return money.',
    'false' => 'A billing question without a refund request.'
]
```

the result retains `success`, `response` and `costs`, plus `aborted`. successful evaluations also expose `model` (the resolved version), `input_tokens`, `output_tokens` and `request_id` (when supplied by the server, otherwise `null`). numeric zero is a valid answer. `costs` is in USD and retains small amounts without rounding them to zero: the documented Jev 1.13 price is $0.042 per million input tokens; output tokens are free.

`model` defaults to `jev-latest`; `jev-preview` and the pinned `jev-1.13.0` are also available. `fetchModels()` merges authenticated discovery with the known catalog; `ping()` requires successful authenticated discovery, not just an offline fallback. pass `api_key` explicitly (for example from `TYPESAFE_API_KEY` loaded by the application). no new dependency is needed.

evaluations are stateless: repeated calls neither read nor update chat history. chat-only factory options such as history, system prompts, MCP, streaming, temperature, effort and auto-compaction are ignored for TypeSafe. `ask()` throws `BadMethodCallException`; `evaluate()` on other providers does the same. existing chat providers are unchanged.

invalid question shapes or non-positive `timeout` / `max_tries` values raise `InvalidArgumentException`; non-JSON-compatible data raises `JsonException`. HTTP, connection and malformed-response failures return `success => false` with an error string in `response`. cancellation through `abort_callback` returns `aborted => true` and `response => null`. TypeSafe defaults to three total attempts (`max_tries: 1` disables retries); only connection failures (including interrupted response bodies), HTTP 408/429 and 5xx are retried, with exponential backoff and `Retry-After` / `retry-after-ms` support. waits remain cancellable. `timeout` defaults to 300 seconds per HTTP request; a server-requested retry delay exceeding this timeout ends the call instead of retrying early. authentication and validation errors are not retried.

Jev's documented input limits are 64k tokens for the whole request and 32k for the state plus the longest question. English currently has the best accuracy; verify other languages against representative data. the provider does not estimate or truncate these inputs. see the [API contract](https://docs.typesafe.ai/api), [state formats](https://docs.typesafe.ai/concepts/state), [primitives](https://docs.typesafe.ai/primitives) and [current models/pricing](https://docs.typesafe.ai/models).

run the isolated provider contract tests without API credentials or paid requests:

```sh
./vendor/bin/phpunit --filter test__typesafe_
```

with a valid `TYPESAFE_API_KEY` in `.env`, the following live test makes seven paid evaluations: each primitive individually, mixed and structured questions, the maximum choice/score sizes, and all three model names. without the key, it is skipped:

```sh
./vendor/bin/phpunit --filter test__ai_typesafe
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

a successful terminal event followed by exit code `0` may return `success: true` with `response: ''`, including when streaming. missing completion events, aborts and provider errors do not qualify; ordinary empty api responses remain errors and retain their retry behavior.

by default every turn continues the newest thread of `cli_workdir` and opens a new one only when that directory has none yet.
set `cli_resume_latest: false` for an explicitly fresh thread, then persist `$ai->getCliSessionId()` and pass it as
`cli_session_id` on later calls to resume that exact native thread. an explicit `cli_session_id` always takes precedence.
codex keeps injected config and skills isolated per aihelper session while storing its threads in the native
`~/.codex` state. to include threads originally started by aihelper's non-interactive `codex exec`, resume from
an interactive terminal with `codex resume --last --include-non-interactive`.

pass `cli_session_home` to keep one harness session's native history, configuration and skills in a dedicated
persistent directory. pass `cli_auth_home` separately to share one authenticated subscription across multiple
session homes without sharing their histories or configuration. both options work for claude code, codex and
opencode and can also be changed before the first request with `setCliStorage()`.

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
    cli_ssh_reverse_tunnel: '40123:127.0.0.1:8000', // optional: -R forward opened for the agent run only, e.g. to reach a local mcp without a public address
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
// no opencode cli login or local usage database is required for these exact limits
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

aihelper streams model output as server-sent events (sse). see [/tests/stream/index.html](tests/stream/index.html) for an example.

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

assistant text, native reasoning and activities use provider-independent json objects for apis, claude code, codex and opencode. text retains the existing unnamed sse messages and `choices[0].delta.content` field; it is not emitted twice:

```text
data: {"type":"text.delta","id":"text-a1","delta":"I will inspect the files.","phase":"commentary","choices":[{"delta":{"content":"I will inspect the files."}}],"seq":1}

event: reasoning
data: {"type":"reasoning.delta","id":"reasoning-a1","delta":"Checking dependencies...","seq":2}

event: reasoning
data: {"type":"activity.upsert","id":"tool-1","kind":"tool","label":"Read README.md","status":"running","detail":{"path":"README.md"},"captures_content":true,"seq":3}

event: reasoning
data: {"type":"activity.upsert","id":"tool-1","kind":"tool","label":"Read README.md","status":"completed","detail":{"output":"..."},"captures_content":true,"seq":4}

data: {"type":"text.delta","id":"text-a2","delta":"Implemented.","phase":null,"choices":[{"delta":{"content":"Implemented."}}],"seq":5}

data: {"type":"text.phase","id":"text-a2","phase":"final_answer","seq":6}
```

- for `text.delta` and `reasoning.delta`, append `delta` to the block identified by `id`. render assistant text, including progress messages, visibly in the conversation; only reasoning and activities belong in collapsible details. subsequent tools must not move text into reasoning. read either `delta` or the compatibility field `choices[0].delta.content`, never both.
- text `phase` is `commentary`, `final_answer` or `null` (unknown). codex and the responses api preserve explicit phases. `text.phase` updates metadata on an existing block without appending text. claude code and opencode do not provide this phase; aihelper does not guess it from tool calls, wording or a completed text block. a failed or interrupted run does not turn partial text into a final answer.
- for `activity.upsert`, insert the block once and update it in place. `detail` is its current snapshot: an object, array, string or `null`. activity `kind`: `tool`, `plan`, `task`, `status`, `usage`, `warning`, `error` or `diagnostic` (stderr). `status`: `running`, `completed` or `error`. `captures_content` remains for compatibility with older consumers; new consumers must not use it to reclassify assistant text.
- `id` identifies a display block; `seq` orders events within one `ask()`, not schema versions. scope both to the current response. unchanged snapshots and internal token telemetry are omitted; progress updates and errors remain visible.
- events are flushed immediately. codex app-server streams text deltas without repeating completed items; opencode enables `--thinking` and retains its native completed-block granularity. activity details redact secrets and binary data and mark shortened strings or collections. native reasoning and the complete tool history in `getSessionContent()` are not shortened. display ids and phase updates do not add unsupported fields to the harness conversation history.

this replaces the previous formatted transcript (`kind: transcript`, `boundary`, display text in `delta`); consumers must handle the new event types. session notifications remain unchanged. harnesses send `[DONE]` once, after the complete `ask()` including goal continuations and exit events, not after individual native turns. `[DONE]` marks the end of delivery, not successful execution; errors and the request result remain authoritative.

if streaming stutters on apache2 with php-fpm, disable gzip for the streaming route and configure fastcgi to forward packets without buffering:

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
