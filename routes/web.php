<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'productos']);
Route::get('/productos', [PageController::class, 'productos']);
Route::get('/pedidos', [PageController::class, 'pedidos']);
Route::get('/pedidos/{id}', [PageController::class, 'pedidoDetalle'])->where('id', '[0-9]+');
Route::get('/checkout', [PageController::class, 'checkout']);
