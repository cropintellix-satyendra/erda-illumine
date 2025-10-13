<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRequestLog extends Model
{
    use HasFactory;

    protected $table = 'api_request_logs';

    protected $fillable = [
        'url',
        'method',
        'ip_address',
        'user_agent',
        'headers',
        'request_data',
        'response_data',
        'user_id',
        'response_status',
        'response_time',
    ];

    protected $casts = [
        'headers' => 'array',
        'request_data' => 'array',
        'response_data' => 'array',
        'response_time' => 'decimal:3',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that made the request
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to filter by method
     */
    public function scopeByMethod($query, $method)
    {
        if ($method) {
            return $query->where('method', $method);
        }
        return $query;
    }

    /**
     * Scope a query to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        if ($status) {
            return $query->where('response_status', $status);
        }
        return $query;
    }

    /**
     * Scope a query to filter by date range
     */
    public function scopeDateRange($query, $from, $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to);
        }
        return $query;
    }

    /**
     * Scope a query to search by URL
     */
    public function scopeSearchUrl($query, $search)
    {
        if ($search) {
            return $query->where('url', 'like', '%' . $search . '%');
        }
        return $query;
    }
}

