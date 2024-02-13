<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;



class WarehouseStatus extends Model
{
    use HasFactory;
    CONST TYPE_IN = 0;
    CONST TYPE_OUT = 1;

    CONST EXCEPTION_TYPE = 'WarehouseStatus::populateWarehouseFromSaleImport';

    protected $table = 'warehouse_status';
    protected $fillable = [
        'warehouse_id',
        'inventory_id',
        'quantity',
        'type', // 0 = in, 1 = out
        'date',
        'batch_id',
        'previous_status',
        'comment',
        'created_at',
    ];

    protected $attributes = [
      'previous_status' => 13,
    ];


    /**
     * @return BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * @return BelongsTo
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}
