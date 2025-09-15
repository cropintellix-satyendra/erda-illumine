<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerConsentForm extends Model
{
    use HasFactory;

    protected $table = 'farmer_consent_forms_images';
    protected $fillable = ['farmer_uniqueId', 'images','index'];
}
