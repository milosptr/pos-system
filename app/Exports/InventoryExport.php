<?php

namespace App\Exports;

use App\Models\Inventory;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use App\Http\Resources\InventoryExportResource;

class InventoryExport implements FromCollection, WithHeadings, ShouldAutoSize
{
  public function headings(): array
  {
      return [
         ['ID', 'Category', 'Name', 'Description', 'Active', 'Sold by', 'Price', 'SKU', 'QTY', 'Color', 'Order', 'Printing'],
      ];
  }

  /**
  * @return \Illuminate\Support\Collection
  */
  public function collection()
  {
      return InventoryExportResource::collection(Inventory::all());
  }
}
