<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pipe extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'Pipe';

    protected $fillable = [
        'farmer_id',
        'farmer_unique_id',
        'select_year',
        'select_season',
        'plot_no',
        'pipe_count',
        'lat',
        'lng',
        'farmerPlotUniqueid',
        'images',
        'current_date',
        'current_time'
    ];

    protected $casts = [
        'current_date' => 'date',
        'current_time' => 'datetime:H:i:s',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',
        'pipe_count' => 'integer',
        'select_year' => 'integer'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    // Relationships
    public function farmer()
    {
        return $this->belongsTo(Farmer::class, 'farmer_id');
    }
}
