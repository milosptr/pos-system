<?php

namespace Tests\Feature;

use App\Http\Controllers\EfaktureController;
use App\Http\Middleware\VerifyExternalApiKey;
use App\Models\ClientBankAccount;
use App\Models\ClientInvoice;
use App\Models\ClientInvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EfaktureImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyExternalApiKey::class);
    }

    /**
     * The invoice exactly as the owner's SEF exporter produced it.
     */
    private function jkspInvoice(): array
    {
        return [
            'broj_racuna' => '115870-2026',
            'dobavljac' => 'JKSP "Zaječar"',
            'dobavljac_pib' => '100578809',
            'datum_izdavanja' => '2026-09-03',
            'rok_dospeca' => '2026-09-30',
            'datum_prometa' => '2026-08-31',
            'model' => '97',
            'poziv_na_broj' => '41262209401158702026',
            'tekuci_racun' => '200-2407070102025-28',
            'ukupno_za_uplatu' => '6439.08',
            'stavke' => [
                ['rb' => '1', 'sifra' => '1', 'naziv' => '4 - Smeće trgovinski prostor', 'kolicina' => 136.0, 'jedinica_mere' => 'm²', 'jedinicna_cena' => 31.46, 'osnovica' => 4278.56, 'pdv_procenat' => 10.0],
                ['rb' => '2', 'sifra' => '1', 'naziv' => '5 - Smeće terase', 'kolicina' => 50.0, 'jedinica_mere' => 'm²', 'jedinicna_cena' => 31.46, 'osnovica' => 1573.0, 'pdv_procenat' => 10.0],
                ['rb' => '3', 'sifra' => '', 'naziv' => 'KAMATA', 'kolicina' => 1.0, 'jedinica_mere' => 'kom', 'jedinicna_cena' => 2.36, 'osnovica' => 2.36, 'pdv_procenat' => 0.0],
            ],
            'sef_id' => 468928984,
        ];
    }

    private function invoice(array $overrides = []): array
    {
        return array_merge($this->jkspInvoice(), $overrides);
    }

    private function item(array $overrides = []): array
    {
        return array_merge([
            'rb' => '1',
            'sifra' => '1',
            'naziv' => 'Stavka',
            'kolicina' => 1.0,
            'jedinica_mere' => 'kom',
            'jedinicna_cena' => 10.0,
            'osnovica' => 10.0,
            'pdv_procenat' => 20.0,
        ], $overrides);
    }

    private function import(array $invoices)
    {
        return $this->postJson('/api/efakture', $invoices);
    }

    public function test_the_owners_real_invoice_imports_exactly_as_sent()
    {
        $response = $this->import([$this->jkspInvoice()]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('summary.processed', 1);
        $response->assertJsonPath('summary.created', 1);
        $response->assertJsonPath('summary.items', 3);

        $invoice = ClientInvoice::first();
        $this->assertEquals(468928984, $invoice->sef_id);
        $this->assertEquals('115870-2026', $invoice->invoice_number);
        $this->assertEquals('41262209401158702026', $invoice->reference_number);
        $this->assertEquals('97', $invoice->payment_model);
        $this->assertEquals(6439.08, $invoice->amount);
        $this->assertEquals(ClientInvoice::STATUS_PENDING, $invoice->status);
        $this->assertEquals('2026-09-03', substr($invoice->issue_date, 0, 10));
        $this->assertEquals('2026-09-30', substr($invoice->payment_deadline, 0, 10));
        $this->assertEquals('2026-08-31', substr($invoice->transaction_date, 0, 10));

        $this->assertEquals('100578809', $invoice->supplier_pib);
        $this->assertEquals('200-2407070102025-28', $invoice->supplier_bank_account);

        $supplier = $invoice->clientAccount;
        $this->assertEquals('JKSP "Zaječar"', $supplier->name);
        $this->assertEquals('100578809', $supplier->pib);
        $this->assertEquals('200-2407070102025-28', $supplier->bank_account);

        $items = $invoice->items()->orderBy('position')->get();
        $this->assertCount(3, $items);

        $this->assertEquals('4 - Smeće trgovinski prostor', $items[0]->name);
        $this->assertEquals('1', $items[0]->sku);
        $this->assertEquals(136.0, $items[0]->quantity);
        $this->assertEquals('m²', $items[0]->unit);
        $this->assertEquals(31.46, $items[0]->unit_price);
        $this->assertEquals(4278.56, $items[0]->net_amount);
        $this->assertEquals(10.0, $items[0]->vat_rate);
        $this->assertNull($items[0]->unit_price_gross);

        $this->assertEquals('5 - Smeće terase', $items[1]->name);
        $this->assertEquals(50.0, $items[1]->quantity);
        $this->assertEquals(1573.0, $items[1]->net_amount);

        $this->assertEquals('KAMATA', $items[2]->name);
        $this->assertNull($items[2]->sku);
        $this->assertEquals('kom', $items[2]->unit);
        $this->assertEquals(2.36, $items[2]->unit_price);
        $this->assertEquals(0.0, $items[2]->vat_rate);
    }

    public function test_a_batch_of_forty_invoices_all_land()
    {
        $invoices = [];
        for ($i = 0; $i < 40; $i++) {
            $invoices[] = $this->invoice([
                'sef_id' => 700000000 + $i,
                'broj_racuna' => "BR-$i",
                'dobavljac' => 'Dobavljač ' . ($i % 5),
                'dobavljac_pib' => (string) (100000000 + ($i % 5)),
            ]);
        }

        $response = $this->import($invoices);

        $response->assertStatus(201);
        $response->assertJsonPath('summary.processed', 40);
        $response->assertJsonPath('summary.failed', []);
        $this->assertEquals(40, ClientInvoice::count());
        $this->assertEquals(120, ClientInvoiceItem::count());
        $this->assertEquals(5, ClientBankAccount::count());
    }

    public function test_a_resent_invoice_is_ignored_and_left_untouched()
    {
        $this->import([$this->jkspInvoice()]);

        $invoice = ClientInvoice::first();
        $invoice->update([
            'status' => ClientInvoice::STATUS_PAID,
            'processed_at' => '2026-09-10 12:00:00',
            'reference_number' => 'rucno ispravljeno',
        ]);

        $response = $this->import([$this->invoice(['ukupno_za_uplatu' => '999.00'])]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('summary.processed', 0);
        $response->assertJsonPath('summary.skipped', 1);

        $after = ClientInvoice::first();
        $this->assertEquals($invoice->id, $after->id);
        $this->assertEquals(6439.08, $after->amount);
        $this->assertEquals(ClientInvoice::STATUS_PAID, $after->status);
        $this->assertEquals('rucno ispravljeno', $after->reference_number);
        $this->assertEquals('2026-09-10 12:00:00', $after->processed_at);
        $this->assertEquals(1, ClientInvoice::count());
    }

    public function test_a_resend_does_not_touch_the_line_items()
    {
        $this->import([$this->jkspInvoice()]);
        $originalIds = ClientInvoiceItem::pluck('id')->sort()->values()->toArray();

        $this->import([$this->invoice(['stavke' => [$this->item(['naziv' => 'Nešto drugo'])]])]);

        $this->assertEquals(3, ClientInvoiceItem::count());
        $this->assertEquals($originalIds, ClientInvoiceItem::pluck('id')->sort()->values()->toArray());
        $this->assertEquals(0, ClientInvoiceItem::where('name', 'Nešto drugo')->count());
    }

    public function test_a_batch_mixes_new_invoices_with_ones_already_imported()
    {
        $this->import([$this->jkspInvoice()]);

        $response = $this->import([
            $this->jkspInvoice(),
            $this->invoice(['sef_id' => 468928985, 'broj_racuna' => '115871-2026']),
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('summary.processed', 1);
        $response->assertJsonPath('summary.skipped', 1);
        $this->assertEquals(2, ClientInvoice::count());
    }

    public function test_one_bad_invoice_does_not_block_the_others()
    {
        $response = $this->import([
            $this->invoice(['sef_id' => 1]),
            $this->invoice(['sef_id' => 2, 'broj_racuna' => 'LOSA', 'ukupno_za_uplatu' => 'abc']),
            $this->invoice(['sef_id' => 3]),
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('summary.processed', 2);
        $response->assertJsonPath('summary.invalid_rows', 1);
        $response->assertJsonPath('summary.failed.0.sef_id', 2);
        $response->assertJsonPath('summary.failed.0.broj_racuna', 'LOSA');

        $this->assertNotNull(ClientInvoice::where('sef_id', 1)->first());
        $this->assertNull(ClientInvoice::where('sef_id', 2)->first());
        $this->assertNotNull(ClientInvoice::where('sef_id', 3)->first());
    }

    public function test_an_invoice_without_a_sef_id_is_rejected()
    {
        $invoice = $this->jkspInvoice();
        unset($invoice['sef_id']);

        $response = $this->import([$invoice]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('summary.failed.0.error', 'sef_id is required');
        $this->assertEquals(0, ClientInvoice::count());
    }

    public function test_a_locally_formatted_amount_is_rejected_rather_than_guessed()
    {
        $response = $this->import([$this->invoice(['ukupno_za_uplatu' => '6.439,08'])]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('summary.failed.0.error', 'ukupno_za_uplatu is not a valid amount');
        $this->assertEquals(0, ClientInvoice::count());
    }

    public function test_an_impossible_date_is_rejected_rather_than_rolled_over()
    {
        $response = $this->import([$this->invoice(['datum_izdavanja' => '2026-13-45'])]);

        $response->assertStatus(200);
        $response->assertJsonPath('summary.failed.0.error', 'datum_izdavanja is not a valid date');
        $this->assertEquals(0, ClientInvoice::count());
    }

    public function test_an_unreadable_due_date_fails_instead_of_silently_using_the_issue_date()
    {
        $response = $this->import([$this->invoice(['rok_dospeca' => '30.09.2026'])]);

        $response->assertStatus(200);
        $response->assertJsonPath('summary.failed.0.error', 'rok_dospeca is not a valid date');
        $this->assertEquals(0, ClientInvoice::count());
    }

    public function test_missing_optional_dates_fall_back_to_the_issue_date()
    {
        $invoice = $this->jkspInvoice();
        unset($invoice['rok_dospeca'], $invoice['datum_prometa']);

        $this->import([$invoice])->assertStatus(201);

        $stored = ClientInvoice::first();
        $this->assertEquals('2026-09-03', substr($stored->payment_deadline, 0, 10));
        $this->assertEquals('2026-09-03', substr($stored->transaction_date, 0, 10));
    }

    public function test_a_credit_note_with_a_negative_total_is_imported()
    {
        $this->import([$this->invoice(['ukupno_za_uplatu' => '-1200.50'])])->assertStatus(201);

        $this->assertEquals(-1200.50, ClientInvoice::first()->amount);
    }

    public function test_the_gross_unit_price_is_stored_when_the_exporter_sends_it()
    {
        $this->import([$this->invoice([
            'stavke' => [$this->item(['jedinicna_cena' => 31.46, 'cena_sa_pdv' => 34.61, 'pdv_procenat' => 10.0])],
        ])])->assertStatus(201);

        $item = ClientInvoiceItem::first();
        $this->assertEquals(31.46, $item->unit_price);
        $this->assertEquals(34.61, $item->unit_price_gross);
    }

    public function test_an_invoice_in_another_currency_is_rejected()
    {
        $response = $this->import([$this->invoice(['valuta' => 'EUR'])]);

        $response->assertStatus(200);
        $response->assertJsonPath('summary.failed.0.error', 'valuta EUR is not supported, only RSD');
        $this->assertEquals(0, ClientInvoice::count());
    }

    public function test_a_placeholder_poziv_na_broj_falls_back_to_the_invoice_number()
    {
        $this->import([$this->invoice(['poziv_na_broj' => '--'])])->assertStatus(201);

        $this->assertEquals('115870-2026', ClientInvoice::first()->reference_number);
    }

    public function test_a_missing_poziv_na_broj_falls_back_to_the_invoice_number()
    {
        $invoice = $this->jkspInvoice();
        unset($invoice['poziv_na_broj']);

        $this->import([$invoice])->assertStatus(201);

        $this->assertEquals('115870-2026', ClientInvoice::first()->reference_number);
    }

    public function test_a_real_poziv_na_broj_is_kept()
    {
        $this->import([$this->jkspInvoice()])->assertStatus(201);

        $this->assertEquals('41262209401158702026', ClientInvoice::first()->reference_number);
    }

    public function test_an_empty_valuta_is_treated_as_rsd()
    {
        $this->import([$this->invoice(['valuta' => ''])])->assertStatus(201);

        $this->assertEquals(6439.08, ClientInvoice::first()->amount);
    }

    public function test_a_batch_where_everything_fails_still_reports_why()
    {
        $response = $this->import([
            $this->invoice(['sef_id' => 1, 'ukupno_za_uplatu' => 'abc']),
            $this->invoice(['sef_id' => 2, 'datum_izdavanja' => 'juce']),
        ]);

        // 2xx on purpose: his script prints summary.failed, and a 4xx would
        // hide it behind raise_for_status().
        $response->assertStatus(200);
        $response->assertJsonPath('success', false);
        $response->assertJsonPath('summary.processed', 0);
        $response->assertJsonPath('summary.invalid_rows', 2);
        $response->assertJsonPath('summary.failed.0.error', 'ukupno_za_uplatu is not a valid amount');
        $response->assertJsonPath('summary.failed.1.error', 'datum_izdavanja is not a valid date');
        $this->assertEquals(0, ClientInvoice::count());
    }

    public function test_the_supplier_is_matched_by_pib()
    {
        $existing = ClientBankAccount::create(['name' => 'Staro ime doo', 'pib' => '100578809']);

        $this->import([$this->jkspInvoice()])->assertStatus(201);

        $this->assertEquals(1, ClientBankAccount::count());
        $this->assertEquals($existing->id, ClientInvoice::first()->client_account);
    }

    public function test_the_supplier_is_matched_by_bank_account_whatever_the_formatting()
    {
        $existing = ClientBankAccount::create([
            'name' => 'Neko drugo ime',
            'bank_account' => '200 2407070102025 28',
        ]);

        $this->import([$this->invoice(['dobavljac_pib' => ''])])->assertStatus(201);

        $this->assertEquals(1, ClientBankAccount::count());
        $this->assertEquals($existing->id, ClientInvoice::first()->client_account);
    }

    public function test_the_invoice_keeps_the_pib_and_account_it_was_billed_from()
    {
        $this->import([$this->jkspInvoice()])->assertStatus(201);

        $supplier = ClientBankAccount::first();
        $supplier->update(['bank_account' => '160-9999999999999-11', 'pib' => '999999999']);

        $invoice = ClientInvoice::first();
        $this->assertEquals('100578809', $invoice->supplier_pib);
        $this->assertEquals('200-2407070102025-28', $invoice->supplier_bank_account);
    }

    public function test_a_supplier_matched_by_name_gets_its_pib_and_account_backfilled()
    {
        $handEntered = ClientBankAccount::create(['name' => 'jksp "zaječar"']);

        $this->import([$this->jkspInvoice()])->assertStatus(201);

        $this->assertEquals(1, ClientBankAccount::count());
        $this->assertEquals('100578809', $handEntered->fresh()->pib);
        $this->assertEquals('200-2407070102025-28', $handEntered->fresh()->bank_account);
    }

    public function test_a_supplier_with_a_different_pib_is_never_reused()
    {
        $other = ClientBankAccount::create([
            'name' => 'JKSP "Zaječar"',
            'pib' => '111111111',
            'bank_account' => '200-2407070102025-28',
        ]);

        $this->import([$this->jkspInvoice()])->assertStatus(201);

        $this->assertEquals(2, ClientBankAccount::count());
        $this->assertNotEquals($other->id, ClientInvoice::first()->client_account);
        $this->assertEquals('111111111', $other->fresh()->pib);
    }

    public function test_an_unknown_supplier_is_created_once_for_the_whole_batch()
    {
        $this->import([
            $this->invoice(['sef_id' => 11]),
            $this->invoice(['sef_id' => 12]),
            $this->invoice(['sef_id' => 13]),
        ])->assertStatus(201);

        $this->assertEquals(1, ClientBankAccount::count());
        $this->assertEquals(3, ClientInvoice::count());
    }

    public function test_overlong_text_is_clipped_instead_of_costing_the_invoice()
    {
        $this->import([$this->invoice([
            'dobavljac' => str_repeat('A', 300),
            'dobavljac_pib' => '',
            'tekuci_racun' => '',
            'broj_racuna' => str_repeat('9', 120),
            'stavke' => [$this->item(['naziv' => str_repeat('B', 400), 'sifra' => str_repeat('S', 100)])],
        ])])->assertStatus(201);

        $this->assertEquals(255, mb_strlen(ClientBankAccount::first()->name));
        $this->assertEquals(64, mb_strlen(ClientInvoice::first()->invoice_number));
        $this->assertEquals(255, mb_strlen(ClientInvoiceItem::first()->name));
        $this->assertEquals(64, mb_strlen(ClientInvoiceItem::first()->sku));
    }

    public function test_a_malformed_line_item_is_coerced_without_failing_the_invoice()
    {
        $this->import([$this->invoice([
            'stavke' => [
                ['rb' => 'x', 'sifra' => '', 'naziv' => '', 'kolicina' => 'puno', 'jedinica_mere' => '', 'jedinicna_cena' => null, 'osnovica' => 'abc', 'pdv_procenat' => null],
            ],
        ])])->assertStatus(201);

        $item = ClientInvoiceItem::first();
        $this->assertEquals('(bez naziva)', $item->name);
        $this->assertNull($item->position);
        $this->assertEquals(0.0, $item->quantity);
        $this->assertEquals(0.0, $item->unit_price);
        $this->assertEquals(0.0, $item->net_amount);
        $this->assertEquals(1, ClientInvoice::count());
    }

    public function test_an_invoice_without_line_items_is_imported()
    {
        $this->import([$this->invoice(['stavke' => []])])->assertStatus(201);

        $this->assertEquals(1, ClientInvoice::count());
        $this->assertEquals(0, ClientInvoiceItem::count());
    }

    public function test_an_empty_payload_is_rejected()
    {
        $this->import([])->assertStatus(422)->assertJsonPath('message', 'No data provided');

        $this->assertEquals(0, ClientInvoice::count());
    }

    public function test_an_oversized_payload_is_rejected_before_anything_is_written()
    {
        $invoices = array_fill(0, EfaktureController::MAX_ROWS + 1, $this->jkspInvoice());

        $this->import($invoices)->assertStatus(422);

        $this->assertEquals(0, ClientInvoice::count());
    }

    public function test_invoices_entered_by_hand_are_left_alone_by_the_import()
    {
        $supplier = ClientBankAccount::create(['name' => 'Rucni dobavljac']);
        $manual = ClientInvoice::create([
            'client_account' => $supplier->id,
            'reference_number' => '123',
            'payment_deadline' => '2026-09-30',
            'transaction_date' => '2026-08-31',
            'amount' => 100.0,
            'status' => ClientInvoice::STATUS_PENDING,
        ]);

        $this->import([$this->jkspInvoice()])->assertStatus(201);

        $this->assertEquals(2, ClientInvoice::count());
        $this->assertNull($manual->fresh()->sef_id);
        $this->assertEquals(100.0, $manual->fresh()->amount);
    }
}
