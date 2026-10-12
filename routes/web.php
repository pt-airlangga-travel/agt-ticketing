<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard/login');
});

Route::get('/dashboard', function () {
    return redirect('/dashboard/ticketing');
})->name('filament.dashboard.pages.dashboard');
