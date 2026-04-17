<?php

namespace App\Models;

use BaseApi\Database\Relations\HasMany;
use BaseApi\Models\BaseModel;

class User extends BaseModel
{
    public string $user_name = '';

    public string $password = '';

    public string $email = '';

    public ?string $mobile = null;

    public bool $active = true;

    public string $role = 'guest';

    /**
     * Define indexes for this model
     * @var array<string, string>
     */
    public static array $indexes = [
        'email' => 'unique',
        'mobile' => 'unique',
    ];

    /**
     * Define custom columns for this model
     * @var array<string, array<string, mixed>>
     */
    public static array $columns = [
        'mobile' => ['type' => 'VARCHAR(20)', 'nullable' => true],
    ];

    public function checkPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * @return array<string, mixed>
     */
    public function toSafeArray(): array
    {
        $data = $this->jsonSerialize();
        unset($data['password']);
        $data['user_name'] = (string) ($data['user_name'] ?? '');
        $data['email'] = (string) ($data['email'] ?? '');
        $data['mobile'] = (string) ($data['mobile'] ?? '');
        $data['role'] = (string) ($data['role'] ?? '');

        return $data;
    }

    public function userApiTokens(): HasMany
    {
        return $this->hasMany(UserApiToken::class);
    }
}
