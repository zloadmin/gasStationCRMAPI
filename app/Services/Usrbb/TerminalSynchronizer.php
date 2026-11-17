<?php

namespace App\Services\Usrbb;

use App\Models\Terminal;
use App\Models\TerminalDevice;
use App\Services\Usrbb\Concerns\ParsesApiValues;
use Illuminate\Support\Facades\DB;

/**
 * Stores a usrbb.ru terminals response ({"count": n, "rows": [...]}) in the local database.
 */
class TerminalSynchronizer
{
    use ParsesApiValues;

    /**
     * @param  array{count?: int, rows: list<array<string, mixed>>}  $payload
     * @return int Number of terminals synced.
     */
    public function sync(array $payload): int
    {
        $rows = $payload['rows'] ?? [];

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $this->syncTerminal($row);
            }
        });

        return count($rows);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncTerminal(array $row): Terminal
    {
        $terminal = Terminal::updateOrCreate(['id' => $row['id']], [
            'org_id' => $row['org_id'],
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'secret' => $row['secret'] ?? null,
            'type' => $row['type'] ?? 'default',
            'archived' => (bool) ($row['archived'] ?? false),
            'terminal_info' => $this->decodeJson($row['terminal_info'] ?? null),
            'settings' => $this->decodeJson($row['settings'] ?? null),
            'settings_updated_at' => $this->parseDate($row['settings_updated_at'] ?? null),
            'last_query' => $row['last_query'] ?? null,
            'last_query_at' => $this->parseDate($row['last_query_at'] ?? null),
            'yandex_enabled' => (bool) ($row['yandex_enabled'] ?? false),
            'yandex_test' => (bool) ($row['yandex_test'] ?? false),
            'yandex_token' => $row['yandex_token'] ?? null,
            'yandex_name' => $row['yandex_name'] ?? null,
            'yandex_address' => $row['yandex_address'] ?? null,
            'yandex_location' => $row['yandex_location'] ?? null,
            'yandex_autooplata_enabled' => (bool) ($row['yandex_autooplata_enabled'] ?? false),
            'place_id' => $row['place_id'] ?? null,
            'ping_settings' => $this->decodeJson($row['ping_settings'] ?? null),
            'ping_at' => $this->parseDate($row['ping_at'] ?? null),
            'loyal_client_enabled' => (bool) ($row['loyal_client_enabled'] ?? false),
            'loyal_client_api' => $row['loyal_client_api'] ?? null,
            'loyal_client_point' => $row['loyal_client_point'] ?? null,
            'remote_created_at' => $this->parseDate($row['created_at'] ?? null),
            'remote_updated_at' => $this->parseDate($row['updated_at'] ?? null),
        ]);

        $devices = $row['devices'] ?? [];

        foreach ($devices as $device) {
            $this->syncDevice($terminal, $device);
        }

        // Drop devices that were removed from the terminal upstream.
        $terminal->devices()->whereNotIn('id', array_column($devices, 'id'))->delete();

        return $terminal;
    }

    /**
     * @param  array<string, mixed>  $device
     */
    private function syncDevice(Terminal $terminal, array $device): TerminalDevice
    {
        return TerminalDevice::updateOrCreate(['id' => $device['id']], [
            'terminal_id' => $terminal->id,
            'device_name' => $device['device_name'],
            'last_report' => $this->decodeJson($device['last_report'] ?? null),
            'last_report_at' => $this->parseDate($device['last_report_at'] ?? null),
            'last_report_time' => $device['last_report_time'] ?? null,
            'last_error' => $device['last_error'] ?? null,
            'from_device' => (bool) ($device['from_device'] ?? false),
            'remote_created_at' => $this->parseDate($device['created_at'] ?? null),
            'remote_updated_at' => $this->parseDate($device['updated_at'] ?? null),
        ]);
    }
}
