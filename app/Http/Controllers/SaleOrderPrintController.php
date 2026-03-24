<?php

namespace App\Http\Controllers;

use App\Models\SaleOrder;

class SaleOrderPrintController extends Controller
{
    public function print(SaleOrder $saleOrder)
    {
        $saleOrder->load(['customer', 'store', 'saleOrderItems.item', 'account', 'creator']);

        return view('sale_orders.receipt', compact('saleOrder'));
    }
}

