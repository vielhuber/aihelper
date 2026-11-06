import { spawn } from 'node:child_process';
import { randomBytes } from 'node:crypto';
import { createInterface } from 'node:readline';

let [binary, ...args] = process.argv.slice(2);
let password = randomBytes(32).toString('base64url');
let environment = { ...process.env, OPENCODE_PASSWORD: password };
let input = createInterface({ input: process.stdin });
let messages = input[Symbol.asyncIterator]();
let server;
let runner;
let session;
let submissions = Promise.resolve();
let waiting = [];

function fail(error) {
    process.stdout.write(JSON.stringify({ type: 'error', error: { message: error.message } }) + '\n');
    process.exitCode = 1;
    runner?.kill();
    server?.stdin.end();
    input.close();
    process.stdin.destroy();
}

try {
    let initial = await messages.next();
    if (initial.done) throw new Error('OpenCode received no initial prompt.');
    server = spawn(binary, ['serve', '--stdio', '--port', '0'], { env: environment });
    server.on('error', fail);
    server.stderr.pipe(process.stderr);
    let output = createInterface({ input: server.stdout });
    let endpoint = await new Promise((resolve, reject) => {
        output.once('line', line => {
            try {
                resolve(JSON.parse(line).url);
            } catch (error) {
                reject(error);
            }
        });
        server.once('close', () => reject(new Error('OpenCode server exited before reporting readiness.')));
    });
    if (!endpoint || new URL(endpoint).hostname !== '127.0.0.1') {
        throw new Error('OpenCode did not report a loopback endpoint.');
    }

    function submit(text) {
        submissions = submissions.then(async () => {
            let response = await fetch(`${endpoint}/api/session/${encodeURIComponent(session)}/prompt`, {
                method: 'POST',
                headers: {
                    Authorization: `Basic ${Buffer.from(`opencode:${password}`).toString('base64')}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ text, delivery: 'steer' }),
                signal: AbortSignal.timeout(30000)
            });
            if (!response.ok) throw new Error(`OpenCode rejected steering (${response.status}).`);
            await response.arrayBuffer();
        });
        submissions.catch(fail);
    }

    runner = spawn(binary, [...args, '--server', endpoint], { env: environment });
    runner.on('error', fail);
    runner.stderr.pipe(process.stderr);
    createInterface({ input: runner.stdout }).on('line', line => {
        try {
            let event = JSON.parse(line);
            if (event.sessionID) session = event.sessionID;
            if (session) {
                for (let text of waiting.splice(0)) submit(text);
            }
        } catch {
            // Non-JSON diagnostics are forwarded unchanged.
        }
        process.stdout.write(line + '\n');
    });
    runner.once('close', async code => {
        await submissions.catch(() => {});
        if (waiting.length) fail(new Error('OpenCode ended before accepting steering.'));
        process.exitCode ||= code ?? 1;
        server.stdin.end();
        input.close();
        process.stdin.destroy();
        let cleanup = setTimeout(() => server.kill(), 3000);
        cleanup.unref();
    });
    runner.stdin.end(JSON.parse(initial.value).text);
    for await (let line of messages) {
        let { text } = JSON.parse(line);
        if (session) submit(text);
        if (!session) waiting.push(text);
    }
} catch (error) {
    fail(error);
}
