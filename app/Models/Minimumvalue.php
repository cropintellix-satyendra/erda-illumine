<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Minimumvalue extends Model
{
    use HasFactory, SoftDeletes;
    
    /**
     * Get the state associated with the village.
     */
    public function state(){
      return $this->hasOne(State::class, 'id', 'state_id');
    }
}
