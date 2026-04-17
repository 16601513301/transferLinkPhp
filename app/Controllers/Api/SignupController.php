<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\User;
use App\Services\AuthTokenService;
use App\Services\EmailService;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;

#[Tag('Authentication')]
class SignupController extends Controller
{
    public string $user_name = '';

    public string $email = '';

    public string $password = '';

    public string $mobile = '';

    public bool $remember = true;

    public function __construct(
        private readonly EmailService $emailService,
    ) {
    }

    #[ResponseType(['token' => 'string', 'token_type' => 'string', 'expires_at' => 'string', 'user' => 'object'])]
    public function post(): JsonResponse
    {
        if (trim($this->user_name) === '') {
            return JsonResponse::badRequest('请输入用户名');
        }

        if (trim($this->email) === '') {
            return JsonResponse::badRequest('请输入邮箱地址');
        }

        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            return JsonResponse::badRequest('请输入正确的邮箱地址');
        }

        if (trim($this->password) === '') {
            return JsonResponse::badRequest('请输入登录密码');
        }

        if (mb_strlen($this->password) < 8) {
            return JsonResponse::badRequest('密码长度不能少于 8 位');
        }

        $existingUser = User::firstWhere('email', '=', $this->email);
        if ($existingUser instanceof User) {
            return JsonResponse::error('该邮箱已注册，请直接登录', 409);
        }

        if (trim($this->mobile) !== '') {
            if (!preg_match('/^1\d{10}$/', trim($this->mobile))) {
                return JsonResponse::badRequest('请输入正确的手机号');
            }

            $mobileExists = User::firstWhere('mobile', '=', trim($this->mobile));
            if ($mobileExists instanceof User) {
                return JsonResponse::error('该手机号已注册，请直接登录', 409);
            }
        }

        $user = new User();
        $user->user_name = $this->user_name;
        $user->email = $this->email;
        $user->mobile = trim($this->mobile) !== '' ? trim($this->mobile) : null;
        $user->password = password_hash($this->password, PASSWORD_DEFAULT);
        $user->role = 'user';
        $user->active = true;

        if (!$user->save()) {
            return JsonResponse::error('创建用户失败', 500);
        }

        $this->emailService->sendWelcome($user->email, $user->user_name);

        return JsonResponse::ok(
            (new AuthTokenService())->issueForUser($user, 'signup-login', $this->remember)
        );
    }
}
