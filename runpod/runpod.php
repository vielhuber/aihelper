<?php
require_once __DIR__ . '/../vendor/autoload.php';
use vielhuber\aihelper\aihelper;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

echo 'ℹ️ Dynamically extracting pods...' . PHP_EOL;

$pods = [];
$status = shell_exec('bash ' . __DIR__ . '/runpod.sh status 2>/dev/null') ?? '';
$status = preg_replace('/\x1b\[[0-9;]*[mGKHF]/', '', $status);
$blocks = preg_split('/^=== .+ ===$/m', $status);
foreach ($blocks as $blocks__value) {
    preg_match('#https://[a-z0-9]+-1234\.proxy\.runpod\.net#', $blocks__value, $url);
    preg_match('/Model:.*?TTL\s+(\S+)/s', $blocks__value, $model);
    if (!empty($url[0]) && !empty($model[1])) {
        $pods[] = ['url' => $url[0], 'model_id' => $model[1]];
    }
}

if (empty($pods)) {
    echo '⛔ Failed to get pods.' . PHP_EOL;
    die();
}
echo '✅ Successfully extracted ' . count($pods) . ' pods.' . PHP_EOL;

// generate mcps
$mcp = [];
$return = __curl(
    @$_SERVER['MCP_SERVER_TEST_AUTH_URL'],
    [
        'client_id' => @$_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_ID'],
        'client_secret' => @$_SERVER['MCP_SERVER_TEST_AUTH_CLIENT_SECRET'],
        'audience' => @$_SERVER['MCP_SERVER_TEST_AUTH_AUDIENCE'],
        'grant_type' => 'client_credentials'
    ],
    'POST'
);
if (empty($return->result)) {
    echo '⛔ Failed to get access token.' . PHP_EOL;
    die();
}
$auth_token = $return->result->access_token;
$i = 1;
while (@$_SERVER['MCP_SERVER_TEST_' . $i . '_URL'] != '') {
    $mcp[] = [
        'name' => 'MCP Server ' . $i,
        'url' => @$_SERVER['MCP_SERVER_TEST_' . $i . '_URL'],
        'authorization_token' => $auth_token
    ];
    $i++;
}

echo PHP_EOL;

foreach ($pods as $pods__key => $pods__value) {
    echo '----------------------------------------' . PHP_EOL;
    echo '----------------------------------------' . PHP_EOL;

    $ai = aihelper::create(
        provider: 'lmstudio',
        model: $pods__value['model_id'],
        temperature: 1.0,
        timeout: 1800,
        api_key: null,
        log: 'runpod.log',
        max_tries: 1,
        mcp_servers: $mcp,
        session_id: null,
        history: [],
        stream: false,
        url: $pods__value['url'] . '/v1'
    );

    $average = 0;

    $prompts = ['Hallo! Wie geht es Dir?', 'Was ist 2+2?', 'Erzähl mir eine Geschichte.'];
    echo 'ℹ️ Testing ' . $pods__value['model_id'] . '...' . PHP_EOL;
    foreach ($prompts as $prompts__key => $prompt__value) {
        echo '----------------------------------------' . PHP_EOL;
        __log_begin();
        echo 'ℹ️ Request: ' . $prompt__value . PHP_EOL;
        $result = $ai->ask($prompt__value);
        if ($result['success'] === false || __nx($result['response'])) {
            echo '⛔ Response failed.' . PHP_EOL;
        } else {
            echo '✅ Response: ' .
                __truncate_string(__remove_newlines($result['response']), 50) .
                ' (' .
                mb_strlen($result['response']) .
                ' chars)' .
                PHP_EOL;
        }
        $time = __log_end(null, false)['time'];
        $average += $time;
        echo 'ℹ️ Time: ' . round($time, 2) . 's' . PHP_EOL;
        if ($prompts__key === count($prompts) - 1) {
            echo '----------------------------------------' . PHP_EOL;
        }
    }
    $average = $average / count($prompts);

    echo 'ℹ️ Average time: ' . round($average, 2) . 's' . PHP_EOL;

    if ($pods__key === count($pods) - 1) {
        echo '----------------------------------------' . PHP_EOL;
        echo '----------------------------------------' . PHP_EOL;
    }
}
