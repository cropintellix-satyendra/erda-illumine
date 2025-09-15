<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerBenefit extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'farmer_benefits';

     /**
     * Get the user that owns the phone.
     */
    public function farmer()
    {
        return $this->belongsTo(Farmer::class);
    }

    /**
     * Get the user that owns the phone.
     */
    public function farmerapproved()
    {
        return $this->belongsTo(FinalFarmer::class,'farmer_uniqueId','farmer_uniqueId');
    }

    public function userid()
    {
        return $this->belongsTo(User::class, 'l2_apprv_reject_user_id', 'id');
    }
}
