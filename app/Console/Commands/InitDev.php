<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:init-dev')]
#[Description('Command description')]
class InitDev extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('migrate:refresh');
        $this->createDevUser();
        $this->createDevServices();

    }

    private function createDevUser()
    {
        User::create([
            'email'    => 'admin@admin.com',
            'password' => bcrypt('admin@admin.com'),
            'name'     => 'Администратор',
        ]);
    }
    private function createDevServices()
    {
        $services = [
            [
                'id' => 182,
                'name' => 'АИ-92',
                'description' => '297',
                'price' => 65.80,
                'currency' => 643,
                'good_name' => 'АИ-92-К5',
                'good_tax' => 0,
                'good_type' => 'excise',
                'archived' => false,
                'data' => null,
                'yandex_type' => 'a92',
                'remote_created_at' => '2026-02-26 07:58:42',
                'remote_updated_at' => '2026-11-17 09:45:02',
            ],
            [
                'id' => 183,
                'name' => 'АИ-95',
                'description' => '297',
                'price' => 71.10,
                'currency' => 643,
                'good_name' => 'АИ-95-К5',
                'good_tax' => 0,
                'good_type' => 'excise',
                'archived' => false,
                'data' => null,
                'yandex_type' => 'a95',
                'remote_created_at' => '2026-02-26 07:59:06',
                'remote_updated_at' => '2026-11-17 10:34:45',
            ],
        ];

        foreach ($services as $service) {
            Service::create($service);
        }
    }
}
