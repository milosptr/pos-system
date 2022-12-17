<?php

namespace App\Models;

use App\Models\User;
use App\Models\Table;
use App\Models\Traits\Revenue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, Revenue;

    CONST STATUS_REFUNDED = 0;
    CONST STATUS_PAYED = 1;
    CONST STATUS_ON_THE_HOUSE = 2;

    protected $fillable = ['user_id', 'table_id', 'status', 'order', 'total', 'payment_type', 'note', 'refund_reason_id', 'discount'];
    protected $casts = ['order' => 'array'];
    public $timestamps = true;


    // RELATIONSHIPS

    public function table()
    {
      return $this->belongsTo(Table::class);
    }

    public function user()
    {
      return $this->belongsTo(User::class);
    }

    public function refundReason()
    {
      return $this->belongsTo(RefundReason::class);
    }
}
