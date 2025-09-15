<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipeInstallationPipeImg extends Model
{
    use HasFactory;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pipe_installation_pipeimg';

    /**
     * The table associated with the model.
     *
     * @var string
     */
        // protected $table = 'farmer_plot_detail';

        protected $casts = [
            'date' => 'date',
        ];
    

    /**
    * Get the user that owns the phone.
    */
   public function farmerapproved()
   {
       return $this->belongsTo(FinalFarmer::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
   }


   
   public function season()
   {
       return $this->belongsTo(Season::class,'season','id');
   }

   
   /**
    * Get the reject reason.
    */
    public function reject_reason()
    {
        return $this->belongsTo(RejectModule::class,'reason_id','id');
    }

    public function Pipe_validations()
    {
        return $this->belongsTo(PipeImgValidation::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
    }
    /**
    * Get the reject detail.
    */
    public function reject_validation_detail()
    {
        return $this->hasOne(PipeImgValidation::class,'farmer_plot_uniqueid','farmer_plot_uniqueid')->where('status','Rejected')->latest();
    }

     /**
    * Get the reject detail.
    */
    public function approve_validation_detail()
    {
        return $this->hasOne(PipeImgValidation::class,'farmer_plot_uniqueid','farmer_plot_uniqueid')->where('status','Approved')->latest();
    }

     /**
    * Get the reject detail.
    */
    public function pipeinstallation()
    {
        return $this->hasOne(PipeInstallation::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
    }

    public function polygon()
    {
        return $this->hasOne(Polygon::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
    }
    

    public function surveyor()
    {
     return $this->belongsTo(User::class,'surveyor_id','id');
    }

    public function seasons(){
        return $this->hasOne(Season::class, 'id', 'season');
      }
    
}
