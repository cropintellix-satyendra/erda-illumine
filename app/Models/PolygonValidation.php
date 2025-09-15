<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolygonValidation extends Model
{
    use HasFactory;

    protected $table = 'polygon_validations';

    protected $fillable = [
        'polygon_id'         ,    
            'farmer_uniqueId'    ,    
            'farmer_plot_uniqueid'  ,
            'plot_no'      ,           
            'status',                  
            'level'       ,            
            'surveyor_id'    ,             
            'comment'       ,          
            'reject_reason_id'   ,     
            'created_at'   ,            
            'updated_at'            
      ];


    /**
    * Get the reject reason.
    */
    public function ValidatorUserDetail()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function reject_reason()
    {
        return $this->belongsTo(RejectModule::class,'reject_reason_id','id');
    }


    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

//     public function reject_validation_detaill2()
// {
//     return $this->hasOne(PolygonValidation::class, 'farmer_plot_uniqueid', 'farmer_plot_uniqueid')
//                 ->where('status', 'Rejected')
//                 ->where('level', 'L-2-Validator')
//                 ->with('reject_reason') // eager load the rejection reason
//                 ->latest();
// }


}
