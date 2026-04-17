<?php

declare(strict_types=1);

namespace App\Controllers;

use BaseApi\App;
use BaseApi\Controllers\Controller;
use BaseApi\Http\JsonResponse;

class HomeController extends Controller
{
    public function get(): JsonResponse
    {
        return JsonResponse::ok([
            'name' => App::config('app.name', 'BaseApi'),
            'env' => App::config('app.env', 'local'),
            'status' => 'running',
            'message' => 'API 服务已启动',
            'links' => [
                'health' => '/health',
                'benchmark' => '/benchmark',
                'openapi' => '/openapi.json',
            ],
        ]);
    }
}
