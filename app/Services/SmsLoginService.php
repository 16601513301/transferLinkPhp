<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SmsLoginCode;
use App\Models\User;
use BaseApi\App;
use RuntimeException;

class SmsLoginService
{
    private const string PURPOSE_LOGIN = 'login';
    private const int CODE_LENGTH = 6;
    private const int EXPIRES_IN_SECONDS = 300;
    private const int RESEND_COOLDOWN_SECONDS = 60;
    private const int MAX_ATTEMPTS = 5;
    private const string DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @return array<string, mixed>
     */
    public function sendLoginCode(string $mobile, ?string $requestIp = null): array
    {
        $mobile = $this->normalizeMobile($mobile);

        $user = User::firstWhere('mobile', '=', $mobile);
        if (!$user instanceof User) {
            throw new RuntimeException('用户不存在，请先去星返注册用户');
        }

        $latestCode = $this->latestActiveCode($mobile);
        if ($latestCode instanceof SmsLoginCode && !$this->isExpired($latestCode->expires_at)) {
            $sentAt = strtotime((string) $latestCode->created_at);
            if ($sentAt !== false) {
                $retryAfter = self::RESEND_COOLDOWN_SECONDS - (time() - $sentAt);
                if ($retryAfter > 0) {
                    throw new RuntimeException(sprintf('请在 %d 秒后重新获取验证码', $retryAfter));
                }
            }
        }

        $this->invalidateActiveCodes($mobile);

        $code = $this->generateCode();
        $record = new SmsLoginCode();
        $record->mobile = $mobile;
        $record->purpose = self::PURPOSE_LOGIN;
        $record->code_hash = $this->hashCode($code);
        $record->expires_at = $this->expiresAt();
        $record->request_ip = $requestIp;
        $record->attempts = 0;
        $record->save();

        $response = [
            'success' => true,
            'message' => '验证码已发送，请注意查收',
            'retry_after' => self::RESEND_COOLDOWN_SECONDS,
            'expires_in' => self::EXPIRES_IN_SECONDS,
            'mobile' => $mobile,
            'debug_code' => '',
        ];

        if ((string) App::config('app.env', 'local') === 'local') {
            $response['debug_code'] = $code;
        }

        return $response;
    }

    public function loginWithCode(string $mobile, string $code): User
    {
        $mobile = $this->normalizeMobile($mobile);
        $code = $this->normalizeCode($code);

        $user = User::firstWhere('mobile', '=', $mobile);
        if (!$user instanceof User) {
            throw new RuntimeException('用户不存在，请先去星返注册用户');
        }

        $record = $this->latestActiveCode($mobile);
        if (!$record instanceof SmsLoginCode) {
            throw new RuntimeException('验证码不存在，请先获取验证码');
        }

        if ($this->isExpired($record->expires_at)) {
            $record->used_at = $this->now();
            $record->save();
            throw new RuntimeException('验证码已过期，请重新获取');
        }

        if ($record->attempts >= self::MAX_ATTEMPTS) {
            $record->used_at = $this->now();
            $record->save();
            throw new RuntimeException('验证码错误次数过多，请重新获取');
        }

        if (!hash_equals((string) $record->code_hash, $this->hashCode($code))) {
            $record->attempts += 1;
            if ($record->attempts >= self::MAX_ATTEMPTS) {
                $record->used_at = $this->now();
            }
            $record->save();

            throw new RuntimeException('验证码错误');
        }

        $record->used_at = $this->now();
        $record->attempts += 1;
        $record->save();

        $this->invalidateActiveCodes($mobile);

        return $user;
    }

    private function normalizeMobile(string $mobile): string
    {
        $mobile = trim($mobile);
        if (!preg_match('/^1\d{10}$/', $mobile)) {
            throw new RuntimeException('请输入正确的手机号');
        }

        return $mobile;
    }

    private function normalizeCode(string $code): string
    {
        $code = trim($code);
        if (!preg_match('/^\d{6}$/', $code)) {
            throw new RuntimeException('请输入 6 位短信验证码');
        }

        return $code;
    }

    private function generateCode(): string
    {
        return str_pad((string) random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    private function hashCode(string $code): string
    {
        return hash('sha256', $code);
    }

    private function latestActiveCode(string $mobile): ?SmsLoginCode
    {
        return SmsLoginCode::where('mobile', '=', $mobile)
            ->where('purpose', '=', self::PURPOSE_LOGIN)
            ->whereNull('used_at')
            ->latest('created_at')
            ->first();
    }

    private function invalidateActiveCodes(string $mobile): void
    {
        $records = SmsLoginCode::where('mobile', '=', $mobile)
            ->where('purpose', '=', self::PURPOSE_LOGIN)
            ->whereNull('used_at')
            ->get();

        foreach ($records as $record) {
            $record->used_at = $this->now();
            $record->save();
        }
    }

    private function isExpired(?string $expiresAt): bool
    {
        if ($expiresAt === null || trim($expiresAt) === '') {
            return true;
        }

        $timestamp = strtotime($expiresAt);
        if ($timestamp === false) {
            return true;
        }

        return $timestamp <= time();
    }

    private function now(): string
    {
        return date(self::DATETIME_FORMAT);
    }

    private function expiresAt(): string
    {
        return date(self::DATETIME_FORMAT, time() + self::EXPIRES_IN_SECONDS);
    }
}
