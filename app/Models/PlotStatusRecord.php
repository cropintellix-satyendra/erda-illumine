<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlotStatusRecord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'plots_status_record';

    protected $fillable = ['farmer_uniqueId','plot_no','farmer_plot_uniqueid','level','status','approve_comment'
                            ,'appr_timestamp','reject_comment','reject_timestamp','aprvd_recj_userid','reject_reason_id','onboarding_timestamp','onboarding_id',
                          'comment', 'timestamp', 'user_id','module'];

    /**
     * Get the user that approved or rejected the code.
     */
    public function UserApprovedRejected()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

     /**
     * Get the farmer associated with the plot.
     */
    public function Reasons(){
      return $this->belongsTo(RejectModule::class, 'reject_reason_id', 'id');
    }

    /**
     * Get the user that approved or rejected the code.
     */
    public function Surveyor()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }
}
