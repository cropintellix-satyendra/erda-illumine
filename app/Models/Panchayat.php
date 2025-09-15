<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Panchayat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['state_id','district_id', 'taluka_id', 'panchayat', 'status'];

    /**
     * Get the district associated with.
     */
    public function district_panchayat(){
      return $this->hasOne(District::class, 'id', 'district_id');
    }
    
    /**
     * Get the taluka associated with.
     */
    public function Talukalist(){
      return $this->hasOne(Taluka::class, 'id', 'taluka_id');
    }

    /**
     * Get the state associated with the village.
     */
    public function state(){
      return $this->hasOne(State::class, 'id', 'state_id');
    }
}
