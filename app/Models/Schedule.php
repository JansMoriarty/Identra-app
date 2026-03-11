<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = ['user_id', 'classroom_id', 'subject_id', 'day', 'start_time', 'end_time'];

    public function guru()
    {
        // Karena kita pakai user_id (standar), tidak perlu mendefinisikan key tambahan
        return $this->belongsTo(User::class, 'user_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function user() // Ubah dari guru ke user
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
