<?php

namespace App\Controllers\Api;

use BaseApi\App;
use BaseApi\Controllers\Controller;
use BaseApi\Http\JsonResponse;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;

#[Tag('User')]
class UserController extends Controller
{
    #[ResponseType(['user' => 'object'])]
    public function profile(): JsonResponse
    {
        if (is_array($this->request->user) && $this->request->user !== []) {
            return JsonResponse::ok(['user' => $this->request->user]);
        }

        $userId = $this->request->session['user_id'] ?? null;
        if (!$userId) {
            return JsonResponse::error('未登录或登录已失效', 401);
        } 

        $user = App::userProvider()->byId($userId);
        if (!$user) {
            return JsonResponse::error('用户不存在', 404);
        }

        return JsonResponse::ok(['user' => $user]);
    }
}
