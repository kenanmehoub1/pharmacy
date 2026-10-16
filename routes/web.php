<?php

use App\Models\Sale;
use Illuminate\Support\Facades\Route;
use App\Models\Purchase;
Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/invoice/{sale}/print', function (Sale $sale) {
    $sale->load('customer', 'items.medicine');
    $autoPrint = request()->has('auto');
    $noPrint = request()->has('no-print');  // ← جديد
    return view('invoice-print', compact('sale', 'autoPrint', 'noPrint'));
})->name('invoice.print');



Route::get('/purchase/{purchase}/print', function (Purchase $purchase) {
    $purchase->load('supplier', 'items.medicine');
    return view('purchase-print', compact('purchase'));
})->name('purchase.print');



Route::get('/purchase/{purchase}/print', function (Purchase $purchase) {
    $purchase->load('supplier', 'items.medicine');
    return view('purchase-print', compact('purchase'));
})->name('purchase.print');