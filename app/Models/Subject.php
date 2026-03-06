<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'code'];

    // Relasi ke Jadwal (Satu pelajaran bisa punya banyak jadwal)
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}