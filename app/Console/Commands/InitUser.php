<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use function Symfony\Component\Translation\t;

#[Signature('app:init-user {email}')]
#[Description('Command description')]
class InitUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pass = Str::random(32);
        User::create([
            'email'    => $this->argument('email'),
            'password' => bcrypt($pass),
            'name'     => 'Администратор',
        ]);
        $this->info('Password: ' . $pass);
    }
}
