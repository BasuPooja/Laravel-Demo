<?php
use App\Http\Controllers\ProductController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {return view('welcome');});

// Route::get('/products', [ProductController::class, 'index']);
// Route::get('/products/{id}', [ProductController::class, 'show']);
// Route::post('/products', [ProductController::class, 'store']);
Route::get('/test', function () {
    return 'Test route working';
});
Route::resource('products', ProductController::class);
