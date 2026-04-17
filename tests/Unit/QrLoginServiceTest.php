<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Models\QrLoginTicket;
use App\Services\QrLoginService;
use BaseApi\App;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class QrLoginServiceTest extends TestCase
{
    /**
     * @var array<int, string>
     */
    private array $ticketIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        App::boot('E:/D/phpStudy/WWW/transferLinkPhp');
    }

    protected function tearDown(): void
    {
        foreach ($this->ticketIds as $ticketId) {
            $ticket = QrLoginTicket::firstWhere('ticket_id', '=', $ticketId);
            if ($ticket instanceof QrLoginTicket) {
                $ticket->delete();
            }
        }

        $this->ticketIds = [];

        parent::tearDown();
    }

    public function test_it_creates_a_pending_ticket(): void
    {
        $service = new QrLoginService(180, 'xingfanapp://pc-transfer-login');

        $ticket = $service->createTicket();
        $this->ticketIds[] = $ticket['ticket_id'];

        $this->assertSame('pending', $ticket['status']);
        $this->assertArrayHasKey('ticket_id', $ticket);
        $this->assertArrayHasKey('poll_token', $ticket);
        $this->assertArrayHasKey('qr_content', $ticket);
        $this->assertArrayHasKey('qr_image_data_url', $ticket);
        $this->assertGreaterThan(0, $ticket['expires_in']);
        $this->assertStringStartsWith('data:image/png;base64,', $ticket['qr_image_data_url']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $ticket['created_at']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $ticket['expires_at']);
    }

    public function test_it_moves_ticket_through_scan_confirm_and_login(): void
    {
        $service = new QrLoginService(180, 'xingfanapp://pc-transfer-login');

        $ticket = $service->createTicket();
        $this->ticketIds[] = $ticket['ticket_id'];
        $scanned = $service->markScanned($ticket['ticket_id'], [
            'id' => 'user-100',
            'user_name' => '星返用户',
            'email' => 'scan@example.com',
            'role' => 'user',
        ]);

        $this->assertSame('scanned', $scanned['status']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $scanned['scanned_at']);

        $confirmed = $service->confirmLogin($ticket['ticket_id'], [
            'id' => 'user-100',
            'user_name' => '星返用户',
            'email' => 'scan@example.com',
            'role' => 'user',
        ]);

        $this->assertSame('confirmed', $confirmed['status']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $confirmed['confirmed_at']);

        $loggedIn = $service->markPcLoggedIn($ticket['ticket_id'], $ticket['poll_token']);

        $this->assertSame('logged_in', $loggedIn['status']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $loggedIn['logged_in_at']);

        $status = $service->getStatusForPc($ticket['ticket_id'], $ticket['poll_token']);

        $this->assertSame('logged_in', $status['status']);
        $this->assertSame('user-100', $status['user']['id']);
        $this->assertSame('星返用户', $status['user']['user_name']);
    }

    public function test_it_rejects_wrong_pc_session(): void
    {
        $service = new QrLoginService(180, 'xingfanapp://pc-transfer-login');
        $ticket = $service->createTicket();
        $this->ticketIds[] = $ticket['ticket_id'];

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('二维码票据与当前电脑端轮询令牌不匹配');

        $service->getStatusForPc($ticket['ticket_id'], 'other-session');
    }
}
