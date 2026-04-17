<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use BaseApi\Controllers\Controller;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;
use BaseApi\Http\JsonResponse;

#[Tag('Transfer')]
class TransferConvertController extends Controller
{
    public string $content = '';

    #[ResponseType(['type' => 'string', 'lines' => 'string[]', 'result_url' => 'string'])]
    public function post(): JsonResponse
    {
        $lines = $this->normalizeLines($this->content);

        return JsonResponse::ok([
            'type' => 'convert',
            'lines' => $lines,
            'result_url' => 'https://demo.example.com/transfer/' . date('YmdHis'),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function normalizeLines(string $content): array
    {
        $lines = array_values(array_filter(array_map(
            static fn(string $line): string => trim($line),
            preg_split('/\r?\n/', $content) ?: []
        )));

        return $lines !== [] ? $lines : ['请输入要转链的内容'];
    }
}
