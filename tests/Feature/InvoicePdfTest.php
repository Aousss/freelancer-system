<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\User;
use Database\Seeders\DemoFreelanceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoicePdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_invoice_pdf_streams_successfully(): void
    {
        $this->seed(DemoFreelanceSeeder::class);
        $invoice = Invoice::first();

        $this->actingAs(User::first());

        $response = $this->get("/invoices/{$invoice->id}/pdf");
        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
    }
}
