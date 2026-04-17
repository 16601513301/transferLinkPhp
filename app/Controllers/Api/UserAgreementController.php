<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\OpenApi\Schemas\AgreementDetailSchema;
use App\OpenApi\Schemas\AgreementListItemSchema;
use App\Services\UserAgreementService;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;
use RuntimeException;

#[Tag('User Agreement')]
class UserAgreementController extends Controller
{
    public string $id = '';

    public string $type = '';

    public string $version = '';

    #[ResponseType([
        'agreement' => AgreementDetailSchema::class,
        'items' => AgreementListItemSchema::class . '[]',
    ])]
    public function get(): JsonResponse
    {
        $path = $this->normalizeRequestPath();

        if (str_ends_with($path, '/history')) {
            return $this->history();
        }

        if (str_ends_with($path, '/detail')) {
            return $this->detail();
        }

        return $this->current();
    }

    #[ResponseType(['agreement' => AgreementDetailSchema::class])]
    public function current(): JsonResponse
    {
        try {
            $agreement = (new UserAgreementService())->getCurrent($this->type);
        } catch (RuntimeException $exception) {
            return JsonResponse::badRequest($exception->getMessage());
        }

        if ($agreement === null) {
            return JsonResponse::notFound('当前没有生效中的协议版本。');
        }

        return JsonResponse::ok(['agreement' => $agreement]);
    }

    #[ResponseType(['items' => AgreementListItemSchema::class . '[]'])]
    public function history(): JsonResponse
    {
        try {
            $items = (new UserAgreementService())->getHistory($this->type);
        } catch (RuntimeException $exception) {
            return JsonResponse::badRequest($exception->getMessage());
        }

        return JsonResponse::ok(['items' => $items]);
    }

    #[ResponseType(['agreement' => AgreementDetailSchema::class])]
    public function detail(): JsonResponse
    {
        try {
            $id = $this->normalizeId($this->id);
            $agreement = (new UserAgreementService())->getDetail($id, $this->type, $this->version);
        } catch (RuntimeException $exception) {
            return JsonResponse::badRequest($exception->getMessage());
        }

        if ($agreement === null) {
            return JsonResponse::notFound('协议不存在。');
        }

        return JsonResponse::ok(['agreement' => $agreement]);
    }

    private function normalizeId(string $id): ?int
    {
        $id = trim($id);

        if ($id === '') {
            return null;
        }

        if (!ctype_digit($id)) {
            throw new RuntimeException('协议 ID 必须是正整数。');
        }

        return (int) $id;
    }

    private function normalizeRequestPath(): string
    {
        $path = (string) ($this->request?->path ?? '');

        return rtrim($path, '/');
    }
}
