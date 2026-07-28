<?php
declare(strict_types=1);

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
    http_response_code(204);
    return;
}

$counterFile = (string) getenv('MCP_RETRY_COUNTER');
$attempt = is_file($counterFile) ? (int) file_get_contents($counterFile) + 1 : 1;
file_put_contents($counterFile, (string) $attempt);

if ($attempt < 3) {
    http_response_code(502);
    echo 'temporary gateway failure';
    return;
}

header('Content-Type: text/event-stream');
echo 'event: message' . "\n";
echo 'data: ' . json_encode([
    'jsonrpc' => '2.0',
    'id' => 1,
    'result' => [
        'tools' => [
            [
                'name' => 'test_tool',
                'description' => 'Test tool',
                'inputSchema' => ['type' => 'object', 'properties' => []]
            ]
        ]
    ]
]) . "\n\n";
