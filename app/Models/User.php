<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    public $incrementing = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->user_firstname) && !empty($user->name)) {
                $user->user_firstname = $user->name;
            }
            if (empty($user->user_lastname) && !empty($user->name)) {
                $user->user_lastname = '';
            }
            if (empty($user->user_email) && !empty($user->email)) {
                $user->user_email = $user->email;
            }
            if (empty($user->user_country)) {
                $user->user_country = '';
            }
            if (empty($user->user_password) && !empty($user->password)) {
                $user->user_password = $user->password;
            }
        });
    }

}
