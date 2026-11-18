<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    private const string URL = '/api/transactions';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createTerminalService(1, 'Терминал №1');
        $this->createTerminalService(2, 'Терминал №2');

//        $this->createService(1, 'АИ-95');
//        $this->createService(2, 'АИ-92');
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_index_returns_empty_collection(): void
    {
        $this->getJson(self::URL)
            ->assertOk()
            ->assertJson(['data' => []]);
    }

    public function test_index_returns_transactions(): void
    {
        $this->createTransaction(100, 1, 1);
        $this->createTransaction(101, 2, 2);

        $this->getJson(self::URL)
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', 101)
            ->assertJsonPath('data.1.id', 100);
    }

    public function test_index_returns_transaction_resource(): void
    {
        $this->createTransaction(100, 1, 1);

        $this->getJson(self::URL)
            ->assertOk()
            ->assertJsonPath('data.0.id', 100)
            ->assertJsonPath('data.0.tid', 500)
            ->assertJsonPath('data.0.terminal_id', 1)
            ->assertJsonPath('data.0.service_id', 1)
            ->assertJsonPath('data.0.type', 'payment_cash');
    }

    // -------------------------------------------------------------------------
    // Date filters
    // -------------------------------------------------------------------------

    public function test_index_filters_by_date_from(): void
    {
        $this->createTransaction(100, 1, 1, 'payment_cash', '2026-11-01 10:00:00');
        $this->createTransaction(101, 1, 1, 'payment_cash', '2026-11-10 10:00:00');

        $this->getJson(self::URL . '?date_from=2026-11-05 00:00:00')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 101);
    }

    public function test_index_filters_by_date_to(): void
    {
        $this->createTransaction(100, 1, 1, 'payment_cash', '2026-11-01 10:00:00');
        $this->createTransaction(101, 1, 1, 'payment_cash', '2026-11-10 10:00:00');

        $this->getJson(self::URL . '?date_to=2026-11-05 23:59:59')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 100);
    }

    public function test_index_filters_by_date_range(): void
    {
        $this->createTransaction(100, 1, 1, 'payment_cash', '2026-11-01 10:00:00');
        $this->createTransaction(101, 1, 1, 'payment_cash', '2026-11-10 10:00:00');
        $this->createTransaction(102, 1, 1, 'payment_cash', '2026-11-20 10:00:00');

        $this->getJson(self::URL . '?date_from=2026-11-05 00:00:00&date_to=2026-11-15 23:59:59')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 101);
    }

    // -------------------------------------------------------------------------
    // Terminal filter
    // -------------------------------------------------------------------------

    public function test_index_filters_by_terminal_id(): void
    {
        $this->createTransaction(100, 1, 1);
        $this->createTransaction(101, 2, 1);

        $this->getJson(self::URL . '?terminal_id=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 100);
    }

    // -------------------------------------------------------------------------
    // Service filter
    // -------------------------------------------------------------------------

    public function test_index_filters_by_service_id(): void
    {
        $this->createTransaction(100, 1, 1);
        $this->createTransaction(101, 1, 2);

        $this->getJson(self::URL . '?service_id=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 100)
            ->assertJsonPath('data.0.service_id', 1);
    }

    // -------------------------------------------------------------------------
    // Payment type filter
    // -------------------------------------------------------------------------

    public function test_index_filters_by_type(): void
    {
        $this->createTransaction(100, 1, 1, 'payment_cash');
        $this->createTransaction(101, 1, 1, 'payment_card');

        $this->getJson(self::URL . '?type=payment_cash')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 100)
            ->assertJsonPath('data.0.type', 'payment_cash');
    }

    // -------------------------------------------------------------------------
    // Transaction ID filter
    // -------------------------------------------------------------------------

    public function test_index_filters_by_transaction_id(): void
    {
        $this->createTransaction(100, 1, 1);
        $this->createTransaction(101, 1, 1);

        $this->getJson(self::URL . '?id=100')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 100);
    }

    // -------------------------------------------------------------------------
    // Terminal transaction ID filter
    // -------------------------------------------------------------------------

    public function test_index_filters_by_tid(): void
    {
        $this->createTransaction(100, 1, 1, 'payment_cash', null, 500);
        $this->createTransaction(101, 1, 1, 'payment_cash', null, 501);

        $this->getJson(self::URL . '?tid=500')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 100)
            ->assertJsonPath('data.0.tid', 500);
    }

    // -------------------------------------------------------------------------
    // Combined filters
    // -------------------------------------------------------------------------

    public function test_index_applies_multiple_filters(): void
    {
        $this->createTransaction(100, 1, 1, 'payment_cash', '2026-11-10 10:00:00', 500);
        $this->createTransaction(101, 1, 2, 'payment_card', '2026-11-10 10:00:00', 501);
        $this->createTransaction(102, 2, 1, 'payment_cash', '2026-11-10 10:00:00', 502);

        $this->getJson(self::URL . '?terminal_id=1&service_id=1&type=payment_cash')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 100);
    }

    // -------------------------------------------------------------------------
    // Pagination
    // -------------------------------------------------------------------------

    public function test_index_uses_default_pagination(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->createTransaction(100 + $i, 1, 1);
        }

        $this->getJson(self::URL)
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 20)
            ->assertJsonPath('meta.total', 25)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_index_accepts_custom_per_page(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->createTransaction(100 + $i, 1, 1);
        }

        $this->getJson(self::URL . '?per_page=10')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 25);
    }

    public function test_index_returns_requested_page(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->createTransaction(100 + $i, 1, 1);
        }

        $this->getJson(self::URL . '?per_page=10&page=2')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('meta.current_page', 2);
    }

    // -------------------------------------------------------------------------
    // Validation
    // -------------------------------------------------------------------------

    public static function invalidFilters(): array
    {
        return [
            'invalid date_from' => [
                ['date_from' => 'not-a-date'],
                'date_from',
            ],
            'invalid date_to' => [
                ['date_to' => 'not-a-date'],
                'date_to',
            ],
            'date_to before date_from' => [
                [
                    'date_from' => '2026-11-10',
                    'date_to' => '2026-11-01',
                ],
                'date_to',
            ],
            'invalid terminal_id' => [
                ['terminal_id' => 'abc'],
                'terminal_id',
            ],
            'nonexistent terminal_id' => [
                ['terminal_id' => 999],
                'terminal_id',
            ],
            'invalid service_id' => [
                ['service_id' => 'abc'],
                'service_id',
            ],
            'nonexistent service_id' => [
                ['service_id' => 999],
                'service_id',
            ],
            'invalid type' => [
                ['type' => str_repeat('a', 101)],
                'type',
            ],
            'invalid transaction id' => [
                ['id' => 'abc'],
                'id',
            ],
            'zero transaction id' => [
                ['id' => 0],
                'id',
            ],
            'invalid tid' => [
                ['tid' => 'abc'],
                'tid',
            ],
            'zero tid' => [
                ['tid' => 0],
                'tid',
            ],
            'invalid per_page' => [
                ['per_page' => 'abc'],
                'per_page',
            ],
            'zero per_page' => [
                ['per_page' => 0],
                'per_page',
            ],
            'per_page greater than 100' => [
                ['per_page' => 101],
                'per_page',
            ],
            'invalid column_id' => [
                ['column_id' => 'abc'],
                'column_id',
            ],
            'invalid status' => [
                ['status' => str_repeat('a', 101)],
                'status',
            ],
        ];
    }

    #[DataProvider('invalidFilters')]
    public function test_index_validates_filters(
        array $filters,
        string $errorField
    ): void {
        $this->getJson(self::URL . '?' . http_build_query($filters))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errorField);
    }

    // -------------------------------------------------------------------------
    // Test helpers
    // -------------------------------------------------------------------------

    private function createTerminalService(int $id, string $name): void
    {
        DB::table('terminal_services')->insert([
            'id' => $id,
            'name' => $name,
        ]);
    }

    private function createService(int $id, string $goodName): void
    {
        DB::table('terminal_services')->insert([
            'id' => $id,
            'org_id' => 1,
            'name' => $goodName,
            'price' => 60.00,
            'currency' => 643,
            'good_name' => $goodName,
            'good_tax' => 0,
            'archived' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createTransaction(
        int $id,
        int $terminalId,
        int $serviceId,
        string $type = 'payment_cash',
        ?string $remoteCreatedAt = null,
        int $tid = 500
    ): void {
        DB::table('transactions')->insert([
            'id' => $id,
            'tid' => $tid,
            'terminal_id' => $terminalId,
            'service_id' => $serviceId,
            'type' => $type,

            'price' => 1000.00,
            'inserted' => 1000.00,
            'dispensed' => 0,
            'price_returned' => 0,
            'discount' => 0,
            'currency' => 643,

            'service_price' => 60.00,
            'amount' => 16.67,
            'amount_dispensed' => 16.67,

            'printed' => false,
            'sync' => true,
            'no_troubles' => true,
            'approved' => true,

            'remote_created_at' => $remoteCreatedAt ?? '2026-11-18 10:00:00',

            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
