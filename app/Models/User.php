<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'mobile',
        'company_code',
        'otp',
        'role',
        'state_id',
        'state','company_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The services that belong to the user.
     */
    public function device()
    {
        return $this->hasOne('App\Models\UserDevices');
    }

    /**
     * Get the farmer associated with the plot.
     */
    public function state(){
      return $this->hasOne(State::class, 'id', 'state_id');
    }

    /**
     * Get the farmer associated with the plot.
     */
    public function company(){
        return $this->hasOne(Company::class, 'user_id', 'id');
    }

    /**
     * Get the farmer associated with the plot.
     */
    public function company_name(){
        return $this->hasOne(Company::class, 'company_code', 'company_code');
    }
}
