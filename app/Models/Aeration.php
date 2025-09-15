<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Aeration extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Get the farmer associated with the plot.
     */
    public function PipeInstallation(){
      return $this->belongsTo(PipeInstallation::class,'pipe_installation_id','id');
    }

     /**
   * Get the farmer associated with the plot.
   */
  public function AerationImages(){
    return $this->hasMany(AerationImage::class, 'farmer_uniqueId', 'farmer_uniqueId');
  }

    /**
     * Get the post that owns the comment.
     */
    public function users()
    {
        return $this->belongsTo(User::class,'surveyor_id','id');
    }

    public function season()
    {
        return $this->belongsTo(Season::class,'season','id');
    }

    /**
     * Get the user that owns the phone.
     */
    public function farmerapproved()
    {
        return $this->belongsTo(FinalFarmer::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
    }

     /**
    * Get the reject reason.
    */
    public function reject_reason()
    {
        return $this->belongsTo(RejectModule::class,'reason_id','id');
    }  

    /**
    * Get the reject detail.
    */
    public function reject_validation_detail()
    {
        return $this->hasOne(AerationValidation::class,'farmer_plot_uniqueid','farmer_plot_uniqueid')->where('status','Rejected')->latest();
    }  

    
  public function seasons(){
    return $this->hasOne(Season::class, 'id', 'season');
  }


  public function state(){
    return $this->hasOne(State::class, 'id', 'state_id');
  }

  public function district(){
    return $this->hasOne(District::class, 'id', 'district_id');
  }

  public function taluka(){
    return $this->hasOne(Taluka::class, 'id', 'taluka_id');
  }

  public function panchayat(){
    return $this->hasOne(Panchayat::class, 'id', 'panchayat_id');
  }

  public function village(){
    return $this->hasOne(Village::class, 'id', 'village_id');
  }

  public function surveyor(){
    return $this->belongsTo(User::class, 'surveyor_id', 'id');
  }
  
}
