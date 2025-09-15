<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Farmer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the farmer associated with the plot.
     */
    public function FarmerPlot(){
      return $this->hasMany(FarmerPlot::class);
    }

    /**
     * Get the farmer associated with the plot.
     */
    public function state(){
      return $this->hasOne(State::class, 'id', 'state_id');
    }

    /**
     * Get the farmer associated with the plot.
     */
    public function organization(){
      return $this->hasOne(Company::class, 'id', 'organization_id');
    }

    /**
     * Get the post that owns the comment.
     */
    public function users()
    {
        return $this->belongsTo(User::class,'surveyor_id','id');
    }

    /**
     * Get the farmer cropdata.
     */
    public function CropData()
    {
        return $this->hasMany(FarmerCropdata::class,'farmer_id','id')->orderBy('plot_no','asc');
    }

     /**
     * Get the farmer benefits data.
     */
    public function BenefitsData()
    {
        return $this->hasMany(FarmerBenefit::class,'farmer_id','id');
    }

}
