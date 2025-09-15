<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Taluka extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['district_id',"state_id",'taluka','status'];

    /**
     * Get the state associated with the takuka.
     */
    public function state(){
      return $this->hasOne(State::class, 'id', 'state_id');
    }
    
    /**
     * Get the district associated with.
     */
    public function district(){
      return $this->hasOne(District::class, 'id', 'district_id');
    }
}
