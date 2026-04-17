<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\SmsLoginService;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;
use RuntimeException;

#[Tag('Authentication')]
class SmsCodeController extends Controller
{
    public string $mobile = '';

    #[ResponseType([
        'success' => 'bool',
        'message' => 'string',
        'retry_after' => 'int',
        'expires_in' => 'int',
        'mobile' => 'string',
        'debug_code' => 'string',
    ])]
    public function post(): JsonResponse
    {
        try {
            $result = (new SmsLoginService())->sendLoginCode($this->mobile, $_SERVER['REMOTE_ADDR'] ?? null);
        } catch (RuntimeException $exception) {
            return JsonResponse::badRequest($exception->getMessage());
        }

        return JsonResponse::ok($result);
    }
}
