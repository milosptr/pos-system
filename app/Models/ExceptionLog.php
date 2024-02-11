<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExceptionLog extends Model
{
    use HasFactory;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'exceptions';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'type', 'message', 'stack_trace', 'file', 'method', 'payload', 'status_code'
  ];

  /**
   * Indicates if the model should be timestamped.
   *
   * @var bool
   */
  public $timestamps = true;
}
