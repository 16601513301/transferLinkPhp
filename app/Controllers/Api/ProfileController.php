<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;

#[Tag('User')]
class ProfileController extends Controller
{
    #[ResponseType(['user' => 'object'])]
    public function get(): JsonResponse
    {
        if (is_array($this->request->user) && $this->request->user !== []) {
            return JsonResponse::ok(['user' => $this->request->user]);
        }

        return JsonResponse::error('未登录或登录已失效', 401);
    }
}
