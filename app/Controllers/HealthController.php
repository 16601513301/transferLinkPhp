<?php

namespace App\Controllers;

use Exception;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;
use BaseApi\App;
use BaseApi\Database\DbException;
use BaseApi\Database\Drivers\SqliteDriver;
use BaseApi\Cache\Cache;

#[Tag('Health')]
class HealthController extends Controller
{
    public string $db = '';

    public string $cache = '';

    public function get(): JsonResponse
    {

        $response = ['ok' => true];

        // Check if cache info is requested
        if ($this->cache === '1') {
            $response['cache'] = $this->getCacheInfo();
        }

        // Check if database check is requested
        if ($this->db === '1') {

            try {
                // Perform simple DB check
                $result = App::db()->scalar('SELECT 1');

                if ($result == 1) {
                    $response['db'] = true;

                    if (App::db()->getConnection()->getDriver() instanceof SqliteDriver) {
                        App::db()->raw('SELECT name FROM sqlite_master WHERE type="table"');
                    } else {
                        App::db()->raw('SHOW TABLES');
                    }
                } else {
                    return JsonResponse::error('数据库检查失败', 500);
                }
            } catch (DbException $e) {
                return JsonResponse::error('数据库连接失败', 500);
            }
        }

        return JsonResponse::ok($response);
    }

    /**
     * Simple cache check
     * 
     * @return array<string, mixed>
     */
    private function getCacheInfo(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'cache_working';

            $putSuccess = Cache::put($testKey, $testValue, 60);
            $getValue = Cache::get($testKey);
            $forgetSuccess = Cache::forget($testKey);

            return [
                'working' => $putSuccess && $getValue === $testValue && $forgetSuccess,
                'driver' => Cache::manager()->getDefaultDriver()
            ];
        } catch (Exception $exception) {
            return [
                'working' => false, 
                'error' => $exception->getMessage()
            ];
        }
    }

    public function post(): JsonResponse
    {

        return JsonResponse::ok(['ok' => true, 'received' => '已收到数据']);
    }
}
