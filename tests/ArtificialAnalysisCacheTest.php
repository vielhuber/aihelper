<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ArtificialAnalysisCacheTest extends TestCase
{
    private const SUCCESS = [
        'status' => 200,
        'result' => [
            'data' => [['slug' => 'fixture-one', 'evaluations' => ['artificial_analysis_intelligence_index' => 42]]],
            'pagination' => ['has_more' => false]
        ]
    ];

    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/aihelper-aa-test-' . bin2hex(random_bytes(8));
        mkdir($this->directory, 0700);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->directory);
    }

    private function startFixture(array $responses, array $options = []): array
    {
        $process = proc_open(
            [PHP_BINARY, __DIR__ . '/fixtures/artificial-analysis.php'],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            __DIR__,
            array_merge(getenv(), ['TMPDIR' => $this->directory, 'TMP' => $this->directory, 'TEMP' => $this->directory])
        );
        $this->assertIsResource($process);
        fwrite(
            $pipes[0],
            json_encode(
                array_merge(
                    [
                        'api_key' => 'fixture-key',
                        'responses' => $responses,
                        'calls_file' => $this->directory . '/requests'
                    ],
                    $options
                ),
                JSON_THROW_ON_ERROR
            )
        );
        fclose($pipes[0]);
        return [$process, $pipes];
    }

    private function finishFixture(array $execution): array
    {
        [$process, $pipes] = $execution;
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $this->assertSame('', $errors);
        return json_decode($output, associative: true, flags: JSON_THROW_ON_ERROR);
    }

    private function expireCache(): void
    {
        $files = glob($this->directory . '/aihelper-artificial-analysis-*.json');
        $this->assertCount(1, $files);
        $cache = json_decode(file_get_contents($files[0]), associative: true, flags: JSON_THROW_ON_ERROR);
        $cache['expires_at'] = time() - 1;
        file_put_contents($files[0], json_encode($cache, JSON_THROW_ON_ERROR));
    }

    private function requests(): array
    {
        $path = $this->directory . '/requests';
        return is_file($path) ? file($path, FILE_IGNORE_NEW_LINES) : [];
    }

    public function test_success_is_shared_between_processes_for_twenty_four_hours(): void
    {
        $first = $this->finishFixture($this->startFixture([self::SUCCESS]));
        $second = $this->finishFixture($this->startFixture([]));
        $this->assertSame(42, $first[0][0]['artificial_analysis_intelligence_index']);
        $this->assertSame($first, $second);
        $this->assertCount(1, $this->requests());
        $files = glob($this->directory . '/aihelper-artificial-analysis-*.json');
        $this->assertCount(1, $files);
        $contents = file_get_contents($files[0]);
        $cache = json_decode($contents, associative: true, flags: JSON_THROW_ON_ERROR);
        $this->assertGreaterThanOrEqual(time() + 86395, $cache['expires_at']);
        $this->assertLessThanOrEqual(time() + 86400, $cache['expires_at']);
        $this->assertStringNotContainsString('fixture-key', $contents);
    }

    public function test_expired_data_is_refreshed(): void
    {
        $this->finishFixture($this->startFixture([self::SUCCESS]));
        $this->expireCache();
        $fresh = self::SUCCESS;
        $fresh['result']['data'][0]['evaluations']['artificial_analysis_intelligence_index'] = 84;
        $result = $this->finishFixture($this->startFixture([$fresh]));
        $this->assertSame(84, $result[0][0]['artificial_analysis_intelligence_index']);
        $this->assertCount(2, $this->requests());
    }

    public function test_failures_preserve_base_models_and_back_off_without_a_cache(): void
    {
        foreach ([0, 401, 403, 429, 500, 200] as $status) {
            $calls = count($this->requests());
            $options = ['api_key' => 'fixture-failure-' . $status];
            $first = $this->finishFixture($this->startFixture([['status' => $status]], $options));
            $second = $this->finishFixture($this->startFixture([], $options));
            $this->assertSame(['fixture-one', 'fixture-two'], array_column($first[0], 'name'));
            $this->assertNull($first[0][0]['artificial_analysis_intelligence_index']);
            $this->assertSame($first, $second);
            $this->assertCount($calls + 1, $this->requests());
        }
    }

    public function test_rate_limit_preserves_stale_data_until_retry_after(): void
    {
        $first = $this->finishFixture($this->startFixture([self::SUCCESS]));
        $this->expireCache();
        $limited = ['status' => 429, 'headers' => ['retry-after' => ['3600']]];
        $failed = $this->finishFixture($this->startFixture([$limited]));
        $again = $this->finishFixture($this->startFixture([]));
        $this->assertSame($first, $failed);
        $this->assertSame($first, $again);
        $this->assertCount(2, $this->requests());
        $file = glob($this->directory . '/aihelper-artificial-analysis-*.json')[0];
        $cache = json_decode(file_get_contents($file), associative: true, flags: JSON_THROW_ON_ERROR);
        $this->assertGreaterThanOrEqual(time() + 3595, $cache['expires_at']);
    }

    public function test_rate_limit_reset_is_honored_without_retry_after(): void
    {
        $reset = time() + 7200;
        $this->finishFixture(
            $this->startFixture([
                [
                    'status' => 429,
                    'headers' => ['x-ratelimit-reset' => [(string) $reset]]
                ]
            ])
        );
        $file = glob($this->directory . '/aihelper-artificial-analysis-*.json')[0];
        $cache = json_decode(file_get_contents($file), associative: true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame($reset, $cache['expires_at']);
    }

    public function test_failed_pagination_keeps_the_previous_complete_dataset(): void
    {
        $first = $this->finishFixture($this->startFixture([self::SUCCESS]));
        $this->expireCache();
        $partial = self::SUCCESS;
        $partial['result']['data'][0]['evaluations']['artificial_analysis_intelligence_index'] = 84;
        $partial['result']['pagination']['has_more'] = true;
        $result = $this->finishFixture($this->startFixture([$partial, ['status' => 500]]));
        $this->assertSame($first, $result);
        $this->assertCount(3, $this->requests());
    }

    public function test_successful_pagination_is_cached_in_full(): void
    {
        $first = self::SUCCESS;
        $first['result']['pagination']['has_more'] = true;
        $second = self::SUCCESS;
        $second['result']['data'][0]['slug'] = 'fixture-two';
        $result = $this->finishFixture($this->startFixture([$first, $second]));
        $cached = $this->finishFixture($this->startFixture([]));
        $this->assertSame(42, $result[0][1]['artificial_analysis_intelligence_index']);
        $this->assertSame($result, $cached);
        $this->assertSame(
            [
                'https://artificialanalysis.ai/api/v2/language/models/free?page=1',
                'https://artificialanalysis.ai/api/v2/language/models/free?page=2'
            ],
            $this->requests()
        );
    }

    public function test_parallel_processes_fetch_only_once(): void
    {
        $executions = [];
        for ($index = 0; $index < 4; $index++) {
            $executions[] = $this->startFixture([self::SUCCESS], ['delay' => 400000]);
        }
        foreach ($executions as $execution) {
            $result = $this->finishFixture($execution);
            $this->assertSame(42, $result[0][0]['artificial_analysis_intelligence_index']);
        }
        $this->assertCount(1, $this->requests());
    }

    public function test_different_api_keys_have_separate_caches(): void
    {
        $this->finishFixture($this->startFixture([self::SUCCESS]));
        $this->finishFixture($this->startFixture([self::SUCCESS], ['api_key' => 'another-fixture-key']));
        $this->assertCount(2, $this->requests());
        $this->assertCount(2, glob($this->directory . '/aihelper-artificial-analysis-*.json'));
    }

    public function test_a_long_running_process_retries_after_the_wait_period(): void
    {
        $limited = ['status' => 429, 'headers' => ['retry-after' => ['1']]];
        $results = $this->finishFixture(
            $this->startFixture(
                [$limited, self::SUCCESS],
                [
                    'repeat' => 2,
                    'between_calls' => 1100000
                ]
            )
        );
        $this->assertNull($results[0][0]['artificial_analysis_intelligence_index']);
        $this->assertSame(42, $results[1][0]['artificial_analysis_intelligence_index']);
        $this->assertCount(2, $this->requests());
    }
}
