<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    /**
     * Show HTML receipt (for print preview in browser)
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['details.product', 'user']);
        return view('receipts.transaction', compact('transaction'));
    }

    /**
     * Download as PDF
     */
    public function pdf(Transaction $transaction)
    {
        $transaction->load(['details.product', 'user']);

        $pdf = Pdf::loadView('receipts.transaction', compact('transaction'))
            ->setPaper([0, 0, 226.77, 800], 'portrait') // 80mm thermal width
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->stream('struk-' . $transaction->transaction_code . '.pdf');
    }
}
