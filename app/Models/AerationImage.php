<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AerationImage extends Model
{
    use HasFactory;
    protected $fillable = ['name','email','address'];
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'aeration_images';
}
