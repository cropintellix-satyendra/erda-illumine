<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cropvariety extends Model
{
    use HasFactory;
    use HasFactory;
      /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cropvarietys';
    /**
     * Get the states associated with the crop variety.
     */
    public function states(){
      return $this->hasOne(State::class, 'id', 'state_id');
    }

    /**
     * Get the seasons associated with the crop variety.
     */
    public function seasons(){
      return $this->hasOne(Season::class, 'id', 'season_id');
    }
}
