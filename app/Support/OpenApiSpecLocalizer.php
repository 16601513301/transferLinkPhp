<?php

declare(strict_types=1);

namespace App\Support;

final class OpenApiSpecLocalizer
{
    /**
     * @param array<string, mixed> $spec
     * @return array<string, mixed>
     */
    public function localize(array $spec): array
    {
        $spec['info'] = [
            'title' => '星返PC端转链接口文档',
            'version' => (string) ($spec['info']['version'] ?? '1.0.0'),
            'description' => '星返PC端转链系统接口文档（中文）',
        ];

        $spec['tags'] = [
            ['name' => '健康检查', 'description' => '服务健康检查相关接口'],
            ['name' => '认证', 'description' => '注册、登录、退出和短信验证码相关接口'],
            ['name' => '二维码登录', 'description' => '星返APP扫码登录相关接口'],
            ['name' => '协议', 'description' => '用户协议、隐私政策和 Cookie 政策相关接口'],
            ['name' => '管理后台', 'description' => '后台仪表盘与协议管理相关接口'],
            ['name' => '用户', 'description' => '当前登录用户信息相关接口'],
            ['name' => '转链', 'description' => '转链和短链生成相关接口'],
            ['name' => '系统', 'description' => '系统文档和调试辅助接口'],
        ];

        if (isset($spec['paths']) && is_array($spec['paths'])) {
            foreach ($spec['paths'] as $path => &$pathItem) {
                if (!is_array($pathItem)) {
                    continue;
                }

                foreach ($pathItem as $httpMethod => &$operation) {
                    if (!is_array($operation)) {
                        continue;
                    }

                    $summary = $this->resolveOperationSummary((string) $path, strtoupper((string) $httpMethod));
                    if ($summary !== '') {
                        $operation['summary'] = $summary;
                        $operation['description'] = $summary;
                    }

                    if (isset($operation['tags']) && is_array($operation['tags'])) {
                        $operation['tags'] = array_values(array_map(
                            fn (mixed $tag): string => $this->translateTag((string) $tag),
                            $operation['tags']
                        ));
                    }

                    if (isset($operation['responses']) && is_array($operation['responses'])) {
                        foreach ($operation['responses'] as &$response) {
                            if (!is_array($response) || !isset($response['description'])) {
                                continue;
                            }

                            $response['description'] = $this->translateResponseDescription(
                                (string) $response['description']
                            );
                        }
                        unset($response);
                    }
                }
                unset($operation);
            }
            unset($pathItem);
        }

        if (isset($spec['components']['responses']) && is_array($spec['components']['responses'])) {
            foreach ($spec['components']['responses'] as &$response) {
                if (!is_array($response) || !isset($response['description'])) {
                    continue;
                }

                $response['description'] = $this->translateResponseDescription((string) $response['description']);
            }
            unset($response);
        }

        $this->applyRequestExamples($spec);
        $this->applyResponseExamples($spec);

        return $spec;
    }

    /**
     * @param array<string, mixed> $spec
     */
    private function applyRequestExamples(array &$spec): void
    {
        $this->ensureJsonRequestBody($spec, '/', 'post', [], false);
        $this->ensureJsonRequestBody($spec, '/benchmark', 'post', [], false);
        $this->ensureJsonRequestBody($spec, '/me', 'post', [], false);
        $this->ensureJsonRequestBody($spec, '/openapi.json', 'post', [], false);
        $this->ensureJsonRequestBody($spec, '/auth/qr-login', 'post', [], false);
        $this->ensureJsonRequestBody($spec, '/auth/logout', 'post', [], false);
        $this->ensureJsonRequestBody($spec, '/admin/dashboard', 'post', [], false);

        $this->setRequestExample($spec, '/', 'post', (object) []);
        $this->setRequestExample($spec, '/benchmark', 'post', (object) []);
        $this->setRequestExample($spec, '/me', 'post', (object) []);
        $this->setRequestExample($spec, '/openapi.json', 'post', (object) []);
        $this->setRequestExample($spec, '/auth/qr-login', 'post', (object) []);
        $this->setRequestExample($spec, '/auth/logout', 'post', (object) []);
        $this->setRequestExample($spec, '/admin/dashboard', 'post', (object) []);

        $this->setRequestExample($spec, '/health', 'post', [
            'db' => '1',
            'cache' => '1',
        ]);

        $this->setRequestExample($spec, '/stream', 'post', [
            'prompt' => '请返回一段测试流式响应的文本',
        ]);

        $this->setRequestExample($spec, '/auth/signup', 'post', [
            'user_name' => '双若汐',
            'email' => 'fselj5_klv@vip.qq.com',
            'password' => 'bmR5pJ3N1oam9UP',
            'mobile' => '13868478533',
            'remember' => true,
        ]);

        $this->setRequestExample($spec, '/auth/login', 'post', [
            'account' => '13868478533',
            'password' => 'bmR5pJ3N1oam9UP',
            'remember' => true,
        ]);

        $this->setRequestExample($spec, '/auth/sms-code', 'post', [
            'mobile' => '13868478533',
        ]);

        $this->setRequestExample($spec, '/auth/sms-login', 'post', [
            'mobile' => '13868478533',
            'sms_code' => '123456',
            'remember' => true,
        ]);

        $this->setRequestExample($spec, '/auth/qr-login/status', 'post', [
            'ticket_id' => 'ca31ee6b0b32511d47e87b1fd0b0392c',
            'poll_token' => '8c9ac53fc5c67d1ddf16d1f4eb79f13d',
            'remember' => true,
        ]);

        $this->setRequestExample($spec, '/auth/qr-login/scan', 'post', [
            'ticket_id' => 'ca31ee6b0b32511d47e87b1fd0b0392c',
        ]);

        $this->setRequestExample($spec, '/auth/qr-login/confirm', 'post', [
            'ticket_id' => 'ca31ee6b0b32511d47e87b1fd0b0392c',
        ]);

        $this->setRequestExample($spec, '/agreements/current', 'post', [
            'type' => 'user',
        ]);

        $this->setRequestExample($spec, '/user-agreements/current', 'post', [
            'type' => 'user',
        ]);

        $this->setRequestExample($spec, '/agreements/history', 'post', [
            'type' => 'privacy',
        ]);

        $this->setRequestExample($spec, '/user-agreements/history', 'post', [
            'type' => 'privacy',
        ]);

        $this->setRequestExample($spec, '/agreements/detail', 'post', [
            'id' => '1',
        ]);

        $this->setRequestExample($spec, '/user-agreements/detail', 'post', [
            'id' => '1',
        ]);

        $this->setRequestExample($spec, '/transfer/convert', 'post', [
            'content' => "https://item.taobao.com/item.htm?id=1234567890\nhttps://detail.tmall.com/item.htm?id=1234567890",
        ]);

        $this->setRequestExample($spec, '/transfer/short-link', 'post', [
            'content' => "https://item.taobao.com/item.htm?id=1234567890\nhttps://detail.tmall.com/item.htm?id=1234567890",
        ]);

        $this->setRequestExample($spec, '/admin/agreements', 'post', [
            'type' => 'user',
            'status' => '1',
        ]);

        $this->setRequestExample($spec, '/admin/user-agreements', 'post', [
            'type' => 'user',
            'status' => '1',
        ]);

        $this->setRequestExample($spec, '/admin/agreements/detail', 'post', [
            'id' => '1',
        ]);

        $this->setRequestExample($spec, '/admin/user-agreements/detail', 'post', [
            'id' => '1',
        ]);

        $this->setRequestExample($spec, '/admin/agreements/save', 'post', [
            'type' => 'user',
            'version' => '1.0.1',
            'title' => '星返用户协议',
            'content' => '这里是后台保存的协议正文内容。',
            'summary' => '后台保存的协议摘要',
            'status' => '1',
            'is_required' => '1',
            'effective_time' => '2026-04-17 00:00:00',
            'published_by' => 'system',
            'published_at' => '2026-04-17 00:00:00',
        ]);

        $this->setRequestExample($spec, '/admin/user-agreements/save', 'post', [
            'type' => 'user',
            'version' => '1.0.1',
            'title' => '星返用户协议',
            'content' => '这里是后台保存的协议正文内容。',
            'summary' => '后台保存的协议摘要',
            'status' => '1',
            'is_required' => '1',
            'effective_time' => '2026-04-17 00:00:00',
            'published_by' => 'system',
            'published_at' => '2026-04-17 00:00:00',
        ]);

        $this->setRequestExample($spec, '/admin/agreements/activate', 'post', [
            'id' => '1',
        ]);

        $this->setRequestExample($spec, '/admin/user-agreements/activate', 'post', [
            'id' => '1',
        ]);
    }

    /**
     * @param array<string, mixed> $spec
     */
    private function applyResponseExamples(array &$spec): void
    {
        $user = [
            'id' => 1,
            'user_name' => '双若汐',
            'email' => 'fselj5_klv@vip.qq.com',
            'mobile' => '13868478533',
            'role' => 'user',
            'active' => true,
            'created_at' => '2026-04-17 12:00:00',
            'updated_at' => '2026-04-17 12:00:00',
        ];

        $auth = [
            'token' => 'xf_2f6d7d3647d04d3792d41275d1cda8d9',
            'token_type' => 'Bearer',
            'expires_at' => '2026-05-17 12:00:00',
            'user' => $user,
        ];

        $this->setSuccessResponseExample($spec, '/auth/signup', 'post', ['data' => $auth]);
        $this->setSuccessResponseExample($spec, '/auth/login', 'post', ['data' => $auth]);
        $this->setSuccessResponseExample($spec, '/auth/sms-login', 'post', ['data' => $auth]);

        $this->setSuccessResponseExample($spec, '/auth/sms-code', 'post', [
            'data' => [
                'success' => true,
                'message' => '验证码已发送，请注意查收',
                'retry_after' => 60,
                'expires_in' => 300,
                'mobile' => '13868478533',
                'debug_code' => '123456',
            ],
        ]);

        $this->setSuccessResponseExample($spec, '/auth/qr-login', 'post', [
            'data' => [
                'scene' => 'xingfan_pc_transfer_login',
                'ticket_id' => 'ca31ee6b0b32511d47e87b1fd0b0392c',
                'poll_token' => '8c9ac53fc5c67d1ddf16d1f4eb79f13d',
                'status' => 'pending',
                'qr_content' => 'xingfanapp://pc-transfer-login?scene=xingfan_pc_transfer_login&ticket_id=ca31ee6b0b32511d47e87b1fd0b0392c',
                'qr_image_data_url' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...',
                'expires_in' => 180,
                'created_at' => '2026-04-17 12:00:00',
                'expires_at' => '2026-04-17 12:03:00',
                'scanned_at' => '',
                'confirmed_at' => '',
                'logged_in_at' => '',
                'user' => (object) [],
            ],
        ]);

        $this->setSuccessResponseExample($spec, '/auth/qr-login/status', 'post', [
            'data' => [
                'scene' => 'xingfan_pc_transfer_login',
                'ticket_id' => 'ca31ee6b0b32511d47e87b1fd0b0392c',
                'status' => 'confirmed',
                'qr_content' => 'xingfanapp://pc-transfer-login?scene=xingfan_pc_transfer_login&ticket_id=ca31ee6b0b32511d47e87b1fd0b0392c',
                'expires_in' => 108,
                'created_at' => '2026-04-17 12:00:00',
                'expires_at' => '2026-04-17 12:03:00',
                'scanned_at' => '2026-04-17 12:01:02',
                'confirmed_at' => '2026-04-17 12:01:08',
                'logged_in_at' => '2026-04-17 12:01:10',
                'user' => [
                    'id' => '1',
                    'user_name' => '双若汐',
                    'email' => 'fselj5_klv@vip.qq.com',
                    'mobile' => '13868478533',
                    'role' => 'user',
                ],
                'auth' => $auth,
            ],
        ]);

        $this->setSuccessResponseExample($spec, '/me', 'post', [
            'data' => [
                'user' => $user,
            ],
        ]);

        $this->setSuccessResponseExample($spec, '/', 'post', [
            'data' => [
                'name' => '星返PC端转链系统',
                'env' => 'local',
                'status' => 'running',
                'message' => 'API 服务已启动',
                'links' => [
                    'health' => '/health',
                    'benchmark' => '/benchmark',
                    'openapi' => '/openapi.json',
                ],
            ],
        ]);

        $this->setSuccessResponseExample($spec, '/health', 'post', [
            'data' => [
                'ok' => true,
                'db' => true,
                'cache' => [
                    'working' => true,
                    'driver' => 'file',
                ],
            ],
        ]);

        $this->setSuccessResponseExample($spec, '/benchmark', 'post', [
            'data' => [
                'message' => '你好，世界！',
                'timestamp' => '2026-04-17 12:00:00',
            ],
        ]);

        $agreement = [
            'id' => 1,
            'type' => 'user',
            'type_name' => '用户协议',
            'version' => '1.0.0',
            'title' => '星返用户协议',
            'content' => '这里是协议正文内容。',
            'summary' => '平台用户使用协议说明',
            'status' => 1,
            'status_text' => '启用',
            'is_required' => 1,
            'effective_time' => '2026-04-17 00:00:00',
            'published_by' => 'system',
            'published_at' => '2026-04-17 00:00:00',
            'created_at' => '2026-04-17 00:00:00',
            'updated_at' => '2026-04-17 00:00:00',
        ];

        $agreementItem = [
            'id' => 1,
            'type' => 'privacy',
            'type_name' => '隐私政策',
            'version' => '1.0.0',
            'title' => '星返隐私政策',
            'summary' => '隐私收集和使用说明',
            'status' => 1,
            'status_text' => '启用',
            'is_required' => 1,
            'effective_time' => '2026-04-17 00:00:00',
            'published_by' => 'system',
            'published_at' => '2026-04-17 00:00:00',
            'created_at' => '2026-04-17 00:00:00',
            'updated_at' => '2026-04-17 00:00:00',
        ];

        $this->setSuccessResponseExample($spec, '/agreements/current', 'post', [
            'data' => ['agreement' => $agreement],
        ]);
        $this->setSuccessResponseExample($spec, '/user-agreements/current', 'post', [
            'data' => ['agreement' => $agreement],
        ]);
        $this->setSuccessResponseExample($spec, '/agreements/detail', 'post', [
            'data' => ['agreement' => $agreement],
        ]);
        $this->setSuccessResponseExample($spec, '/user-agreements/detail', 'post', [
            'data' => ['agreement' => $agreement],
        ]);
        $this->setSuccessResponseExample($spec, '/agreements/history', 'post', [
            'data' => ['items' => [$agreementItem]],
        ]);
        $this->setSuccessResponseExample($spec, '/user-agreements/history', 'post', [
            'data' => ['items' => [$agreementItem]],
        ]);

        $this->setSuccessResponseExample($spec, '/transfer/convert', 'post', [
            'data' => [
                'type' => 'convert',
                'lines' => [
                    '淘宝口令：￥a1B2C3D4E5F￥',
                    '结果链接：https://demo.example.com/transfer/20260417120000',
                ],
                'result_url' => 'https://demo.example.com/transfer/20260417120000',
            ],
        ]);

        $this->setSuccessResponseExample($spec, '/transfer/short-link', 'post', [
            'data' => [
                'type' => 'short-link',
                'lines' => [
                    '短链地址：https://demo.example.com/s/7bc82e19',
                    '结果链接：https://demo.example.com/s/7bc82e19',
                ],
                'result_url' => 'https://demo.example.com/s/7bc82e19',
            ],
        ]);

        $this->setSuccessResponseExample($spec, '/admin/dashboard', 'post', [
            'data' => [
                'cards' => [
                    ['label' => '用户数', 'value' => 128],
                    ['label' => '今日转链', 'value' => 36],
                    ['label' => '短链生成', 'value' => 18],
                ],
            ],
        ]);

        $this->setSuccessResponseExample($spec, '/admin/agreements', 'post', [
            'data' => ['items' => [$agreementItem]],
        ]);

        $this->setSuccessResponseExample($spec, '/admin/user-agreements', 'post', [
            'data' => ['items' => [$agreementItem]],
        ]);

        $this->setSuccessResponseExample($spec, '/admin/agreements/detail', 'post', [
            'data' => ['agreement' => $agreement],
        ]);

        $this->setSuccessResponseExample($spec, '/admin/user-agreements/detail', 'post', [
            'data' => ['agreement' => $agreement],
        ]);

        $this->setSuccessResponseExample($spec, '/admin/agreements/save', 'post', [
            'data' => ['agreement' => $agreement],
        ]);

        $this->setSuccessResponseExample($spec, '/admin/user-agreements/save', 'post', [
            'data' => ['agreement' => $agreement],
        ]);

        $this->setSuccessResponseExample($spec, '/admin/agreements/activate', 'post', [
            'data' => ['agreement' => $agreement],
        ]);

        $this->setSuccessResponseExample($spec, '/admin/user-agreements/activate', 'post', [
            'data' => ['agreement' => $agreement],
        ]);
    }

    /**
     * @param array<string, mixed> $spec
     * @param mixed $example
     */
    private function setRequestExample(array &$spec, string $path, string $method, mixed $example): void
    {
        if (
            !isset($spec['paths'][$path][$method]['requestBody']['content']['application/json'])
            || !is_array($spec['paths'][$path][$method]['requestBody']['content']['application/json'])
        ) {
            return;
        }

        $spec['paths'][$path][$method]['requestBody']['content']['application/json']['example'] = $example;
    }

    /**
     * @param array<string, mixed> $spec
     * @param array<string, mixed> $properties
     */
    private function ensureJsonRequestBody(
        array &$spec,
        string $path,
        string $method,
        array $properties = [],
        bool $required = true
    ): void {
        if (!isset($spec['paths'][$path][$method]) || !is_array($spec['paths'][$path][$method])) {
            return;
        }

        if (isset($spec['paths'][$path][$method]['requestBody'])) {
            return;
        }

        $spec['paths'][$path][$method]['requestBody'] = [
            'required' => $required,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => $properties !== [] ? $properties : (object) [],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $spec
     * @param array<string, mixed> $example
     */
    private function setSuccessResponseExample(array &$spec, string $path, string $method, array $example): void
    {
        if (
            !isset($spec['paths'][$path][$method]['responses']['200']['content']['application/json'])
            || !is_array($spec['paths'][$path][$method]['responses']['200']['content']['application/json'])
        ) {
            return;
        }

        $spec['paths'][$path][$method]['responses']['200']['content']['application/json']['example'] = $example;
    }

    private function resolveOperationSummary(string $path, string $method): string
    {
        $map = [
            'POST /' => '服务入口',
            'POST /health' => '健康检查',
            'POST /benchmark' => '性能测试',
            'POST /auth/signup' => '用户注册',
            'POST /auth/login' => '账号密码登录',
            'POST /auth/sms-code' => '发送短信验证码',
            'POST /auth/sms-login' => '短信验证码登录',
            'POST /auth/logout' => '退出登录',
            'POST /auth/qr-login' => '创建扫码登录二维码',
            'POST /auth/qr-login/status' => '查询扫码登录状态',
            'POST /auth/qr-login/scan' => '手机端标记已扫码',
            'POST /auth/qr-login/confirm' => '手机端确认二维码登录',
            'POST /me' => '获取当前用户信息',
            'POST /agreements/current' => '获取当前协议',
            'POST /user-agreements/current' => '获取当前用户协议',
            'POST /agreements/history' => '获取协议历史版本',
            'POST /user-agreements/history' => '获取用户协议历史版本',
            'POST /agreements/detail' => '获取协议详情',
            'POST /user-agreements/detail' => '获取用户协议详情',
            'POST /admin/dashboard' => '获取后台仪表盘',
            'POST /admin/agreements' => '获取后台协议列表',
            'POST /admin/user-agreements' => '获取后台用户协议列表',
            'POST /admin/agreements/detail' => '获取后台协议详情',
            'POST /admin/user-agreements/detail' => '获取后台用户协议详情',
            'POST /admin/agreements/save' => '保存后台协议',
            'POST /admin/user-agreements/save' => '保存后台用户协议',
            'POST /admin/agreements/activate' => '启用后台协议',
            'POST /admin/user-agreements/activate' => '启用后台用户协议',
            'POST /transfer/convert' => '普通转链',
            'POST /transfer/short-link' => '生成短链',
            'POST /openapi.json' => '获取OpenAPI文档',
            'POST /stream' => '流式响应测试',
        ];

        return $map[$method . ' ' . $path] ?? '';
    }

    private function translateTag(string $tag): string
    {
        return match ($tag) {
            'Health' => '健康检查',
            'Authentication' => '认证',
            'QR Login' => '二维码登录',
            'User' => '用户',
            'Transfer' => '转链',
            'User Agreement' => '协议',
            'Admin User Agreement' => '管理后台',
            'Files' => '文件',
            'Admin' => '管理后台',
            'API' => '系统',
            default => $tag,
        };
    }

    private function translateResponseDescription(string $description): string
    {
        return match ($description) {
            'Success' => '成功',
            'Created' => '创建成功',
            'Accepted' => '已接收',
            'No Content' => '无返回内容',
            'Bad Request' => '请求参数错误',
            'Unauthorized' => '未登录或登录已失效',
            'Forbidden' => '禁止访问',
            'Not Found' => '资源不存在',
            'Unprocessable Entity' => '请求数据不合法',
            'Internal Server Error' => '服务器内部错误',
            default => $description,
        };
    }
}
