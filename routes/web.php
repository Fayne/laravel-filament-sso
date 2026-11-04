<?php

use Fayne\Sso\Http\Controllers\SsoController;
use Illuminate\Support\Facades\Route;

Route::get('{driver}/redirect', [SsoController::class, 'redirect'])->name('filament-sso.redirect');
Route::match(['get', 'post'], '{driver}/callback', [SsoController::class, 'callback'])->name('filament-sso.callback');
Route::view('login', 'filament-sso::login')->name('filament-sso.login');
