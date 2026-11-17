<?php

namespace Tests\Feature;

use App\Models\Terminal;
use App\Models\TerminalService;
use App\Models\Transaction;
use App\Services\Usrbb\TerminalSynchronizer;
use App\Services\Usrbb\TransactionSynchronizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionSynchronizerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(string $name = 'transactions'): array
    {
        return json_decode(file_get_contents(base_path("tests/Fixtures/usrbb/{$name}.json")), true);
    }

    public function test_it_stores_transactions(): void
    {
        $synced = app(TransactionSynchronizer::class)->sync($this->payload());

        $this->assertSame(3, $synced);

        $transaction = Transaction::findOrFail(7094366);
        $this->assertSame(5810, $transaction->tid);
        $this->assertSame('payment_card', $transaction->type);
        $this->assertSame('2000.00', $transaction->price);
        $this->assertSame('30.59', $transaction->amount_dispensed);
        $this->assertSame('65.40', $transaction->service_price);
        $this->assertTrue($transaction->printed);
        $this->assertTrue($transaction->no_troubles);
        $this->assertFalse($transaction->approved);
        $this->assertNull($transaction->fisc);
        $this->assertSame('2026-11-17 07:36:51', $transaction->remote_created_at->toDateTimeString());
    }

    public function test_it_stores_terminal_services_from_nested_data(): void
    {
        app(TransactionSynchronizer::class)->sync($this->payload());

        $this->assertSame(2, TerminalService::count());

        $service = TerminalService::findOrFail(359);
        $this->assertSame(292, $service->terminal_id);
        $this->assertSame('1', $service->station_id);
        $this->assertSame('АИ-92', $service->name);
        $this->assertSame('65.40', $service->price);
        $this->assertSame(2, $service->transactions()->count());
        $this->assertSame('АИ-95', Transaction::findOrFail(7094408)->service->name);
    }

    public function test_it_creates_a_stub_terminal_that_a_later_terminal_sync_completes(): void
    {
        app(TransactionSynchronizer::class)->sync($this->payload());

        $terminal = Terminal::findOrFail(292);
        $this->assertSame('Терм-297', $terminal->name);
        $this->assertSame(57, $terminal->org_id);
        $this->assertSame(3, $terminal->transactions()->count());

        app(TerminalSynchronizer::class)->sync($this->payload('terminals'));

        $this->assertSame('297-1', $terminal->fresh()->settings['terminal_id']);
        $this->assertSame(3, $terminal->transactions()->count());
    }

    public function test_resync_updates_existing_transactions(): void
    {
        $synchronizer = app(TransactionSynchronizer::class);
        $synchronizer->sync($this->payload());

        $payload = $this->payload();
        $payload['rows'][0]['approved'] = 1;
        $payload['rows'][0]['fisc'] = '{"fd":123,"fp":"456"}';
        $synchronizer->sync($payload);

        $this->assertSame(3, Transaction::count());

        $transaction = Transaction::findOrFail(7094408);
        $this->assertTrue($transaction->approved);
        $this->assertSame(['fd' => 123, 'fp' => '456'], $transaction->fisc);
    }
}
