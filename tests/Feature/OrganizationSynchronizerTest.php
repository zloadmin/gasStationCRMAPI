<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\OrganizationUser;
use App\Models\Product;
use App\Models\Terminal;
use App\Services\Usrbb\OrganizationSynchronizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationSynchronizerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return json_decode(file_get_contents(base_path('tests/Fixtures/usrbb/organizations.json')), true);
    }

    public function test_it_stores_organizations_with_members(): void
    {
        $synced = app(OrganizationSynchronizer::class)->sync($this->payload());

        $this->assertSame(1, $synced);

        $organization = Organization::findOrFail(57);
        $this->assertSame('ООО Кубаторика', $organization->name);
        $this->assertSame('МО, Лосино-Петровский', $organization->description);
        $this->assertNull($organization->email);
        $this->assertTrue($organization->active);
        $this->assertSame('2026-10-20 19:27:13', $organization->remote_updated_at->toDateTimeString());
        $this->assertSame([86], $organization->users()->pluck('user_id')->all());
    }

    public function test_resync_replaces_members(): void
    {
        $synchronizer = app(OrganizationSynchronizer::class);
        $synchronizer->sync($this->payload());

        $payload = $this->payload();
        $payload['rows'][0]['org_users'] = [['org_id' => 57, 'user_id' => 90], ['org_id' => 57, 'user_id' => 91]];
        $synchronizer->sync($payload);

        $this->assertSame(1, Organization::count());
        $this->assertSame([90, 91], OrganizationUser::orderBy('user_id')->pluck('user_id')->all());
    }

    public function test_resync_without_org_users_keeps_members(): void
    {
        $synchronizer = app(OrganizationSynchronizer::class);
        $synchronizer->sync($this->payload());

        $payload = $this->payload();
        unset($payload['rows'][0]['org_users']);
        $synchronizer->sync($payload);

        $this->assertSame(1, OrganizationUser::count());
    }

    public function test_it_has_terminals_and_products(): void
    {
        $organization = Organization::factory()->create();
        Terminal::factory()->count(2)->create(['org_id' => $organization->id]);
        Product::factory()->create(['org_id' => $organization->id]);
        Product::factory()->create();

        $this->assertCount(2, $organization->terminals);
        $this->assertCount(1, $organization->products);
    }
}
