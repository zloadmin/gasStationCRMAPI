<?php

namespace Tests\Feature;

use App\Models\Terminal;
use App\Models\TerminalDevice;
use App\Services\Usrbb\TerminalSynchronizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerminalSynchronizerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return json_decode(file_get_contents(base_path('tests/Fixtures/usrbb/terminals.json')), true);
    }

    public function test_it_stores_terminals_with_decoded_json_fields(): void
    {
        $synced = app(TerminalSynchronizer::class)->sync($this->payload());

        $this->assertSame(1, $synced);

        $terminal = Terminal::findOrFail(292);
        $this->assertSame(57, $terminal->org_id);
        $this->assertSame('Терм-297', $terminal->name);
        $this->assertSame('297-1', $terminal->settings['terminal_id']);
        $this->assertSame('/kiosk/', $terminal->terminal_info['location']);
        $this->assertSame([38.20278, 55.865894], $terminal->yandex_location['coordinates']);
        $this->assertTrue($terminal->yandex_enabled);
        $this->assertFalse($terminal->archived);
        $this->assertSame('2026-11-17 08:55:25', $terminal->last_query_at->toDateTimeString());
        $this->assertSame('2026-02-26 08:00:12', $terminal->remote_created_at->toDateTimeString());
        $this->assertArrayNotHasKey('secret', $terminal->toArray());
        $this->assertArrayNotHasKey('yandex_token', $terminal->toArray());
    }

    public function test_it_stores_devices_with_decoded_reports(): void
    {
        app(TerminalSynchronizer::class)->sync($this->payload());

        $terminal = Terminal::findOrFail(292);
        $this->assertSame(['Topaz', 'CashcodeSM', 'Tank'], $terminal->devices()->orderBy('id')->pluck('device_name')->all());

        $tank = TerminalDevice::findOrFail(8840);
        $this->assertSame(65.4, $tank->last_report['status']['359']['price']);
        $this->assertSame(1794905722, $tank->last_report_time);
        $this->assertTrue($tank->terminal->is($terminal));

        $this->assertSame(22, TerminalDevice::findOrFail(8836)->last_report[0]['count']);
    }

    public function test_resync_updates_rows_and_removes_missing_devices(): void
    {
        $synchronizer = app(TerminalSynchronizer::class);
        $synchronizer->sync($this->payload());

        $payload = $this->payload();
        $payload['rows'][0]['name'] = 'Терм-297 (new)';
        array_pop($payload['rows'][0]['devices']);
        $synchronizer->sync($payload);

        $this->assertSame(1, Terminal::count());
        $this->assertSame('Терм-297 (new)', Terminal::findOrFail(292)->name);
        $this->assertSame(2, TerminalDevice::count());
        $this->assertNull(TerminalDevice::find(8840));
    }
}
