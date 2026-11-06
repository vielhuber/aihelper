import { spawn } from 'node:child_process';
import { createInterface } from 'node:readline';

let [binary, previousSession, model, effort, resumeLatest] = process.argv.slice(2);
let child = spawn(binary, ['acp', '--dangerously-skip-permissions']);
let input = createInterface({ input: process.stdin });
let messages = input[Symbol.asyncIterator]();
let requests = new Map();
let identifier = 0;
let session;
let active = 0;
let failure;
let finished = false;
let step = 0;
let textStep;
let tools = new Map();
let usage = {};

function emit(event) {
    process.stdout.write(JSON.stringify(event) + '\n');
}

function flushText() {
    if (textStep === undefined) return;
    emit({ event: 'step_update', step_update: { step_index: textStep, step_type: 'agent_response', state: 'DONE' } });
    textStep = undefined;
}

function finish(error) {
    if (finished) return;
    finished = true;
    flushText();
    emit({
        event: 'result',
        result: { conversation_id: session, status: error ? 'ERROR' : 'SUCCESS', error: error?.message, usage }
    });
    process.exitCode = error ? 1 : 0;
    child.stdin.end();
    input.close();
    process.stdin.destroy();
    let cleanup = setTimeout(() => child.kill(), 3000);
    cleanup.unref();
}

function request(method, params) {
    return new Promise((resolve, reject) => {
        let id = ++identifier;
        requests.set(id, { resolve, reject });
        child.stdin.write(JSON.stringify({ jsonrpc: '2.0', id, method, params }) + '\n');
    });
}

child.on('error', finish);
child.stderr.pipe(process.stderr);
child.on('close', () => {
    if (!finished) finish(new Error('Antigravity ACP exited before completing the turn.'));
});
createInterface({ input: child.stdout }).on('line', line => {
    try {
        let event = JSON.parse(line);
        if (event.method === 'session/request_permission') {
            let option = event.params.options.find(option => option.kind === 'reject_once');
            child.stdin.write(
                JSON.stringify({
                    jsonrpc: '2.0',
                    id: event.id,
                    result: {
                        outcome: option ? { outcome: 'selected', optionId: option.optionId } : { outcome: 'cancelled' }
                    }
                }) + '\n'
            );
            return;
        }
        if (event.method === 'session/update') {
            let update = event.params.update;
            if (update.sessionUpdate === 'agent_message_chunk' && update.content?.type === 'text') {
                textStep ??= step++;
                emit({
                    event: 'step_update',
                    step_update: {
                        step_index: textStep,
                        step_type: 'agent_response',
                        state: 'ACTIVE',
                        text_delta: update.content.text
                    }
                });
            }
            if (['tool_call', 'tool_call_update'].includes(update.sessionUpdate)) {
                flushText();
                let tool = { ...tools.get(update.toolCallId), ...update };
                tool.step ??= step++;
                tools.set(update.toolCallId, tool);
                let output = tool.rawOutput ?? tool.content?.map(item => item.content?.text ?? '').join('\n') ?? '';
                emit({
                    event: 'step_update',
                    step_update: {
                        conversation_id: session,
                        step_index: tool.step,
                        step_type: 'tool',
                        tool_call_id: update.toolCallId,
                        state: tool.status === 'failed' ? 'ERROR' : tool.status === 'completed' ? 'DONE' : 'ACTIVE',
                        tool_info: { name: tool.title ?? tool.kind ?? 'tool', parameters: tool.rawInput ?? {}, output }
                    }
                });
            }
            return;
        }
        let pending = requests.get(event.id);
        if (!pending) return;
        requests.delete(event.id);
        if (event.error) pending.reject(new Error(event.error.message));
        if (!event.error) pending.resolve(event.result);
    } catch (error) {
        finish(error);
    }
});

try {
    await request('initialize', {
        protocolVersion: 1,
        clientCapabilities: {},
        clientInfo: { name: 'aihelper', version: '1' }
    });
    if (!previousSession && resumeLatest === '1') {
        let listing = await request('session/list', {});
        previousSession = listing.sessions
            ?.filter(entry => entry.cwd === process.cwd())
            .sort((first, second) => String(second.updatedAt).localeCompare(String(first.updatedAt)))[0]?.sessionId;
    }
    let response = await request(previousSession ? 'session/resume' : 'session/new', {
        cwd: process.cwd(),
        mcpServers: [],
        ...(previousSession ? { sessionId: previousSession } : {})
    });
    session = response.sessionId ?? previousSession;
    if (!session) throw new Error('Antigravity ACP did not open a session.');
    for (let [configId, value] of [
        ['model', model],
        ['thought_level', effort]
    ]) {
        if (!value) continue;
        let selection = await request('session/set_config_option', { sessionId: session, configId, value });
        response = { ...response, ...selection };
    }
    emit({ event: 'init', conversation_id: session });
    for await (let line of messages) {
        let { text } = JSON.parse(line);
        active++;
        request('session/prompt', { sessionId: session, prompt: [{ type: 'text', text }] })
            .then(result => {
                if (result.stopReason !== 'end_turn') {
                    failure ??= new Error(`Antigravity ACP ended with ${result.stopReason}.`);
                }
                if (result.usage) {
                    usage = {
                        input_tokens: result.usage.inputTokens ?? 0,
                        output_tokens: result.usage.outputTokens ?? 0,
                        cache_read_tokens: result.usage.cachedReadTokens ?? 0
                    };
                }
            })
            .catch(error => {
                failure ??= error;
            })
            .finally(() => {
                active--;
                if (active === 0) finish(failure);
            });
    }
} catch (error) {
    finish(error);
}
