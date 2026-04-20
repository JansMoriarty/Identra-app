<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $fillable = [
        'user_id',
        'item_id',
        'status',
        'used_at_attendance_id',
        'used_at',
        'expires_at'
    ];

    // Relasi balik ke Item
    // Relasi balik ke Item
    public function item()
    {
        // Tambahkan withTrashed() supaya voucher yang sudah di-soft delete
        // tetap muncul di riwayat "My Voucher" milik user.
        return $this->belongsTo(FlexibilityItem::class, 'item_id')->withTrashed();
    }

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
