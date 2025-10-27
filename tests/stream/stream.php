<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use vielhuber\aihelper\aihelper;
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}
if (!isset($_GET['prompt'])) {
    die();
}
@unlink('../ai.log');
$ai = aihelper::create(
    service: 'claude',
    model: 'claude-haiku-4-5',
    temperature: 1.0,
    api_key: $_SERVER['CLAUDE_API_KEY'],
    log: '../ai.log',
    stream: true
);
$response = $ai->ask($_GET['prompt']);
