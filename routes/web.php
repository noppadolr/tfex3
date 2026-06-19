<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

// Route::middleware(['auth', 'verified'])->group(function () {
//    Route::view('dashboard', 'dashboard')->name('dashboard');
// });

require __DIR__.'/settings.php';

Route::middleware('auth')->group(function () {
    Route::livewire('dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('/trades', 'pages::-trades.index')->name('trades.index');
    Route::livewire('/trades/create', 'pages::-trades.create')->name('trades.create');
    Route::livewire('/trades/{trade}', 'pages::-trades.show')->name('trades.show');
    Route::livewire('/trades/{trade/edit', 'pages::-trades.edit')->name('trades.edit');

    Route::livewire('/contracts', 'pages::-contracts.index')->name('contracts-pages::.index');

    Route::livewire('/commission-rates', 'pages::commission-rates.index')->name('commission-rates.index');

    Route::livewire('/reports/performance','pages::reports.performance')->name('reports.performance');

    Route::livewire('/reports/commission','pages::reports.commission')->name('reports.commission');
});
