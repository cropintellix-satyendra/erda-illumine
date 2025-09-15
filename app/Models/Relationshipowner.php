<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Relationshipowner extends Model
{
    use HasFactory, SoftDeletes;
      /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'relatioshipowners';
}
