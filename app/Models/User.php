<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Auth\Passwords\CanResetPassword;
/**
 * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", format="int64", example="1"),
 *     @OA\Property(property="name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="gender", type="string", example="male"),
 *     @OA\Property(property="birth_date", type="string", format="date", example="1990-01-01"),
 *     @OA\Property(property="phone_number", type="string", example="123456789"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 * )
 */


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'gender',
        'birth_date',
        'phone_number',
        'email',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sendPasswordResetNotification($token)
    {
        $url = 'https://localhost:8000/reset-password?token=' . $token;

        $this->notify(new ResetPasswordNotification($url));
    }
}
