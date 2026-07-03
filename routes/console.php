<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('google-sheet:import')
    ->everyTenMinutes()
    ->withoutOverlapping();

Schedule::command('india-mart-sheet:import')
    ->everyFifteenMinutes()
    ->withoutOverlapping();
