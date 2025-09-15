<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class District extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['state_id','district','status'];
    
    /**
     * Get the state associated with the takuka.
     */
    public function state(){
      return $this->hasOne(State::class, 'id', 'state_id');
    }
}
