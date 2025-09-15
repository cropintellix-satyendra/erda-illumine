<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Polygon extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'farmer_uniqueId',
        "farmer_plot_uniqueid",
        "plot_no",
        "latitude",
        "longitude",
        "area_units",
        "plot_area",
        "surveyor_id",
        "ranges",
       "polygon_date_time",
        'status',
        'financial_year',
        'season',
        'final_status'
      ];

    //   public function reject_validation_detaill2()
    //   {
    //       return $this->hasOne(PolygonValidation::class,'farmer_plot_uniqueid','farmer_plot_uniqueid')->where('status','Rejected')->where('level','L-2-Validator')->latest();
    //   }

    public function reject_validation_detaill2()
{
    return $this->hasOne(PolygonValidation::class, 'farmer_plot_uniqueid', 'farmer_plot_uniqueid')
                ->where('status', 'Rejected')
                ->where('level', 'L-2-Validator')
                ->with('reject_reason') // eager load the rejection reason
                ->latest();
}

    //   public function reject_reason()
    //   {
    //       return $this->belongsTo(RejectModule::class,'reason_id','id');
    //   }

      public function farmerapproved()
      {
          return $this->belongsTo(FinalFarmer::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
      }

      public function FormSubmitBy()
      {
          return $this->belongsTo(User::class,'surveyor_id','id');
      }
      
      public function PolygonValidation()
      {
          return $this->belongsTo(PolygonValidation::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
      }

      public function polygon()
      {
          return $this->hasOne(Polygon::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
      }

      public function pipe_image_latest()
      {
          return $this->hasOne(PipeInstallationPipeImg::class,'farmer_plot_uniqueid','farmer_plot_uniqueid')->latest();
      }

      public function plot_detail()
   {
       return $this->hasOne(FinalFarmer::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
   }

   public function seasons()
   {
       return $this->belongsTo(Season::class,'season','id');
   }
   
   

       public function surveyor()
   {
    return $this->belongsTo(User::class,'surveyor_id','id');
   }

}
