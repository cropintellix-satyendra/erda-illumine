<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTarget extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'date', 'module_name', 'count','module_id'];

    public function daily_target(){
        return $this->belongsTo(DailyTarget::class, 'module_id', 'id');
    }
}
