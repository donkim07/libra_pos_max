<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SalePrintController extends Controller
{
    public function print(Sale $sale)
    {
        $sale->load(['customer', 'store', 'saleItems.item', 'account', 'creator']);

        return view('sales.receipt', compact('sale'));
    }
}
