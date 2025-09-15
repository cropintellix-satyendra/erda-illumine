<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CropDataValidation extends Model
{
    use HasFactory;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cropdata_validation';

   
    /**
    * Get the reject reason.
    */
    public function ValidatorUserDetail()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

}
