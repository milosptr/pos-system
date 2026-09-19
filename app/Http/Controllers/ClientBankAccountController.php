<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use App\Models\ClientBankAccount;
use Services\EfaktureService;

class ClientBankAccountController extends Controller
{
    /**
     * Every Serbian PIB is nine digits, so anything else is a typo that would
     * quietly never match an invoice.
     */
    private const PIB_LENGTH = 9;

    public const STATUS_UPDATED = 'updated';
    public const STATUS_UNCHANGED = 'unchanged';
    public const STATUS_HAS_DIFFERENT_PIB = 'has_different_pib';
    public const STATUS_PIB_TAKEN = 'pib_taken';
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_AMBIGUOUS = 'ambiguous';
    public const STATUS_INVALID_PIB = 'invalid_pib';
    public const STATUS_INVALID_LINE = 'invalid_line';

    public function index()
    {
        return ClientBankAccount::all();
    }

    public function store(Request $request)
    {
        $clientBankAccount = ClientBankAccount::create($request->all());
        return $clientBankAccount;
    }

    public function assignPibs(Request $request)
    {
        $request->validate(['lines' => 'required|string']);

        $lines = preg_split('/\r\n|\r|\n/', $request->input('lines'));
        $suppliers = ClientBankAccount::all();
        $results = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            $results[] = $this->assignPibFromLine($line, $suppliers);
        }

        return response()->json([
            'success' => true,
            'applied' => collect($results)->where('status', self::STATUS_UPDATED)->count(),
            'results' => $results,
        ]);
    }

    private function assignPibFromLine(string $line, Collection $suppliers): array
    {
        $separator = strrpos($line, ':');

        if ($separator === false) {
            return ['line' => $line, 'status' => self::STATUS_INVALID_LINE];
        }

        $name = trim(substr($line, 0, $separator));
        $pib = trim(substr($line, $separator + 1));

        if ($name === '') {
            return ['line' => $line, 'status' => self::STATUS_INVALID_LINE];
        }

        if (!preg_match('/^\d{' . self::PIB_LENGTH . '}$/', $pib)) {
            return ['line' => $line, 'status' => self::STATUS_INVALID_PIB];
        }

        $matches = $this->matchByName($suppliers, $name);

        if ($matches->isEmpty()) {
            return ['line' => $line, 'status' => self::STATUS_NOT_FOUND];
        }

        if ($matches->count() > 1) {
            return [
                'line' => $line,
                'status' => self::STATUS_AMBIGUOUS,
                'candidates' => $matches->pluck('name')->values()->toArray(),
            ];
        }

        $supplier = $matches->first();
        $current = trim((string) $supplier->pib);

        if ($current === $pib) {
            return ['line' => $line, 'status' => self::STATUS_UNCHANGED, 'name' => $supplier->name];
        }

        if ($current !== '') {
            return [
                'line' => $line,
                'status' => self::STATUS_HAS_DIFFERENT_PIB,
                'name' => $supplier->name,
                'current_pib' => $current,
            ];
        }

        // Two suppliers on one PIB would leave the import picking between them
        // by whatever order the database returns, so refuse and let him merge.
        $takenBy = $suppliers->first(fn ($other) => trim((string) $other->pib) === $pib);

        if ($takenBy) {
            return [
                'line' => $line,
                'status' => self::STATUS_PIB_TAKEN,
                'name' => $supplier->name,
                'taken_by' => $takenBy->name,
            ];
        }

        $supplier->update(['pib' => $pib]);

        return ['line' => $line, 'status' => self::STATUS_UPDATED, 'name' => $supplier->name];
    }

    /**
     * Exact name first, then a partial, so "eps" can find "EPS Snabdevanje"
     * without a partial silently beating an exact one.
     */
    private function matchByName(Collection $suppliers, string $name): \Illuminate\Support\Collection
    {
        $needle = EfaktureService::normalizeName($name);

        $exact = $suppliers->filter(function ($supplier) use ($needle) {
            return EfaktureService::normalizeName($supplier->name) === $needle;
        });

        if ($exact->isNotEmpty()) {
            return $exact->values();
        }

        return $suppliers->filter(function ($supplier) use ($needle) {
            return mb_strpos(EfaktureService::normalizeName($supplier->name), $needle) !== false;
        })->values();
    }
}
