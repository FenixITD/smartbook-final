<?php

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Incorrect login or password'], 401);
    }

    return response()->json([
        'token' => $user->createToken('api-token')->plainTextToken
    ]);
});

require __DIR__.'/api/authors.php';
require __DIR__.'/api/books.php';
require __DIR__.'/api/cartItems.php';
require __DIR__.'/api/favorites.php';
require __DIR__.'/api/genres.php';
require __DIR__.'/api/orderItems.php';
require __DIR__.'/api/orders.php';
require __DIR__.'/api/reviews.php';
