<?php

use App\Http\Controllers\Authors\CreateAuthorController;
use App\Http\Controllers\Authors\DeleteAuthorController;
use App\Http\Controllers\Authors\GetByIdAuthorController;
use App\Http\Controllers\Authors\GetListAuthorController;
use App\Http\Controllers\Authors\UpdateAuthorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::prefix('authors')->group(function (): void {
    Route::get('/', GetListAuthorController::class)->name('authors.list');
    Route::get('/{author}', GetByIdAuthorController::class)->name('authors.show');
    Route::post('/', CreateAuthorController::class)->name('authors.store');
    Route::put('/{author}', UpdateAuthorController::class)->name('authors.update');
    Route::delete('/{author}', DeleteAuthorController::class)->name('authors.delete');
});

require __DIR__.'/settings.php';
