<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaselineForm extends Model
{
    use HasFactory;

    protected $fillable = ['farmer_signature'];

    public function surveyor(){
        return $this->belongsTo(User::class, 'surveyor_id', 'id');
    }
}
