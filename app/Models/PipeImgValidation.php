<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PipeImgValidation extends Model
{
    use HasFactory;


    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pipe_img_validation';

    /**
    * Get the reject reason.
    */
    public function ValidatorUserDetail()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
