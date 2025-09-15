<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaValue extends Model
{
    use HasFactory;

    protected $fillable =[
        'state_id',
        'area_value_name',
        'status',
        'type'
    ];
}
