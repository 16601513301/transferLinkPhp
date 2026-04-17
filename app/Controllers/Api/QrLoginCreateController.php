<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Services\QrLoginService;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;

#[Tag('QR Login')]
class QrLoginCreateController extends Controller
{
    #[ResponseType([
        'scene' => 'string',
        'ticket_id' => 'string',
        'poll_token' => 'string',
        'status' => 'string',
        'qr_content' => 'string',
        'qr_image_data_url' => 'string',
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
        $ticket = (new QrLoginService())->createTicket();

        return JsonResponse::ok($ticket);
    }
}
