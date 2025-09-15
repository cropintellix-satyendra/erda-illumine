<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ApprvFarmer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'final_farmers';

    /**
     * Get the user that owns the phone.
     */
    public function farmer()
    {
        return $this->belongsTo(Farmer::class,'farmer_id','id');
    }

    /**
     * Get the farmer associated with the plot.
     */
    public function ApprvFarmerPlotImages(){
      return $this->hasMany(FinalFarmerPlotImage::class, 'farmer_unique_id', 'farmer_uniqueId');
    }

    /**
     * Get the user that approved or rejected the code.
     */
    public function UserApprovedRejected()
    {
        return $this->belongsTo(User::class,'aprv_recj_userid','id');
    }

    /**
     * Get the user that approved or rejected the code.
     */
    public function FinalUserApprovedRejected()
    {
        return $this->belongsTo(User::class,'finalappr_userid','id');
    }

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
