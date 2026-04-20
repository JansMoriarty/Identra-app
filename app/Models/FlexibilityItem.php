<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlexibilityItem extends Model
{
    use SoftDeletes;
    // Menentukan kolom mana saja yang boleh diisi secara massal
    protected $fillable = [
        'item_name',
        'description',
        'item_type',
        'value_power',
        'point_cost',
        'stock_limit',
        'is_available'
    ];

    /**
     * Relasi: Satu item bisa dimiliki oleh banyak user (lewat UserToken)
     */
    public function userTokens()
    {
        return $this->hasMany(UserToken::class, 'item_id');
    }
}