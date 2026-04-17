<?php

declare(strict_types=1);

namespace App\Middleware;

use Exception;
use Override;
use BaseApi\Http\Middleware;
use BaseApi\Http\Request;
use BaseApi\Http\Response;
use BaseApi\Http\JsonResponse;
use BaseApi\App;
use App\Models\UserApiToken;

/**
 * Middleware to authenticate users via user API tokens.
 * Checks for Bearer token in Authorization header and attaches user to request.
 */
class UserApiTokenAuthMiddleware implements Middleware
{
    #[Override]
    public function handle(Request $request, callable $next): Response
    {
        $authHeader = null;
        foreach ($request->headers ?? [] as $key => $value) {
            if (strcasecmp((string) $key, 'authorization') === 0) {
                $authHeader = is_array($value) ? reset($value) : $value;
                break;
            }
        }

        if (!is_string($authHeader) || strncasecmp($authHeader, 'Bearer ', 7) !== 0) {
            return JsonResponse::error('未登录或登录已失效', 401);
        }

        $token = trim(substr($authHeader, 7));

        if ($token === '' || $token === '0') {
            return JsonResponse::error('未登录或登录已失效', 401);
        }

        $tokenModel = null;
        $user = $this->validateTokenAndGetUser($token, $tokenModel);

        if ($user === null) {
            return JsonResponse::error('未登录或登录已失效', 401);
        }

        $request->user = $user;
        $request->authMethod = 'user_api_token';
        $request->apiToken = $tokenModel;

        return $next($request);
    }

    /**
     * Validate token and return user data
     */
    private function validateTokenAndGetUser(string $token, ?UserApiToken &$resolvedToken = null): ?array
    {
        try {
            $tokenModel = UserApiToken::findByToken($token);

            if (!$tokenModel instanceof UserApiToken) {
                return null;
            }

            if ($tokenModel->isExpired()) {
                return null;
            }

            $userProvider = App::userProvider();
            $user = $userProvider->byId($tokenModel->user_id);

            if ($user) {
                $tokenModel->updateLastUsed();
                $resolvedToken = $tokenModel;
            }

            return $user;
        } catch (Exception) {
            return null;
        }
    }
}
