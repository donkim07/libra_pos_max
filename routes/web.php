<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SalePrintController;
use App\Http\Controllers\SaleOrderPrintController;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('landing.home');
})->name('landing.home');

Route::get('/features', fn () => view('landing.features'))->name('landing.features');
Route::get('/pricing', fn () => view('landing.pricing'))->name('landing.pricing');
Route::get('/contact', fn () => view('landing.contact'))->name('landing.contact');
Route::get('/about', fn () => view('landing.about'))->name('landing.about');
Route::post('/contact', function (Request $request) {
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'business_name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
        'phone' => ['required', 'string', 'max:50'],
        'plan_interest' => ['required', 'in:starter,business,enterprise'],
        'message' => ['required', 'string', 'max:5000'],
    ]);

    // TODO: Send contact email via Mail::to(...)->send(new ContactInquiryMailable(...));
    return back()->with('success', 'Thanks for reaching out. Our team will contact you shortly.');
})->name('landing.contact.store');

Route::get('/sales/{sale}/print', [SalePrintController::class, 'print'])
    ->name('sales.print')
    ->middleware('auth');

Route::get('/sale-orders/{saleOrder}/print', [SaleOrderPrintController::class, 'print'])
    ->name('sale-orders.print')
    ->middleware('auth');
