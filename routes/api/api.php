<?php

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// Route for obtaining a token for API
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

require __DIR__.'/authors.php';
require __DIR__.'/books.php';
require __DIR__.'/cartItems.php';
require __DIR__.'/favorites.php';
require __DIR__.'/genres.php';
require __DIR__.'/orderItems.php';
require __DIR__.'/orders.php';
require __DIR__.'/reviews.php';
