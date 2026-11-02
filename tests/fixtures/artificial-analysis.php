<?php
declare(strict_types=1);

namespace vielhuber\stringhelper {
    final class __
    {
        public static array $fixture;

        /**
         * Supply recorded responses without contacting the external service.
         */
        public static function curl(...$arguments): object
        {
            file_put_contents(self::$fixture['calls_file'], $arguments['url'] . "\n", FILE_APPEND | LOCK_EX);
            usleep(self::$fixture['delay'] ?? 0);
            if (self::$fixture['responses'] === []) {
                throw new \RuntimeException('Unexpected Artificial Analysis request.');
            }
            $fixture = array_shift(self::$fixture['responses']);
            $response = json_decode(json_encode($fixture, JSON_THROW_ON_ERROR));
            $response->headers = $fixture['headers'] ?? [];
            return $response;
        }
    }
}

namespace {
    require __DIR__ . '/../../src/aihelper.php';

    $fixture = json_decode(stream_get_contents(STDIN), associative: true, flags: JSON_THROW_ON_ERROR);
    \vielhuber\stringhelper\__::$fixture = $fixture;
    $_SERVER['ARTIFICIAL_ANALYSIS_API_KEY'] = $fixture['api_key'];
    $helper = (new ReflectionClass(\vielhuber\aihelper\ai_openai::class))->newInstanceWithoutConstructor();
    $normalize = new ReflectionMethod($helper, 'normalizeAndEnrichModels');
    $results = [];
    for ($iteration = 0; $iteration < ($fixture['repeat'] ?? 1); $iteration++) {
        if ($iteration > 0) {
            usleep($fixture['between_calls'] ?? 0);
        }
        $results[] = $normalize->invoke($helper, [['name' => 'fixture-one'], ['name' => 'fixture-two']]);
    }
    echo json_encode($results, JSON_THROW_ON_ERROR);
}
