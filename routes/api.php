<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Route::post('/user', function () {
//     return 'post';
// });

// Route::post('/products', [ProductController::class, 'create']);

// Route::resource('products', ProductController::class);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::delete('/products/bulk_delete', [ProductController::class, 'bulkDelete']);
// Route::middleware('auth:sanctum')->group(function () {
//     Route::apiResource('products', ProductController::class);
// });

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('products', ProductController::class);
    // Route::get('/products', [ProductController::class, 'index']);
    // Route::get('/products/{id}', [ProductController::class, 'show']);
    // Route::post('/products', [ProductController::class, 'create']);
});