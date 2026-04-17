<?php

namespace App\Models;

use BaseApi\Models\BaseModel;

class SmsLoginCode extends BaseModel
{
    public string $mobile = '';

    public string $purpose = 'login';

    public string $code_hash = '';

    public ?string $expires_at = null;

    public ?string $used_at = null;

    public ?string $request_ip = null;

    public int $attempts = 0;

    /**
     * Define indexes for this model
     * @var array<string, string>
     */
    public static array $indexes = [
        'mobile' => 'index',
        'purpose' => 'index',
        'expires_at' => 'index',
    ];

    /**
     * Define custom columns for this model
     * @var array<string, array<string, mixed>>
     */
    public static array $columns = [
        'mobile' => ['type' => 'VARCHAR(20)'],
        'purpose' => ['type' => 'VARCHAR(50)', 'default' => 'login'],
        'code_hash' => ['type' => 'VARCHAR(255)'],
        'expires_at' => ['type' => 'DATETIME', 'nullable' => true],
        'used_at' => ['type' => 'DATETIME', 'nullable' => true],
        'request_ip' => ['type' => 'VARCHAR(64)', 'nullable' => true],
        'attempts' => ['type' => 'INT', 'default' => '0'],
    ];
}
