<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    // app/Models/LeaveRequest.php
    protected $fillable = [
        'guru_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'alasan',
        'lampiran_foto',
        'status',
        'approved_by'
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'guru_id', 'guru_id'); // Sesuaikan jika PK guru bukan uuid
    }
}
