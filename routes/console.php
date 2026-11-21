<?php


use App\Console\Commands\Usrbb\UsrbbSynchronizer;
use Illuminate\Support\Facades\Schedule;



Schedule::command(UsrbbSynchronizer::class)
    ->everyMinute()
    ->withoutOverlapping(10);
//Schedule::command(UsrbbSynchronizer::class, ['--full'])->daily();



