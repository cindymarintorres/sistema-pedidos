<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PedidoController;

Route::middleware('throttle:60,1')->group(function () {
    Route::apiResource('productos', ProductoController::class);
    Route::get('pedidos', [PedidoController::class, 'index']);
    Route::post('pedidos', [PedidoController::class, 'store'])->middleware('throttle:20,1');
    Route::get('pedidos/{id}', [PedidoController::class, 'show']);
    Route::get('health', fn() => response()->json(['status' => 'ok', 'php' => PHP_VERSION]));
});
