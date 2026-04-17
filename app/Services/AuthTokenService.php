<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserApiToken;

class AuthTokenService
{
    private const string DEFAULT_TOKEN_NAME = 'pc-transfer-web';
    private const int REMEMBER_TTL_SECONDS = 2592000;
    private const int SESSION_TTL_SECONDS = 86400;
    private const string DATETIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @return array<string, mixed>
     */
    public function issueForUser(User $user, string $tokenName = self::DEFAULT_TOKEN_NAME, bool $remember = true): array
    {
        $plainToken = UserApiToken::generateToken();
        $userApiToken = new UserApiToken();
        $userApiToken->user_id = (string) $user->id;
        $userApiToken->user_name = $tokenName;
        $userApiToken->token_hash = UserApiToken::hashToken($plainToken);
        $userApiToken->expires_at = $this->resolveExpiresAt($remember);
        $userApiToken->save();

        return [
            'token' => $plainToken,
            'token_type' => 'Bearer',
            'expires_at' => $userApiToken->expires_at,
            'user' => $user->toSafeArray(),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function issueForUserId(string $userId, string $tokenName = self::DEFAULT_TOKEN_NAME, bool $remember = true): ?array
    {
        $user = User::firstWhere('id', '=', $userId);
        if (!$user instanceof User) {
            return null;
        }

        return $this->issueForUser($user, $tokenName, $remember);
    }

    public function revoke(UserApiToken $userApiToken): void
    {
        $userApiToken->delete();
    }

    private function resolveExpiresAt(bool $remember): string
    {
        $ttl = $remember ? self::REMEMBER_TTL_SECONDS : self::SESSION_TTL_SECONDS;

        return date(self::DATETIME_FORMAT, time() + $ttl);
    }
}
