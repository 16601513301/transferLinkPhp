<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\QrLoginService;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;
use RuntimeException;

#[Tag('QR Login')]
class QrLoginConfirmController extends Controller
{
    public string $ticket_id = '';

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
    ])]
    public function post(): JsonResponse
    {
        if (trim($this->ticket_id) === '') {
            return JsonResponse::badRequest('票据编号不能为空');
        }

        if (!is_array($this->request->user) || $this->request->user === []) {
            return JsonResponse::unauthorized('未登录或登录已失效');
        }

        try {
            $ticket = (new QrLoginService())->confirmLogin($this->ticket_id, $this->request->user);
        } catch (RuntimeException $exception) {
            return JsonResponse::badRequest($exception->getMessage());
        }

        return JsonResponse::ok($ticket);
    }
}
