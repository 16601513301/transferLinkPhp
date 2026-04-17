<?php

namespace App\Controllers\Api;

use App\Models\User;
use App\Services\EmailService;
use BaseApi\Controllers\Controller;
use BaseApi\Http\JsonResponse;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;

#[Tag('Authentication')]
class AuthController extends Controller
{
    public string $user_name = '';

    public string $email = '';

    public string $account = '';

    public string $password = '';

    public function __construct(
        private readonly EmailService $emailService,
    ) {
    }

    #[ResponseType(['user' => 'array'])]
    public function login(): JsonResponse
    {
        $identity = trim($this->account !== '' ? $this->account : $this->email);

        if ($identity === '' || trim($this->password) === '') {
            return JsonResponse::badRequest('请输入账号或邮箱，并填写密码');
        }

        $user = User::where('email', '=', $identity)
            ->orWhere('mobile', '=', $identity)
            ->first();

        if (!$user instanceof User || !$user->checkPassword($this->password)) {
            return JsonResponse::error('账号或密码错误', 401);
        }

        $this->request->session['user_id'] = $user->id ?? null;
        session_regenerate_id(true);

        return JsonResponse::ok($user->jsonSerialize());
    }

    #[ResponseType(User::class)]
    public function signup(): JsonResponse
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

        $user = new User();
        $user->user_name = $this->user_name;
        $user->email = $this->email;
        $user->password = password_hash($this->password, PASSWORD_DEFAULT);
        $user->role = 'user';
        $user->active = true;

        if (!$user->save()) {
            return JsonResponse::error('创建用户失败', 500);
        }

        $this->emailService->sendWelcome($user->email, $user->user_name);

        $this->request->session['user_id'] = $user->id ?? null;
        session_regenerate_id(true);

        return JsonResponse::ok($user->jsonSerialize());
    }

    #[ResponseType(['message' => 'string'])]
    public function logout(): JsonResponse
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        } else {
            $_SESSION = [];
        }

        return JsonResponse::ok(['message' => '退出登录成功']);
    }
}
