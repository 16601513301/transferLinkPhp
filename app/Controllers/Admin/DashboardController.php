<?php

namespace App\Controllers\Admin;

use App\Models\User;
use BaseApi\Controllers\Controller;
use BaseApi\Http\JsonResponse;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;

#[Tag('Admin')]
class DashboardController extends Controller
{
    #[ResponseType(['cards' => 'array'])]
    public function get(): JsonResponse
    {
        $users = User::all();
        $userCount = is_array($users) ? count($users) : 0;

        return JsonResponse::ok([
            'cards' => [
                ['label' => '用户数', 'value' => $userCount],
                ['label' => '今日转链', 'value' => 0],
                ['label' => '短链生成', 'value' => 0],
            ],
        ]);
    }
}
