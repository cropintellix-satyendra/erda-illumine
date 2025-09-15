<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerFarmDetails extends Model
{
    use HasFactory;

 protected $fillable = [
    'farmer_uniqueId',
    'irigation_source',
    'struble_burning',
    'double_paddy_status',
    'soil_type',
    'variety',
    'flooding_type',
    'proper_drainage',
    'awd_previous',
    'awd_previous_no',
    'community_benefit',
    // Include other existing fillable fields here
  ];
     public function variety(){
      return $this->hasOne(Cropvariety::class, 'id', 'variety');
    }


}
