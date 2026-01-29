<?php
use App\Http\Controllers\ProductController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {return view('welcome');});

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products', [ProductController::class, 'create']);

Route::get('/test', function () {
    return 'Test route working';
});

Route::post('/test', function () {
    return 'post';
});

// Route::resource('products', ProductController::class);
