<?php

namespace App\Controllers;

use BaseApi\Models\BaseModel;
use BaseApi\Http\JsonResponse;
use App\Models\UserApiToken;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;

/**
 * 用户登录令牌管理接口
 */
#[Tag('登录令牌管理')]
class UserApiTokenController extends Controller
{
    public string $user_name = '';

    public string $expires_at = '';

    public string $id = '';

    /**
     * List all user API tokens for the authenticated user
     */
    #[ResponseType(['tokens' => 'array'])]
    public function get(): JsonResponse
    {
        $user = $this->request->user;

        if (!$user) {
            return JsonResponse::unauthorized('未登录或登录已失效');
        }

        $tokens = UserApiToken::where('user_id', '=', $user['id'])
            ->orderBy('created_at', 'desc')
            ->get();

        // 响应中不返回敏感信息
        $tokenData = array_map(fn(UserApiToken $userApiToken): array => [
            'id' => $userApiToken->id,
            'user_name' => $userApiToken->user_name,
            'expires_at' => (string) ($userApiToken->expires_at ?? ''),
            'last_used_at' => (string) ($userApiToken->last_used_at ?? ''),
            'created_at' => (string) ($userApiToken->created_at ?? ''),
        ], $tokens);

        return JsonResponse::ok([
            'tokens' => $tokenData
        ]);
    }

    /**
     * Create a new user API token
     */
    #[ResponseType(['token' => 'string', 'id' => 'string', 'user_name' => 'string', 'expires_at' => 'string', 'created_at' => 'string'])]
    public function post(): JsonResponse
    {
        $user = $this->request->user;

        if (!$user) {
            return JsonResponse::unauthorized('未登录或登录已失效');
        }

        if (trim($this->user_name) === '') {
            return JsonResponse::badRequest('令牌名称不能为空');
        }

        if (mb_strlen(trim($this->user_name)) > 100) {
            return JsonResponse::badRequest('令牌名称长度不能超过 100 个字符');
        }

        // 生成令牌
        $plainToken = UserApiToken::generateToken();
        $tokenHash = UserApiToken::hashToken($plainToken);

        // 创建令牌记录
        $userApiToken = new UserApiToken();
        $userApiToken->user_id = $user['id'];
        $userApiToken->user_name = $this->user_name;
        $userApiToken->token_hash = $tokenHash;
        $userApiToken->expires_at = trim($this->expires_at) !== '' ? trim($this->expires_at) : null;
        $userApiToken->save();

        return JsonResponse::created([
            'token' => $plainToken, // 明文令牌只返回一次
            'id' => $userApiToken->id,
            'user_name' => $userApiToken->user_name,
            'expires_at' => (string) ($userApiToken->expires_at ?? ''),
            'created_at' => (string) ($userApiToken->created_at ?? ''),
        ]);
    }

    /**
     * Delete a user API token
     */
    #[ResponseType(['message' => 'string'])]
    public function delete(): JsonResponse
    {
        $user = $this->request->user;

        if (!$user) {
            return JsonResponse::unauthorized('未登录或登录已失效');
        }

        // 查询当前用户自己的令牌
        $token = UserApiToken::where('id', '=', $this->id)
            ->where('user_id', '=', $user['id'])
            ->first();

        if (!$token instanceof BaseModel) {
            return JsonResponse::notFound('令牌不存在');
        }

        $token->delete();

        return JsonResponse::ok(['message' => '令牌删除成功']);
    }
}
