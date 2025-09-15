<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CropdataDetail extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nursery','crop_season_lastyrs','crop_season_currentyrs','crop_variety_lastyrs','crop_variety_currentyrs','fertilizer_1_name','fertilizer_1_lastyrs'
                                ,'fertilizer_1_currentyrs','fertilizer_2_name','fertilizer_2_lastyrs','fertilizer_2_currentyrs','fertilizer_3_name','fertilizer_3_lastyrs','fertilizer_3_currentyrs'
                            ,'water_mng_lastyrs','water_mng_currentyrs','yeild_lastyrs','yeild_currentyrs'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cropdata_detail';
    
    
    /**
     * Get the user that owns the phone.
     */
    public function farmerapproved()
    {
        return $this->belongsTo(FinalFarmer::class,'farmer_plot_uniqueid','farmer_plot_uniqueid');
    }

    /**
     * Get the user that owns the phone.
     */
    public function cropdata()
    {
        return $this->hasOne(CropdataDetail::class,'id','farmer_cropdata_id');
    }
    
}
