<?php

namespace App\Controllers;

use BaseApi\Controllers\Controller;
use BaseApi\Http\JsonResponse;

class BenchmarkController extends Controller
{
    public function get(): JsonResponse
    {
        return JsonResponse::ok([
            'message' => '你好，世界！',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }
}
