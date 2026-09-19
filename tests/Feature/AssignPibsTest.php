<?php

namespace Tests\Feature;

use App\Models\ClientBankAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignPibsTest extends TestCase
{
    use RefreshDatabase;

    private function assign(string $lines)
    {
        return $this->postJson('/api/bank-accounts/pibs', ['lines' => $lines]);
    }

    public function test_a_supplier_is_matched_by_its_exact_name_whatever_the_case()
    {
        $supplier = ClientBankAccount::create(['name' => 'EPS Snabdevanje']);

        $response = $this->assign('eps snabdevanje:100001378');

        $response->assertStatus(200);
        $response->assertJsonPath('applied', 1);
        $response->assertJsonPath('results.0.status', 'updated');
        $this->assertEquals('100001378', $supplier->fresh()->pib);
    }

    public function test_a_partial_name_matches_when_only_one_supplier_fits()
    {
        $supplier = ClientBankAccount::create(['name' => 'EPS Snabdevanje']);
        ClientBankAccount::create(['name' => 'Pekara Trajković']);

        $this->assign('eps:100001378')->assertJsonPath('results.0.status', 'updated');

        $this->assertEquals('100001378', $supplier->fresh()->pib);
    }

    public function test_an_exact_match_wins_over_a_partial_one()
    {
        $exact = ClientBankAccount::create(['name' => 'EPS']);
        $partial = ClientBankAccount::create(['name' => 'EPS Snabdevanje']);

        $this->assign('eps:100001378')->assertJsonPath('results.0.status', 'updated');

        $this->assertEquals('100001378', $exact->fresh()->pib);
        $this->assertNull($partial->fresh()->pib);
    }

    public function test_an_ambiguous_partial_name_changes_nothing_and_names_the_candidates()
    {
        $first = ClientBankAccount::create(['name' => 'EPS Snabdevanje']);
        $second = ClientBankAccount::create(['name' => 'EPS Distribucija']);

        $response = $this->assign('eps:100001378');

        $response->assertJsonPath('applied', 0);
        $response->assertJsonPath('results.0.status', 'ambiguous');
        $response->assertJsonCount(2, 'results.0.candidates');
        $this->assertNull($first->fresh()->pib);
        $this->assertNull($second->fresh()->pib);
    }

    public function test_an_unknown_supplier_is_reported()
    {
        $this->assign('nepoznat:100001378')
            ->assertJsonPath('applied', 0)
            ->assertJsonPath('results.0.status', 'not_found');
    }

    public function test_a_pib_that_is_not_nine_digits_is_refused()
    {
        $supplier = ClientBankAccount::create(['name' => 'EPS']);

        $response = $this->assign("eps:12345678\neps:abcdefghi\neps:1000013789");

        $response->assertJsonPath('applied', 0);
        $response->assertJsonPath('results.0.status', 'invalid_pib');
        $response->assertJsonPath('results.1.status', 'invalid_pib');
        $response->assertJsonPath('results.2.status', 'invalid_pib');
        $this->assertNull($supplier->fresh()->pib);
    }

    public function test_a_line_without_a_colon_is_refused()
    {
        $this->assign('eps 100001378')->assertJsonPath('results.0.status', 'invalid_line');
    }

    public function test_a_supplier_that_already_has_a_different_pib_is_left_alone()
    {
        $supplier = ClientBankAccount::create(['name' => 'EPS', 'pib' => '111111111']);

        $response = $this->assign('eps:100001378');

        $response->assertJsonPath('applied', 0);
        $response->assertJsonPath('results.0.status', 'has_different_pib');
        $response->assertJsonPath('results.0.current_pib', '111111111');
        $this->assertEquals('111111111', $supplier->fresh()->pib);
    }

    public function test_the_same_pib_again_is_left_alone()
    {
        ClientBankAccount::create(['name' => 'EPS', 'pib' => '100001378']);

        $this->assign('eps:100001378')
            ->assertJsonPath('applied', 0)
            ->assertJsonPath('results.0.status', 'unchanged');
    }

    public function test_several_lines_are_applied_and_blank_ones_ignored()
    {
        $eps = ClientBankAccount::create(['name' => 'EPS']);
        $jksp = ClientBankAccount::create(['name' => 'JKSP "Zaječar"']);

        $response = $this->assign("eps:100001378\n\n  \njksp:100578809\n");

        $response->assertJsonPath('applied', 2);
        $response->assertJsonCount(2, 'results');
        $this->assertEquals('100001378', $eps->fresh()->pib);
        $this->assertEquals('100578809', $jksp->fresh()->pib);
    }

    public function test_a_pib_another_supplier_already_holds_is_refused()
    {
        ClientBankAccount::create(['name' => 'RADISA MLADENOVIC PREDUZETNIK MATALJ NEGOTIN', 'pib' => '100565511']);
        $handEntered = ClientBankAccount::create(['name' => 'MATALJ']);

        $response = $this->assign('MATALJ:100565511');

        // Copying it would leave the import choosing between two rows on the
        // same PIB by whatever order the database returns.
        $response->assertJsonPath('applied', 0);
        $response->assertJsonPath('results.0.status', 'pib_taken');
        $response->assertJsonPath('results.0.taken_by', 'RADISA MLADENOVIC PREDUZETNIK MATALJ NEGOTIN');
        $this->assertNull($handEntered->fresh()->pib);
    }

    public function test_lines_must_be_sent_as_a_string()
    {
        $this->postJson('/api/bank-accounts/pibs', ['lines' => ['eps:100001378']])
            ->assertStatus(422);
    }

    public function test_a_name_containing_a_colon_splits_on_the_last_one()
    {
        $supplier = ClientBankAccount::create(['name' => 'Firma: Beograd']);

        $this->assign('firma: beograd:100001378')->assertJsonPath('results.0.status', 'updated');

        $this->assertEquals('100001378', $supplier->fresh()->pib);
    }
}
