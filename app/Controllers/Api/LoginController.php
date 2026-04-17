<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\User;
use App\Services\AuthTokenService;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;

#[Tag('Authentication')]
class LoginController extends Controller
{
    public string $email = '';

    public string $account = '';

    public string $password = '';

    public bool $remember = true;

    #[ResponseType(['token' => 'string', 'token_type' => 'string', 'expires_at' => 'string', 'user' => 'object'])]
    public function post(): JsonResponse
    {
        $identity = trim($this->account !== '' ? $this->account : $this->email);

        if ($identity === '' || trim($this->password) === '') {
            return JsonResponse::badRequest('请输入账号或邮箱，并填写密码');
        }

        $user = User::where('email', '=', $identity)
            ->orWhere('mobile', '=', $identity)
            ->first();

        if (!$user instanceof User) {
            return JsonResponse::error('账号不存在，请先去星返APP登录', 404);
        }

        if (!$user->checkPassword($this->password)) {
            return JsonResponse::error('密码错误', 401);
        }

        return JsonResponse::ok(
            (new AuthTokenService())->issueForUser($user, 'password-login', $this->remember)
        );
    }
}
