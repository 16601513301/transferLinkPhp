<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\User;
use App\Services\AuthTokenService;
use App\Services\SmsLoginService;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;
use RuntimeException;

#[Tag('Authentication')]
class SmsLoginController extends Controller
{
    public string $mobile = '';

    public string $sms_code = '';

    public bool $remember = true;

    #[ResponseType(['token' => 'string', 'token_type' => 'string', 'expires_at' => 'string', 'user' => 'object'])]
    public function post(): JsonResponse
    {
        try {
            $user = (new SmsLoginService())->loginWithCode($this->mobile, $this->sms_code);
        } catch (RuntimeException $exception) {
            return JsonResponse::badRequest($exception->getMessage());
        }

        if (!$user instanceof User) {
            return JsonResponse::error('用户不存在', 404);
        }

        return JsonResponse::ok(
            (new AuthTokenService())->issueForUser($user, 'sms-login', $this->remember)
        );
    }
}
