<?php
declare(strict_types=1);
require_once __DIR__ . '/../../vendor/autoload.php';
use vielhuber\aihelper\aihelper;

if (
    ($_SERVER['REMOTE_ADDR'] ?? '') !== '127.0.0.1' &&
    ($_SERVER['REMOTE_ADDR'] ?? '') !== '::1' &&
    ($_SERVER['AIHELPER_STREAM_TEST_ALLOW_REMOTE'] ?? '') !== 'true'
) {
    http_response_code(403);
    die();
}

if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

if (
    !isset($_GET['provider']) ||
    !isset($_GET['model']) ||
    !isset($_GET['api_key']) ||
    !isset($_GET['log']) ||
    !isset($_GET['prompt'])
) {
    die();
}

$provider = (string) $_GET['provider'];
$model = (string) $_GET['model'];
$api_key = (string) $_GET['api_key'];
$url = (string) ($_GET['url'] ?? '');
$prompt = mb_substr((string) $_GET['prompt'], 0, 1000);
$log = basename((string) $_GET['log']);

if (
    !preg_match('/^[a-z0-9_-]+$/i', $provider) ||
    !preg_match('/^[a-z0-9._:\/#-]+$/i', $model) ||
    !preg_match('/^[A-Z0-9_]*$/', $api_key) ||
    ($url !== '' && !preg_match('/^[A-Z0-9_]+$/', $url)) ||
    !preg_match('/^[a-z0-9._-]+\.log$/i', $log)
) {
    http_response_code(400);
    die();
}

$log = __DIR__ . '/' . $log;

if (file_exists($log)) {
    unlink($log);
}
$ai = aihelper::create(
    provider: $provider,
    model: $model,
    temperature: 1.0,
    api_key: $api_key !== '' ? $_SERVER[$api_key] ?? '' : '',
    url: $url !== '' ? $_SERVER[$url] ?? '' : null,
    log: $log,
    stream: true
);

$result = $ai->ask($prompt);
