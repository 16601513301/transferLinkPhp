<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Support\OpenApiSpecLocalizer;
use BaseApi\Controllers\Controller;
use BaseApi\Http\JsonResponse;
use BaseApi\Http\Response;
use BaseApi\OpenApi\Builders\IRBuilder;
use BaseApi\OpenApi\Emitters\OpenAPIEmitter;
use Exception;

class BaseController extends Controller
{
    public function get(): Response
    {
        try {
            $builder = new IRBuilder();
            $emitter = new OpenAPIEmitter();
            $spec = $emitter->emit($builder->build());
            $spec = $this->localizeSpec($spec);

            return new JsonResponse($spec);
        } catch (Exception $exception) {
            return JsonResponse::error('生成 OpenAPI 文档失败');
        }
    }

    /**
     * @param array<string, mixed> $spec
     * @return array<string, mixed>
     */
    private function localizeSpec(array $spec): array
    {
        return (new OpenApiSpecLocalizer())->localize($spec);
    }
}
