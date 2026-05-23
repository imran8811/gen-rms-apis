<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    protected $fillable = ['name', 'category', 'unit', 'current_stock', 'min_stock', 'cost_per_unit', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->current_stock <= 0) return 'Out';
        if ($this->current_stock < $this->min_stock * 0.5) return 'Critical';
        if ($this->current_stock < $this->min_stock) return 'Low';
        return 'OK';
    }
}
