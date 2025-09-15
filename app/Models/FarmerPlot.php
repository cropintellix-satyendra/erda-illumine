<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerPlot extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'farmer_plot_detail';

    protected $fillable = ['farmer_id','farmer_uniqueId','plot_no','area_in_acers','land_ownership','actual_owner_name','affidavit_tnc'
                            ,'sign_affidavit','sign_affidavit_date','survey_no','status','reason_id','reject_comment','reject_timestamp',
                            'appr_timestamp','check_update','deleted_at','created_at','updated_at','aprv_recj_userid','area_acre_awd','area_other_awd','area_in_other'];

    /**
     * Get the user that owns the phone.
     */
    public function farmer()
    {
        return $this->belongsTo(Farmer::class,'farmer_id','id');
    }

    public function final_farmers()
    {
        return $this->belongsTo(FinalFarmer::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
    }

    public function final_farmerno()
    {
        return $this->hasOne(FinalFarmer::class,'farmer_uniqueId','farmer_uniqueId');
    }

    /**
     * Get the farmer associated with the plot.
     */
    // public function FarmerPlotImages(){
    //   return $this->hasMany(FarmerPlotImage::class, 'farmer_unique_id', 'farmer_uniqueId');
    // }
    public function FarmerPlotImages(){
      return $this->hasMany(FarmerPlotImage::class, 'farmer_unique_id', 'farmer_uniqueId');
    }

    public function final_farmer_plot_image()
    {
        return $this->belongsTo(FinalFarmerPlotImage::class,'farmer_uniqueId','farmer_unique_id');
    }

    /**
     * Get the farmer associated with the plot.
     */
    public function Reasons(){
      return $this->belongsTo(RejectModule::class, 'reason_id', 'id');
    }

    /**
     * Get the farmer associated with the plot.
     */
    public function FarmerBenefit(){
      return $this->hasMany(FarmerBenefit::class, 'farmer_uniqueId', 'farmer_uniqueId');
    }

    /**
     * Get the farmer cropdata.
     */
    public function CropData()
    {
        return $this->hasMany(FarmerCropdata::class,'farmer_id','farmer_id')->orderBy('plot_no','asc');
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
        return $this->belongsTo(User::class,'finalreject_userid','id');
    }


    public function FinalUserApproved()
    {
        return $this->belongsTo(User::class,'L2_appr_userid','id');
    }
  
    public function FinalUserRejected()
    {
        return $this->belongsTo(User::class,'L2_reject_userid','id');
    }
    
}
