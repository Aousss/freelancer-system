<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InvoicePdfController extends Controller
{
    public function show(Request $request, Invoice $invoice): Response
    {
        $invoice->load(['client', 'project', 'items']);

        $pdf = Pdf::loadView('pdf.invoice-template', compact('invoice'))
            ->setPaper('a4', 'portrait');

        if ($request->query('download') === '1') {
            return $pdf->download("{$invoice->invoice_number}.pdf");
        }

        return $pdf->stream("{$invoice->invoice_number}.pdf");
    }
}
