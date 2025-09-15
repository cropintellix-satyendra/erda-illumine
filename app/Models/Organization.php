<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory, SoftDeletes;


    public function state()
    {
        return $this->hasOne(State::class,'id','state_id');
    }
}
