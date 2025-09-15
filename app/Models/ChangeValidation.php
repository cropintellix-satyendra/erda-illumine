<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeValidation extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['farmer_plot_uniqueid', 'farmer_uniqueId','plot_no','deleted_at','updated_at'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'change_validation';
    
    
    
    
}
