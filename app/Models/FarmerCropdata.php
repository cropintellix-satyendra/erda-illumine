<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerCropdata extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['farmer_id', 'farmer_uniqueId','plot_no', 'area_in_acers','season','dt_irrigation_last','crop_variety','dt_ploughing','dt_transplanting','surveyor_id','surveyor_name' ,
                        'surveyor_email','surveyor_mobile'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'farmer_cropdata';


    /**
     * Get the user that owns the phone.
     */
    public function farmerapproved()
    {
        return $this->belongsTo(FinalFarmer::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
    }

    public function farmerplot_details()
    {
        return $this->belongsTo(FarmerPlot::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
    }

    public function PlotCropDetails()
    {
        return $this->belongsTo(CropdataDetail::class,'id','farmer_cropdata_id');
    }

    // public function cropdata()
    // {
    //     return $this->hasOne(CropdataDetail::class,'id','farmer_cropdata_id');
    // }  
    
    public function pipe_images()
    {
        return $this->belongsTo(PipeInstallationPipeImg::class,'farmer_plot_uniqueid','farmer_uniqueId');
    }

    public function usertag()
    {
        return $this->belongsTo(User::class,'l2_apprv_reject_user_id', 'id');
    }
   
    public function aeration_data()
    {
        return $this->belongsTo(Aeration::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
    }  


}
