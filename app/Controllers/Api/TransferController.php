<?php

namespace App\Controllers\Api;

use BaseApi\Controllers\Controller;
use BaseApi\Http\JsonResponse;
use BaseApi\Http\Attributes\ResponseType;
use BaseApi\Http\Attributes\Tag;

#[Tag('Transfer')]
class TransferController extends Controller
{
    public string $content = '';

    #[ResponseType(['type' => 'string', 'lines' => 'string[]', 'result_url' => 'string'])]
    public function convert(): JsonResponse
    {
        $lines = $this->normalizeLines($this->content);

        return JsonResponse::ok([
            'type' => 'convert',
            'lines' => $lines,
            'result_url' => 'https://demo.example.com/transfer/' . date('YmdHis'),
        ]);
    }

    #[ResponseType(['type' => 'string', 'lines' => 'string[]', 'result_url' => 'string'])]
    public function shortLink(): JsonResponse
    {
        $lines = $this->normalizeLines($this->content);

        return JsonResponse::ok([
            'type' => 'short-link',
            'lines' => $lines,
            'result_url' => 'https://demo.example.com/s/' . substr(md5((string) microtime(true)), 0, 8),
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
