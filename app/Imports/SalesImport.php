<?php

namespace App\Imports;

use App\Models\Inventory;
use App\Models\Sales;
use App\Models\Stockroom;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalesImport implements ToCollection, WithHeadingRow
{
    private $date;
    private $saleDetails;
    public function __construct($date, $saleDetails)
    {
        $this->date = $date;
        $this->saleDetails = $saleDetails;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if($row['artikal'] === null || $row['sifra'] === null) {
                continue;
            }
            $sku = Inventory::skuMask($row['sifra']);
            $inventory = Inventory::where('sku', $sku)->first();
            if($inventory) {
                $price = (int) $row['ukupno'] / (int) $row['kolicina'];
                Sales::create([
                  'inventory_id' => $inventory->id,
                  'sku' => $inventory->sku,
                  'category_id' => $inventory->category->id,
                  'category_name' => $inventory->category->name,
                  'name' => $row['artikal'],
                  'qty' => $row['kolicina'],
                  'price' => $price,
                  'total' => $row['ukupno'],
                  'type' => Sales::TYPE_EBAR,
                  'status' => Sales::STATUS_ACTIVE,
                  'batch_id' => $this->saleDetails->id,
                  'created_at' => $this->date,
                ]);
            }
        }
    }
}
