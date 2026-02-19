<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRule extends Model
{
    protected $fillable = ['name', 'rule_value', 'description'];

    public static function getValue($name, $default = '00:00:00')
    {
        $rule = self::where('name', $name)->first();
        return $rule ? $rule->rule_value : $default;
    }
}
