<?php

namespace App\Services\Usrbb;

use App\Models\Organization;
use App\Services\Usrbb\Concerns\ParsesApiValues;
use Illuminate\Support\Facades\DB;

/**
 * Stores a usrbb.ru organizations response ({"count": n, "rows": [...]}) in the local database.
 */
class OrganizationSynchronizer
{
    use ParsesApiValues;

    /**
     * @param  array{count?: int, rows: list<array<string, mixed>>}  $payload
     * @return int Number of organizations synced.
     */
    public function sync(array $payload): int
    {
        $rows = $payload['rows'] ?? [];

        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                $this->syncOrganization($row);
            }
        });

        return count($rows);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function syncOrganization(array $row): Organization
    {
        $organization = Organization::updateOrCreate(['id' => $row['id']], [
            'name' => $row['name'],
            'description' => $row['description'] ?? null,
            'type' => $row['type'] ?? 'default',
            'email' => $row['email'] ?? null,
            'active' => (bool) ($row['active'] ?? true),
            'remote_created_at' => $this->parseDate($row['created_at'] ?? null),
            'remote_updated_at' => $this->parseDate($row['updated_at'] ?? null),
        ]);

        // Only sync memberships when the API included them, so a response without org_users keeps existing ones.
        if (array_key_exists('org_users', $row)) {
            $userIds = array_map('intval', array_column($row['org_users'] ?? [], 'user_id'));

            foreach ($userIds as $userId) {
                $organization->users()->firstOrCreate(['user_id' => $userId]);
            }

            $organization->users()->whereNotIn('user_id', $userIds)->delete();
        }

        return $organization;
    }
}
