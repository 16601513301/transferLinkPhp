<?php

declare(strict_types=1);

namespace App\Models;

use BaseApi\Models\BaseModel;

class QrLoginTicket extends BaseModel
{
    public string $ticket_id = '';

    public string $scene = 'xingfan_pc_transfer_login';

    public string $status = 'pending';

    public string $pc_poll_token = '';

    public ?string $expires_at = null;

    public ?string $scanned_at = null;

    public ?string $confirmed_at = null;

    public ?string $logged_in_at = null;

    public string $scan_user_json = '';

    public string $confirm_user_json = '';

    /**
     * Define indexes for this model
     * @var array<string, string>
     */
    public static array $indexes = [
        'ticket_id' => 'unique',
        'pc_poll_token' => 'unique',
        'status' => 'index',
        'expires_at' => 'index',
    ];

    /**
     * Define custom columns for this model
     * @var array<string, array<string, mixed>>
     */
    public static array $columns = [
        'ticket_id' => ['type' => 'VARCHAR(32)'],
        'scene' => ['type' => 'VARCHAR(100)', 'default' => 'xingfan_pc_transfer_login'],
        'status' => ['type' => 'VARCHAR(32)', 'default' => 'pending'],
        'pc_poll_token' => ['type' => 'VARCHAR(32)'],
        'expires_at' => ['type' => 'DATETIME', 'nullable' => true],
        'scanned_at' => ['type' => 'DATETIME', 'nullable' => true],
        'confirmed_at' => ['type' => 'DATETIME', 'nullable' => true],
        'logged_in_at' => ['type' => 'DATETIME', 'nullable' => true],
        'scan_user_json' => ['type' => 'TEXT'],
        'confirm_user_json' => ['type' => 'TEXT'],
    ];

    /**
     * @param array<string, mixed>|null $user
     */
    public function setScanUser(?array $user): void
    {
        $this->scan_user_json = $this->encodeUser($user);
    }

    /**
     * @param array<string, mixed>|null $user
     */
    public function setConfirmUser(?array $user): void
    {
        $this->confirm_user_json = $this->encodeUser($user);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getScanUser(): ?array
    {
        return $this->decodeUser($this->scan_user_json);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getConfirmUser(): ?array
    {
        return $this->decodeUser($this->confirm_user_json);
    }

    /**
     * @param array<string, mixed>|null $user
     */
    private function encodeUser(?array $user): string
    {
        if ($user === null || $user === []) {
            return '';
        }

        $encoded = json_encode($user, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : '';
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decodeUser(string $json): ?array
    {
        $json = trim($json);
        if ($json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }
}
