<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalePrintController;
use App\Http\Controllers\SaleOrderPrintController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sales/{sale}/print', [SalePrintController::class, 'print'])
    ->name('sales.print')
    ->middleware('auth');

Route::get('/sale-orders/{saleOrder}/print', [SaleOrderPrintController::class, 'print'])
    ->name('sale-orders.print')
    ->middleware('auth');
