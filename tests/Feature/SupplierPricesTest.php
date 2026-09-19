<?php

namespace Tests\Feature;

use App\Models\ClientBankAccount;
use App\Models\ClientInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SupplierPricesTest extends TestCase
{
    use RefreshDatabase;

    private function supplier(string $name = 'Pekara Trajković'): ClientBankAccount
    {
        return ClientBankAccount::create(['name' => $name]);
    }

    private function invoice(ClientBankAccount $supplier, string $issueDate, int $sefId, array $overrides = []): ClientInvoice
    {
        return ClientInvoice::create(array_merge([
            'sef_id' => $sefId,
            'invoice_number' => 'R-' . $sefId,
            'client_account' => $supplier->id,
            'issue_date' => $issueDate,
            'payment_deadline' => $issueDate,
            'transaction_date' => $issueDate,
            'amount' => 1000.0,
            'status' => ClientInvoice::STATUS_PENDING,
        ], $overrides));
    }

    private function item(ClientInvoice $invoice, array $overrides = []): void
    {
        $invoice->items()->create(array_merge([
            'position' => 1,
            'name' => 'HLEB 500g',
            'quantity' => 10,
            'unit' => 'kom',
            'unit_price' => 40.0,
            'net_amount' => 400.0,
            'vat_rate' => 10,
        ], $overrides));
    }

    private function prices(array $params): TestResponse
    {
        return $this->getJson('/api/supplier-prices?' . http_build_query($params));
    }

    public function test_the_prices_are_returned_newest_first_with_the_whole_history()
    {
        $supplier = $this->supplier();

        foreach ([['2026-03-01', 30.0], ['2026-04-01', 32.0], ['2026-05-01', 34.0], ['2026-06-01', 36.0], ['2026-07-01', 38.0], ['2026-08-01', 40.0], ['2026-09-01', 45.0]] as $index => [$date, $price]) {
            $this->item($this->invoice($supplier, $date, 100 + $index), ['unit_price' => $price]);
        }

        $response = $this->prices(['client_account' => $supplier->id]);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'articles');
        $this->assertEquals(
            [45.0, 40.0, 38.0, 36.0, 34.0, 32.0, 30.0],
            array_column($response->json('articles.0.entries'), 'unit_price')
        );
        $this->assertEquals('2026-09-01', $response->json('articles.0.entries.0.date'));
        $this->assertEquals(7, $response->json('articles.0.observations'));
        $this->assertEquals(12.5, $response->json('articles.0.change'));
        $this->assertTrue($response->json('articles.0.changed'));
    }

    public function test_invoices_at_the_same_price_are_one_level()
    {
        $supplier = $this->supplier();

        // Weekly invoices, one price change in the middle of them.
        foreach ([['2026-07-01', 60.10], ['2026-07-08', 60.10], ['2026-07-15', 60.10], ['2026-07-22', 62.50], ['2026-07-29', 62.50]] as $index => [$date, $price]) {
            $this->item($this->invoice($supplier, $date, 300 + $index), ['unit_price' => $price]);
        }

        $levels = $this->prices(['client_account' => $supplier->id])->json('articles.0.levels');

        $this->assertCount(2, $levels);
        $this->assertEquals(60.10, $levels[0]['unit_price']);
        $this->assertEquals('2026-07-01', $levels[0]['from']);
        $this->assertEquals('2026-07-15', $levels[0]['to']);
        $this->assertEquals(3, $levels[0]['invoices']);
        $this->assertNull($levels[0]['change']);

        $this->assertEquals(62.50, $levels[1]['unit_price']);
        $this->assertEquals('2026-07-22', $levels[1]['from']);
        $this->assertEquals(2, $levels[1]['invoices']);
        $this->assertEquals(4.0, $levels[1]['change']);
    }

    public function test_a_price_that_returns_to_an_earlier_level_is_a_new_level()
    {
        $supplier = $this->supplier();

        foreach ([['2026-07-01', 60.0], ['2026-08-01', 70.0], ['2026-09-01', 60.0]] as $index => [$date, $price]) {
            $this->item($this->invoice($supplier, $date, 400 + $index), ['unit_price' => $price]);
        }

        $levels = $this->prices(['client_account' => $supplier->id])->json('articles.0.levels');

        $this->assertCount(3, $levels);
        $this->assertEquals([60.0, 70.0, 60.0], array_column($levels, 'unit_price'));
    }

    public function test_the_total_change_is_measured_from_the_oldest_price()
    {
        $supplier = $this->supplier();

        $this->item($this->invoice($supplier, '2026-03-01', 101), ['unit_price' => 56.0]);
        $this->item($this->invoice($supplier, '2026-07-01', 102), ['unit_price' => 62.5]);
        $this->item($this->invoice($supplier, '2026-09-01', 103), ['unit_price' => 64.8]);

        $response = $this->prices(['client_account' => $supplier->id]);

        // The last step is small, the whole climb is not.
        $this->assertEquals(3.7, $response->json('articles.0.change'));
        $this->assertEquals(15.7, $response->json('articles.0.total_change'));
        $this->assertEquals(56.0, $response->json('articles.0.first.unit_price'));
        $this->assertEquals('2026-03-01', $response->json('articles.0.first.date'));
    }

    public function test_a_single_price_is_its_own_first_and_has_no_total_change()
    {
        $supplier = $this->supplier();

        $this->item($this->invoice($supplier, '2026-09-01', 101), ['unit_price' => 61.2]);

        $response = $this->prices(['client_account' => $supplier->id]);

        $this->assertEquals(1, $response->json('articles.0.observations'));
        $this->assertEquals(61.2, $response->json('articles.0.first.unit_price'));
        $response->assertJsonPath('articles.0.total_change', null);
    }

    public function test_the_history_sent_to_the_browser_is_capped()
    {
        $supplier = $this->supplier();
        $months = \Services\SupplierPriceService::HISTORY_LENGTH + 3;

        for ($month = 0; $month < $months; $month++) {
            $date = \Carbon\Carbon::create(2024, 1, 1)->addMonths($month)->format('Y-m-d');
            $this->item($this->invoice($supplier, $date, 200 + $month), ['unit_price' => 10.0 + $month]);
        }

        $response = $this->prices(['client_account' => $supplier->id]);

        $response->assertJsonCount(\Services\SupplierPriceService::HISTORY_LENGTH, 'articles.0.entries');
        $this->assertEquals($months, $response->json('articles.0.observations'));
        // The oldest price is still known even though it was not sent.
        $this->assertEquals(10.0, $response->json('articles.0.first.unit_price'));
    }

    public function test_case_and_spacing_differences_are_one_article_under_the_newest_spelling()
    {
        $supplier = $this->supplier();

        $this->item($this->invoice($supplier, '2026-08-01', 101), ['name' => 'hleb  500g']);
        $this->item($this->invoice($supplier, '2026-09-01', 102), ['name' => 'HLEB 500g', 'unit_price' => 45.0]);

        $response = $this->prices(['client_account' => $supplier->id]);

        $response->assertJsonCount(1, 'articles');
        $response->assertJsonPath('articles.0.name', 'HLEB 500g');
        $response->assertJsonCount(2, 'articles.0.entries');
    }

    public function test_a_different_unit_is_a_different_article_but_a_missing_one_is_not()
    {
        $supplier = $this->supplier();

        $this->item($this->invoice($supplier, '2026-08-01', 101), ['unit' => 'kg']);
        $this->item($this->invoice($supplier, '2026-08-02', 102), ['unit' => 'kom']);
        $this->item($this->invoice($supplier, '2026-08-03', 103), ['unit' => null]);
        $this->item($this->invoice($supplier, '2026-08-04', 104), ['unit' => '']);

        $response = $this->prices(['client_account' => $supplier->id]);

        $response->assertJsonCount(3, 'articles');
        $units = array_column($response->json('articles'), 'unit');
        sort($units);
        $this->assertEquals(['', 'kg', 'kom'], $units);
    }

    public function test_two_invoices_issued_the_same_day_are_ordered_by_sef_id()
    {
        $supplier = $this->supplier();

        // Created out of order on purpose: a backlog import writes the whole
        // batch inside one second, so created_at cannot separate them.
        $this->item($this->invoice($supplier, '2026-09-01', 205), ['unit_price' => 50.0]);
        $this->item($this->invoice($supplier, '2026-09-01', 204), ['unit_price' => 40.0]);

        $response = $this->prices(['client_account' => $supplier->id]);

        $this->assertEquals([50.0, 40.0], array_column($response->json('articles.0.entries'), 'unit_price'));
    }

    public function test_the_same_article_twice_on_one_invoice_yields_the_later_line()
    {
        $supplier = $this->supplier();
        $invoice = $this->invoice($supplier, '2026-09-01', 101);

        $this->item($invoice, ['position' => 1, 'unit_price' => 40.0]);
        $this->item($invoice, ['position' => 2, 'unit_price' => 45.0]);

        $response = $this->prices(['client_account' => $supplier->id]);

        $response->assertJsonCount(1, 'articles.0.entries');
        $this->assertEquals(45.0, $response->json('articles.0.entries.0.unit_price'));
    }

    public function test_credit_notes_and_cancelled_invoices_are_left_out()
    {
        $supplier = $this->supplier();

        $this->item($this->invoice($supplier, '2026-08-01', 101), ['unit_price' => 40.0]);
        $this->item($this->invoice($supplier, '2026-09-01', 102, ['amount' => -1000.0]), ['unit_price' => 99.0]);
        $this->item($this->invoice($supplier, '2026-09-02', 103, ['status' => ClientInvoice::STATUS_CANCELLED]), ['unit_price' => 88.0]);

        $response = $this->prices(['client_account' => $supplier->id]);

        $response->assertJsonCount(1, 'articles.0.entries');
        $this->assertEquals(40.0, $response->json('articles.0.entries.0.unit_price'));
    }

    public function test_an_earlier_price_of_zero_reports_no_percentage()
    {
        $supplier = $this->supplier();

        $this->item($this->invoice($supplier, '2026-08-01', 101), ['unit_price' => 0.0, 'unit_price_gross' => 33.0]);
        $this->item($this->invoice($supplier, '2026-09-01', 102), ['unit_price' => 45.0]);

        $response = $this->prices(['client_account' => $supplier->id]);

        $response->assertStatus(200);
        $response->assertJsonPath('articles.0.change', null);
        $response->assertJsonPath('articles.0.changed', true);
        $this->assertEquals(33.0, $response->json('articles.0.entries.1.unit_price_gross'));
    }

    public function test_a_vat_change_alone_is_not_a_price_change()
    {
        $supplier = $this->supplier();

        $this->item($this->invoice($supplier, '2026-08-01', 101), ['unit_price' => 40.0, 'unit_price_gross' => 44.0, 'vat_rate' => 10]);
        $this->item($this->invoice($supplier, '2026-09-01', 102), ['unit_price' => 40.0, 'unit_price_gross' => 48.0, 'vat_rate' => 20]);
        $this->item($this->invoice($supplier, '2026-09-02', 103), ['name' => 'KIFLA', 'unit_price' => 22.0]);
        $this->item($this->invoice($supplier, '2026-09-03', 104), ['name' => 'KIFLA', 'unit_price' => 25.0]);

        $all = $this->prices(['client_account' => $supplier->id]);
        $all->assertJsonCount(2, 'articles');

        $hleb = collect($all->json('articles'))->firstWhere('name', 'HLEB 500g');
        $this->assertFalse($hleb['changed']);
        $this->assertNull($hleb['change']);

        $changed = $this->prices(['client_account' => $supplier->id, 'changed' => 1]);
        $changed->assertJsonCount(1, 'articles');
        $changed->assertJsonPath('articles.0.name', 'KIFLA');
    }

    public function test_the_search_narrows_by_article_name_whatever_the_case()
    {
        $supplier = $this->supplier();

        $this->item($this->invoice($supplier, '2026-09-01', 101));
        $this->item($this->invoice($supplier, '2026-09-02', 102), ['name' => 'KIFLA']);

        $response = $this->prices(['client_account' => $supplier->id, 'search' => 'hleb']);

        $response->assertJsonCount(1, 'articles');
        $response->assertJsonPath('articles.0.name', 'HLEB 500g');
    }

    public function test_another_suppliers_items_are_never_returned()
    {
        $supplier = $this->supplier();
        $other = $this->supplier('JKSP "Zaječar"');

        $this->item($this->invoice($supplier, '2026-09-01', 101));
        $this->item($this->invoice($other, '2026-09-02', 102), ['name' => 'SMEĆE']);

        $response = $this->prices(['client_account' => $supplier->id]);

        $response->assertJsonCount(1, 'articles');
        $response->assertJsonPath('articles.0.name', 'HLEB 500g');
    }

    public function test_a_supplier_with_no_stored_prices_returns_an_empty_list()
    {
        $supplier = $this->supplier();

        $this->prices(['client_account' => $supplier->id])
            ->assertStatus(200)
            ->assertJsonCount(0, 'articles');
    }

    public function test_the_biggest_mover_comes_first_and_the_rest_fall_in_behind_it()
    {
        $supplier = $this->supplier();

        $this->item($this->invoice($supplier, '2026-08-01', 101), ['name' => 'VELIKI SKOK', 'unit_price' => 10.0]);
        $this->item($this->invoice($supplier, '2026-09-01', 102), ['name' => 'VELIKI SKOK', 'unit_price' => 20.0]);
        $this->item($this->invoice($supplier, '2026-08-01', 103), ['name' => 'MALI SKOK', 'unit_price' => 100.0]);
        $this->item($this->invoice($supplier, '2026-09-01', 104), ['name' => 'MALI SKOK', 'unit_price' => 102.0]);
        $this->item($this->invoice($supplier, '2026-08-01', 105), ['name' => 'ISTA CENA', 'unit_price' => 50.0]);
        $this->item($this->invoice($supplier, '2026-09-01', 106), ['name' => 'ISTA CENA', 'unit_price' => 50.0]);
        $this->item($this->invoice($supplier, '2026-09-01', 107), ['name' => 'PRVA CENA', 'unit_price' => 70.0]);

        $response = $this->prices(['client_account' => $supplier->id]);

        $this->assertEquals(
            ['VELIKI SKOK', 'MALI SKOK', 'ISTA CENA', 'PRVA CENA'],
            array_column($response->json('articles'), 'name')
        );
    }

    public function test_the_articles_can_be_sorted_by_name_instead()
    {
        $supplier = $this->supplier();

        $this->item($this->invoice($supplier, '2026-08-01', 101), ['name' => 'VELIKI SKOK', 'unit_price' => 10.0]);
        $this->item($this->invoice($supplier, '2026-09-01', 102), ['name' => 'VELIKI SKOK', 'unit_price' => 20.0]);
        $this->item($this->invoice($supplier, '2026-09-01', 103), ['name' => 'PRVA CENA', 'unit_price' => 70.0]);

        $response = $this->prices(['client_account' => $supplier->id, 'sort' => 'name']);

        $this->assertEquals(
            ['PRVA CENA', 'VELIKI SKOK'],
            array_column($response->json('articles'), 'name')
        );
    }

    public function test_an_unknown_sort_is_refused()
    {
        $supplier = $this->supplier();

        $this->prices(['client_account' => $supplier->id, 'sort' => 'cena'])->assertStatus(422);
    }

    public function test_a_missing_or_unknown_supplier_is_refused()
    {
        $this->prices([])->assertStatus(422);
        $this->prices(['client_account' => \Illuminate\Support\Str::uuid()->toString()])->assertStatus(422);
    }
}
