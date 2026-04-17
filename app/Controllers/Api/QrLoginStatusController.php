<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\AuthTokenService;
use App\Services\QrLoginService;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;
use RuntimeException;
use stdClass;

#[Tag('QR Login')]
class QrLoginStatusController extends Controller
{
    public string $ticket_id = '';

    public string $poll_token = '';

    public bool $remember = true;

    #[ResponseType([
        'scene' => 'string',
        'ticket_id' => 'string',
        'status' => 'string',
        'qr_content' => 'string',
        'expires_in' => 'int',
        'created_at' => 'string',
        'expires_at' => 'string',
        'scanned_at' => 'string',
        'confirmed_at' => 'string',
        'logged_in_at' => 'string',
        'user' => 'object',
        'auth' => 'object',
    ])]
    public function post(): JsonResponse
    {
        if (trim($this->ticket_id) === '') {
            return JsonResponse::badRequest('票据编号不能为空');
        }

        if (trim($this->poll_token) === '') {
            return JsonResponse::badRequest('轮询令牌不能为空');
        }

        $service = new QrLoginService();
        $response = null;

        try {
            $ticket = $service->getStatusForPc($this->ticket_id, $this->poll_token);
            $response = array_merge($ticket, ['auth' => new stdClass()]);

            if (($ticket['status'] ?? '') === 'confirmed' && is_array($ticket['user'] ?? null)) {
                $userId = $ticket['user']['id'] ?? '';
                if ((string) $userId !== '') {
                    $auth = (new AuthTokenService())->issueForUserId(
                        (string) $userId,
                        'qr-login',
                        $this->remember
                    );

                    if ($auth === null) {
                        return JsonResponse::error('用户不存在', 404);
                    }

                    $ticket = $service->markPcLoggedIn($this->ticket_id, $this->poll_token);
                    $response = array_merge($ticket, ['auth' => $auth]);
                }
            }
        } catch (RuntimeException $exception) {
            return JsonResponse::badRequest($exception->getMessage());
        }

        return JsonResponse::ok($response ?? ['auth' => new stdClass()]);
    }
}
