<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\AuthTokenService;
use App\Models\UserApiToken;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;

#[Tag('Authentication')]
class LogoutController extends Controller
{
    #[ResponseType(['message' => 'string'])]
    public function post(): JsonResponse
    {
        if ($this->request->apiToken instanceof UserApiToken) {
            (new AuthTokenService())->revoke($this->request->apiToken);
        }

        return JsonResponse::ok(['message' => '退出登录成功']);
    }
}
