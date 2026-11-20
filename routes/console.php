<?php


use App\Console\Commands\Usrbb\UsrbbSynchronizer;
use Illuminate\Support\Facades\Schedule;



Schedule::command(UsrbbSynchronizer::class)->everyMinute();
//Schedule::command(UsrbbSynchronizer::class, ['--full'])->daily();



