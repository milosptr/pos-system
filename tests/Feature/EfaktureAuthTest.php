<?php

namespace Tests\Feature;

use App\Models\ClientInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kept apart from EfaktureImportTest because that class bypasses the API key
 * middleware for every one of its tests.
 */
class EfaktureAuthTest extends TestCase
{
    use RefreshDatabase;

    private const KEY = 'test-key';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.external_invoice.api_key' => self::KEY]);
    }

    private function invoice(): array
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
            'stavke' => [],
            'sef_id' => 468928984,
        ];
    }

    public function test_a_wrong_api_key_is_rejected()
    {
        $this->withHeader('X-API-Key', 'pogresan-kljuc')
            ->postJson('/api/efakture', [$this->invoice()])
            ->assertStatus(401)
            ->assertJsonPath('success', false);

        $this->assertEquals(0, ClientInvoice::count());
    }

    public function test_a_missing_api_key_is_rejected()
    {
        $this->postJson('/api/efakture', [$this->invoice()])
            ->assertStatus(401);

        $this->assertEquals(0, ClientInvoice::count());
    }

    public function test_the_correct_api_key_is_accepted()
    {
        $this->withHeader('X-API-Key', self::KEY)
            ->postJson('/api/efakture', [$this->invoice()])
            ->assertStatus(201);

        $this->assertEquals(1, ClientInvoice::count());
    }
}
