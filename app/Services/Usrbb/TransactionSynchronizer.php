<?php

namespace App\Services\Usrbb;

use App\Models\Terminal;
use App\Models\TerminalService;
use App\Models\Transaction;
use App\Services\Usrbb\Concerns\ParsesApiValues;
use Illuminate\Support\Facades\DB;

/**
 * Stores one page of a usrbb.ru transactions response ({"rows": [...], "count": n}) in the local database.
 *
 * Nested terminal and service objects are used to create missing terminals and keep terminal services current.
 */
class TransactionSynchronizer
{
    use ParsesApiValues;

    /**
     * @param array{rows: list<array<string, mixed>>, count?: int, count_archived?: int} $payload
     * @return int Number of transactions synced.
     */
    public function sync(array $payload): int
    {
        $rows = $payload['rows'] ?? [];

        if ($rows === []) {
            return 0;
        }

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $this->syncTerminal($row);
                $this->syncService($row);
                $this->syncTransaction($row);
            }

        });

        return count($rows);
    }

    /**
     * Create a stub terminal if this transaction arrived before the terminal itself was synced.
     *
     * @param array<string, mixed> $row
     */
    private function syncTerminal(array $row): void
    {
        $terminal = $row['terminal'] ?? null;

        Terminal::firstOrCreate([ 'id' => $row['terminal_id'] ], [
            'org_id' => $terminal['org_id'] ?? 0,
            'name'   => $terminal['name'] ?? "Terminal {$row['terminal_id']}",
        ]);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function syncService(array $row): void
    {
        $service = $row['service'] ?? null;

        if ($service === null) {
            return;
        }

        TerminalService::updateOrCreate([ 'id' => $service['id'] ], [
            'terminal_id'  => $row['terminal_id'],
            'station_id'   => $service['station_id'] ?? null,
            'station_name' => $service['station_name'] ?? null,
            'name'         => $service['service']['name'] ?? null,
            'price'        => $service['service']['price'] ?? null,
        ]);
    }


    private function syncTransaction(mixed $row)
    {
        Transaction::updateOrCreate(
            [ 'id' => $row['id'] ],
            [
                'id'                  => $row['id'],
                'tid'                 => $row['tid'],
                'terminal_id'         => $row['terminal_id'] ?? null,
                'terminal_service_id' => $row['service_id'] ?? null,
                'type'                => $row['type'],
                'price'               => $row['price'],
                'inserted'            => $row['inserted'] ?? 0,
                'dispensed'           => $row['dispensed'] ?? 0,
                'price_returned'      => $row['price_returned'] ?? 0,
                'discount'            => $row['discount'] ?? 0,
                'currency'            => $row['currency'] ?? 0,
                'service_price'       => $row['service_price'] ?? null,
                'amount'              => $row['amount'] ?? null,
                'amount_dispensed'    => $row['amount_dispensed'] ?? null,
                'printed'             => (bool)($row['printed'] ?? false),
                'sync'                => (bool)($row['sync'] ?? false),
                'no_troubles'         => (bool)($row['no_troubles'] ?? true),
                'approved'            => (bool)($row['approved'] ?? false),
                'user_id'             => $row['user_id'] ?? null,
                'remote_created_at'   => $this->parseDate($row['created_at'] ?? null)?->toDateTimeString(),
            ]
        );
        // update date from full request, better create new method
        if (isset($row['service']['service']['id'])) {
            Transaction::findOrFail($row['id'])->update([
                'parsed_full' => true,
                'service_id'          => $row['service']['service']['id'] ?? null,
                'service_json'        => isset($row['service']['service']['id']) ? $row['service'] : null,
                'data'                => $row['data'] ?? null,
            ]);
        }
    }
}
