<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use HasFactory, SoftDeletes;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'states';

    protected $fillable = ['country_id','name','status'];
    
     /**
     * Get the phone associated with the user.
     */
    public function countryname()
    {
        return $this->hasOne(Country::class,'id','country_id');
    }
}
