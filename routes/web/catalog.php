<?php

use App\Http\Controllers\Web\Catalog\GetPublicBookController;
use Illuminate\Support\Facades\Route;

Route::get('/catalog/{book}', GetPublicBookController::class)->name('catalog.show')->whereNumber('book');
