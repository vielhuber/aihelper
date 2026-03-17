<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use vielhuber\aihelper\aihelper;

if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

if (!isset($_GET['provider']) || !isset($_GET['model']) || !isset($_GET['api_key']) || !isset($_GET['log'])) {
    die();
}

if (file_exists($_GET['log'])) {
    unlink($_GET['log']);
}
$ai = aihelper::create(
    provider: $_GET['provider'],
    model: $_GET['model'],
    temperature: 1.0,
    api_key: $_SERVER[$_GET['api_key']] ?? '',
    url: isset($_GET['url']) && $_GET['url'] != '' ? $_SERVER[$_GET['url']] ?? '' : null,
    log: $_GET['log'],
    stream: true
);

$result = $ai->ask($_GET['prompt']);
