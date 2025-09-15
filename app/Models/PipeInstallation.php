<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipeInstallation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
        // protected $table = 'farmer_plot_detail';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      "farmer_id","farmer_uniqueId","plot_no","latitude",
      "longitude","state","district","taluka","village",
      "khasara_no","acers_units","plot_area","no_pipe_req",
      "no_pipe_avl","installing_pipe","surveyor_id","surveyor_name",
      "surveyor_email","surveyor_mobile","ranges","farmer_plot_uniqueid","installing_pipe","no_pipe_req","no_pipe_avl","date_survey","date_time","area_in_acers","polygon_date_time",
      "apprv_reject_user_id",'apprv_reject_user_id','status','reason_id','delete_polygon','delete_polygon','financial_year','season','deleted_at',
    ];

    /**
    * Get the farmer benefits data.
    */
   public function AerationData()
   {
       return $this->hasMany(Aeration::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
   }

   public function season()
   {
       return $this->belongsTo(Season::class,'season','id');
   }


   /**
    * Get the farmer benefits data.
    */
    public function pipe_image()
    {
        return $this->hasMany(PipeInstallationPipeImg::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
    }

    public function farmerplot_details()
    {
        return $this->belongsTo(FarmerPlot::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
    }

    public function validator()
   {
     return $this->belongsTo(User::class,'l2_apprv_reject_user_id','id');
   }
    /**
    * Get the farmer benefits data.
    */
    public function pipe_image_latest()
    {
        return $this->hasOne(PipeInstallationPipeImg::class,'farmer_plot_uniqueid','farmer_plot_uniqueid')->latest();
    }

   /**
    * Get the user that owns the phone.
    */
   public function farmerapproved()
   {
       return $this->belongsTo(FinalFarmer::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
   }

   
   public function plot_detail()
   {
       return $this->hasOne(FinalFarmer::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
   }
   
   /**
    * Get the user that owns the phone.
    */
   public function FormSubmitBy()
   {
     return $this->belongsTo(User::class,'surveyor_id','id');
   }

   /**
    * Get the reject detail.
    */
    public function reject_validation_detail()
    {
        return $this->hasOne(PipeImgValidation::class,'farmer_plot_uniqueid','farmer_plot_uniqueid')->where('status','Rejected')->where('level','L-1-Validator')->latest();
    }

    /**
    * Get the reject detail.
    */
    public function reject_validation_detaill2()
    {
        return $this->hasOne(PipeImgValidation::class,'farmer_plot_uniqueid','farmer_plot_uniqueid')->where('status','Rejected')->where('level','L-2-Validator')->latest();
    }


    /**
    * Get the reject reason.
    */
    public function reject_reason()
    {
        return $this->belongsTo(RejectModule::class,'reason_id','id');
    }
}
