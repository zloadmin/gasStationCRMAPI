<?php

namespace App\Services\Usrbb;

use App\Models\Service;
use App\Models\TerminalService;
use App\Services\Usrbb\Concerns\ParsesApiValues;
use Illuminate\Support\Facades\DB;

/**
 * Stores a usrbb.ru terminal services response ({"count": n, "rows": [...]})
 * in the local database.
 */
class ServiceSynchronizer
{
    use ParsesApiValues;

    /**
     * @param array{count?: int, rows: list<array<string, mixed>>} $payload
     * @return int Number of terminal services synced.
     */
    public function sync(array $payload): int
    {
        $rows = $payload['rows'] ?? [];

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                Service::updateOrCreate(
                    ['id' => $row['id']],
                    [
                        'org_id' => $row['org_id'],
                        'name' => $row['name'],
                        'description' => $row['description'] ?? null,
                        'price' => $row['price'],
                        'currency' => $row['currency'] ?? 643,
                        'good_name' => $row['good_name'] ?? null,
                        'good_tax' => $row['good_tax'] ?? 0,
                        'good_type' => $row['good_type'] ?? null,
                        'archived' => (bool) ($row['archived'] ?? false),
                        'data' => $this->decodeJson($row['data'] ?? null),
                        'yandex_type' => $row['yandex_type'] ?? null,
                        'remote_created_at' => $this->parseDate($row['created_at'] ?? null),
                        'remote_updated_at' => $this->parseDate($row['updated_at'] ?? null),
                    ]
                );
            }
        });

        return count($rows);
    }
}
