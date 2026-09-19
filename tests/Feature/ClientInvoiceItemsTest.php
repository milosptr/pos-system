<?php

namespace Tests\Feature;

use App\Models\ClientBankAccount;
use App\Models\ClientInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientInvoiceItemsTest extends TestCase
{
    use RefreshDatabase;

    private function invoiceWithItems(): ClientInvoice
    {
        $supplier = ClientBankAccount::create(['name' => 'JKSP "Zaječar"']);
        $invoice = ClientInvoice::create([
            'client_account' => $supplier->id,
            'payment_deadline' => '2026-09-30',
            'transaction_date' => '2026-08-31',
            'amount' => 6439.08,
            'status' => ClientInvoice::STATUS_PENDING,
        ]);

        $invoice->items()->create(['position' => 2, 'name' => 'Smeće terase', 'quantity' => 50, 'unit' => 'm²', 'unit_price' => 31.46, 'unit_price_gross' => 34.61, 'net_amount' => 1573.0, 'vat_rate' => 10]);
        $invoice->items()->create(['position' => 1, 'name' => 'Smeće trgovinski prostor', 'quantity' => 136, 'unit' => 'm²', 'unit_price' => 31.46, 'net_amount' => 4278.56, 'vat_rate' => 10]);

        return $invoice;
    }

    public function test_the_items_of_an_invoice_are_returned_in_order()
    {
        $invoice = $this->invoiceWithItems();

        $response = $this->getJson("/api/bank-invoices/{$invoice->id}/items");

        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonPath('0.name', 'Smeće trgovinski prostor');
        $response->assertJsonPath('1.name', 'Smeće terase');
        $response->assertJsonPath('1.unit_price_gross', 34.61);
        $response->assertJsonPath('0.unit_price_gross', null);
    }

    public function test_an_invoice_with_no_items_returns_an_empty_list()
    {
        $supplier = ClientBankAccount::create(['name' => 'Rucni dobavljac']);
        $invoice = ClientInvoice::create([
            'client_account' => $supplier->id,
            'payment_deadline' => '2026-09-30',
            'transaction_date' => '2026-09-01',
            'amount' => 100.0,
            'status' => ClientInvoice::STATUS_PENDING,
        ]);

        $this->getJson("/api/bank-invoices/{$invoice->id}/items")
            ->assertStatus(200)
            ->assertJsonCount(0);
    }

    public function test_an_unknown_invoice_returns_not_found()
    {
        $this->getJson('/api/bank-invoices/' . \Illuminate\Support\Str::uuid() . '/items')
            ->assertStatus(404);
    }
}
