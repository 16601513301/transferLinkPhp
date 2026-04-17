<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\QrLoginTicket;
use RuntimeException;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use stdClass;

class QrLoginService
{
    private const string DEFAULT_SCENE = 'xingfan_pc_transfer_login';
    private const string DATETIME_FORMAT = 'Y-m-d H:i:s';
    private const int QR_IMAGE_SIZE = 320;
    private const int QR_IMAGE_MARGIN = 12;

    /**
     * @var array<int, string>
     */
    private const array FINAL_STATUSES = ['expired', 'logged_in'];

    public function __construct(
        private readonly int $ttlSeconds = 180,
        private readonly string $deepLinkBase = 'xingfanapp://pc-transfer-login'
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function createTicket(): array
    {
        $ticket = new QrLoginTicket();
        $ticket->ticket_id = bin2hex(random_bytes(16));
        $ticket->scene = self::DEFAULT_SCENE;
        $ticket->status = 'pending';
        $ticket->pc_poll_token = bin2hex(random_bytes(16));
        $ticket->created_at = $this->now();
        $ticket->expires_at = $this->expiresAt();
        $ticket->scanned_at = null;
        $ticket->confirmed_at = null;
        $ticket->logged_in_at = null;
        $ticket->setScanUser(null);
        $ticket->setConfirmUser(null);

        if (!$ticket->save()) {
            throw new RuntimeException('二维码登录票据写入失败');
        }

        return $this->presentTicket($ticket, true, true);
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatusForPc(string $ticketId, string $pollToken): array
    {
        $ticket = $this->readTicket($ticketId);
        $this->assertPcOwnership($ticket, $pollToken);

        return $this->presentTicket($ticket);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    public function markScanned(string $ticketId, array $user): array
    {
        $ticket = $this->readTicket($ticketId);
        $this->assertActionable($ticket);

        $normalizedUser = $this->normalizeUser($user);

        $ticket->status = 'scanned';
        $ticket->scanned_at = $this->now();
        $ticket->setScanUser($normalizedUser);

        if ($ticket->getConfirmUser() === null) {
            $ticket->setConfirmUser($normalizedUser);
        }

        if (!$ticket->save()) {
            throw new RuntimeException('二维码登录票据写入失败');
        }

        return $this->presentTicket($ticket);
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    public function confirmLogin(string $ticketId, array $user): array
    {
        $ticket = $this->readTicket($ticketId);
        $this->assertActionable($ticket);

        $normalizedUser = $this->normalizeUser($user);

        if ($ticket->getScanUser() === null) {
            $ticket->setScanUser($normalizedUser);
            $ticket->scanned_at = $this->now();
        }

        $ticket->status = 'confirmed';
        $ticket->confirmed_at = $this->now();
        $ticket->setConfirmUser($normalizedUser);

        if (!$ticket->save()) {
            throw new RuntimeException('二维码登录票据写入失败');
        }

        return $this->presentTicket($ticket);
    }

    /**
     * @return array<string, mixed>
     */
    public function markPcLoggedIn(string $ticketId, string $pollToken): array
    {
        $ticket = $this->readTicket($ticketId);
        $this->assertPcOwnership($ticket, $pollToken);

        if ($ticket->status !== 'confirmed') {
            throw new RuntimeException('当前二维码尚未完成手机端授权确认');
        }

        $ticket->status = 'logged_in';
        $ticket->logged_in_at = $this->now();

        if (!$ticket->save()) {
            throw new RuntimeException('二维码登录票据写入失败');
        }

        return $this->presentTicket($ticket);
    }

    /**
     * @return QrLoginTicket
     */
    private function readTicket(string $ticketId): QrLoginTicket
    {
        $this->assertTicketId($ticketId);

        $ticket = QrLoginTicket::firstWhere('ticket_id', '=', $ticketId);
        if (!$ticket instanceof QrLoginTicket) {
            throw new RuntimeException('二维码登录票据不存在');
        }

        if ($this->isExpired($ticket)) {
            $ticket->status = 'expired';
            if (!$ticket->save()) {
                throw new RuntimeException('二维码登录票据写入失败');
            }
        }

        return $ticket;
    }

    /**
     * @param QrLoginTicket $ticket
     * @return array<string, mixed>
     */
    private function presentTicket(
        QrLoginTicket $ticket,
        bool $includePollToken = false,
        bool $includeQrImage = false
    ): array
    {
        $expiresIn = max(0, strtotime((string) $ticket->expires_at) - time());
        $user = new stdClass();
        $confirmUser = $ticket->getConfirmUser();
        $scanUser = $ticket->getScanUser();
        if (is_array($confirmUser)) {
            $user = $this->normalizeUser($confirmUser);
        } elseif (is_array($scanUser)) {
            $user = $this->normalizeUser($scanUser);
        }
        $qrContent = $this->buildQrContent($ticket->ticket_id);

        $data = [
            'scene' => (string) ($ticket->scene !== '' ? $ticket->scene : self::DEFAULT_SCENE),
            'ticket_id' => $ticket->ticket_id,
            'status' => $ticket->status,
            'qr_content' => $qrContent,
            'expires_in' => $expiresIn,
            'created_at' => $this->formatDateTime($ticket->created_at),
            'expires_at' => $this->formatDateTime($ticket->expires_at),
            'scanned_at' => $this->formatDateTime($ticket->scanned_at),
            'confirmed_at' => $this->formatDateTime($ticket->confirmed_at),
            'logged_in_at' => $this->formatDateTime($ticket->logged_in_at),
            'user' => $user,
        ];

        if ($includePollToken) {
            $data['poll_token'] = $ticket->pc_poll_token;
        }

        if ($includeQrImage) {
            $data['qr_image_data_url'] = $this->buildQrImageDataUrl($qrContent);
        }

        return $data;
    }

    /**
     * @param QrLoginTicket $ticket
     */
    private function assertActionable(QrLoginTicket $ticket): void
    {
        if ($this->isExpired($ticket)) {
            throw new RuntimeException('二维码已过期，请刷新后重新扫码');
        }

        if (in_array($ticket->status, self::FINAL_STATUSES, true)) {
            throw new RuntimeException('当前二维码状态不可继续操作');
        }
    }

    /**
     * @param QrLoginTicket $ticket
     */
    private function assertPcOwnership(QrLoginTicket $ticket, string $pollToken): void
    {
        if (trim($pollToken) === '') {
            throw new RuntimeException('电脑端轮询令牌不存在');
        }

        if ($ticket->pc_poll_token !== $pollToken) {
            throw new RuntimeException('二维码票据与当前电脑端轮询令牌不匹配');
        }
    }

    /**
     * @param QrLoginTicket $ticket
     */
    private function isExpired(QrLoginTicket $ticket): bool
    {
        $expiresAt = strtotime((string) ($ticket->expires_at ?? ''));
        if ($expiresAt === false) {
            return true;
        }

        return $expiresAt <= time();
    }

    /**
     * @param array<string, mixed> $user
     * @return array<string, mixed>
     */
    private function normalizeUser(array $user): array
    {
        return [
            'id' => (string) ($user['id'] ?? ''),
            'user_name' => (string) ($user['user_name'] ?? $user['name'] ?? ''),
            'email' => (string) ($user['email'] ?? ''),
            'mobile' => (string) ($user['mobile'] ?? ''),
            'role' => (string) ($user['role'] ?? ''),
        ];
    }

    private function assertTicketId(string $ticketId): void
    {
        if (!preg_match('/^[a-f0-9]{32}$/', $ticketId)) {
            throw new RuntimeException('票据编号格式无效');
        }
    }

    private function buildQrContent(string $ticketId): string
    {
        return sprintf('%s?scene=%s&ticket_id=%s', $this->deepLinkBase, self::DEFAULT_SCENE, $ticketId);
    }

    private function buildQrImageDataUrl(string $content): string
    {
        $writer = new PngWriter();
        $qrCode = new QrCode(
            data: $content,
            size: self::QR_IMAGE_SIZE,
            margin: self::QR_IMAGE_MARGIN
        );

        return $writer->write($qrCode)->getDataUri();
    }
    private function now(): string
    {
        return date(self::DATETIME_FORMAT);
    }

    private function expiresAt(): string
    {
        return date(self::DATETIME_FORMAT, time() + $this->ttlSeconds);
    }

    private function formatDateTime(mixed $value): string
    {
        if (!is_string($value) || trim($value) === '') {
            return '';
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return (string) $value;
        }

        return date(self::DATETIME_FORMAT, $timestamp);
    }
}
