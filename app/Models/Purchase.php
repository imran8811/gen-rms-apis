<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = ['date', 'item_name', 'vendor_name', 'rate', 'quantity', 'unit', 'total_amount'];

    protected $casts = [
        'date'         => 'date:Y-m-d',
        'rate'         => 'decimal:2',
        'quantity'     => 'decimal:3',
        'total_amount' => 'decimal:2',
    ];
}
