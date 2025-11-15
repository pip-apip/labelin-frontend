<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['api.auth'])
    ->name('dashboard');

Route::middleware(['api.auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Volt::route('paket', 'barang')->name('paket');
    Volt::route('paket/show/{id}', 'paket.barangDetail')->name('paket.show');
    Volt::route('barang-masuk', 'barangMasuk')->name('barang-masuk');
});

require __DIR__.'/auth.php';
