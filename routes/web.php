<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/authors.php';
require __DIR__.'/books.php';
require __DIR__.'/cartItems.php';
require __DIR__.'/favorites.php';
require __DIR__.'/genres.php';

require __DIR__.'/settings.php';
