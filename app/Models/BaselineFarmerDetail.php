<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaselineFarmerDetail extends Model
{
    use HasFactory;

    public function surveyor(){
        return $this->belongsTo(User::class, 'surveyor_id', 'id');
    }
}
