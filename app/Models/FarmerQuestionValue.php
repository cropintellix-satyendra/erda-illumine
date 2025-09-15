<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FarmerQuestionValue extends Model
{
    use HasFactory;

    protected $fillable = ['question_value'];

    public function question()
    {
        return $this->belongsTo(FarmerQuestion::class);
    }

}
