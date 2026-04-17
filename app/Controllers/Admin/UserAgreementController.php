<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\OpenApi\Schemas\AgreementDetailSchema;
use App\OpenApi\Schemas\AgreementListItemSchema;
use App\Services\UserAgreementService;
use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;
use RuntimeException;

#[Tag('Admin User Agreement')]
class UserAgreementController extends Controller
{
    public string $id = '';

    public string $type = '';

    public string $version = '';

    public string $title = '';

    public string $content = '';

    public string $summary = '';

    public string $status = '';

    public string $is_required = '1';

    public string $effective_time = '';

    public string $published_by = '';

    public string $published_at = '';

    #[ResponseType([
        'items' => AgreementListItemSchema::class . '[]',
        'agreement' => AgreementDetailSchema::class,
    ])]
    public function get(): JsonResponse
    {
        $path = $this->normalizeRequestPath();

        if (str_ends_with($path, '/detail')) {
            return $this->detail();
        }

        return $this->index();
    }

    #[ResponseType(['agreement' => AgreementDetailSchema::class])]
    public function post(): JsonResponse
    {
        $path = $this->normalizeRequestPath();

        if (str_ends_with($path, '/activate')) {
            return $this->activate();
        }

        return $this->save();
    }

    #[ResponseType(['items' => AgreementListItemSchema::class . '[]'])]
    public function index(): JsonResponse
    {
        $status = $this->normalizeOptionalFlag($this->status);

        try {
            $items = (new UserAgreementService())->getAdminList(
                trim($this->type) !== '' ? $this->type : null,
                $status
            );
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

    #[ResponseType(['agreement' => AgreementDetailSchema::class])]
    public function save(): JsonResponse
    {
        try {
            $operator = $this->resolveOperator();
            $id = $this->normalizeOptionalId($this->id);
            $agreement = (new UserAgreementService())->save([
                'type' => $this->type,
                'version' => $this->version,
                'title' => $this->title,
                'content' => $this->content,
                'summary' => $this->summary,
                'status' => trim($this->status) !== '' ? $this->status : '1',
                'is_required' => $this->is_required,
                'effective_time' => $this->effective_time,
                'published_by' => trim($this->published_by) !== '' ? $this->published_by : $operator,
                'published_at' => $this->published_at,
            ], $id);
        } catch (RuntimeException $exception) {
            return JsonResponse::badRequest($exception->getMessage());
        }

        return $id === null
            ? JsonResponse::created(['agreement' => $agreement])
            : JsonResponse::ok(['agreement' => $agreement]);
    }

    #[ResponseType(['agreement' => AgreementDetailSchema::class])]
    public function activate(): JsonResponse
    {
        try {
            $id = $this->normalizeId($this->id);
            $agreement = (new UserAgreementService())->activate($id, $this->resolveOperator());
        } catch (RuntimeException $exception) {
            return JsonResponse::badRequest($exception->getMessage());
        }

        return JsonResponse::ok(['agreement' => $agreement]);
    }

    private function resolveOperator(): string
    {
        if (is_array($this->request?->user ?? null)) {
            $user = $this->request->user;

            foreach (['user_name', 'email', 'mobile', 'id'] as $field) {
                if (isset($user[$field]) && trim((string) $user[$field]) !== '') {
                    return trim((string) $user[$field]);
                }
            }
        }

        return 'system';
    }

    private function normalizeId(string $id): int
    {
        $id = trim($id);

        if ($id === '' || !ctype_digit($id)) {
            throw new RuntimeException('协议 ID 必须是正整数。');
        }

        return (int) $id;
    }

    private function normalizeOptionalId(string $id): ?int
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

    private function normalizeOptionalFlag(string $flag): ?int
    {
        $flag = trim($flag);

        if ($flag === '') {
            return null;
        }

        if ($flag === '1') {
            return 1;
        }

        if ($flag === '0') {
            return 0;
        }

        throw new RuntimeException('状态筛选仅支持 0 或 1。');
    }

    private function normalizeRequestPath(): string
    {
        $path = (string) ($this->request?->path ?? '');

        return rtrim($path, '/');
    }
}
