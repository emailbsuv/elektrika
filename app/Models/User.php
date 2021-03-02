<?php

namespace App\Models;

use Carbon\Carbon;
use Log;
use Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword as ResetPasswordNotification;

/**
 * App\Models\User
 *
 * @OA\Schema (
 *     schema="User",
 *     description="User model",
 *     title="User model",
 *     required={"name", "photoUrls"},
 *     @OA\Xml(
 *         name="User"
 *     )
 * )
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\User query()
 * @mixin \Eloquent
 */
class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'code', 'code_created_at', 'phone_approved_at', 'verification_code', 'phone_verified_at'
    ];

    /**
     * Generate random password
     */
    public static function generatePassword($length = 32)
    {
        return bcrypt(Str::random($length));
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * @return int
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function routeNotificationForSmscru()
    {
        Log::info('User routeNotificationForSmscru '.$this->phone);
        return $this->phone;
    }

    public function generateVerificationCode()
    {
        try {
            $verificationCode = strval(random_int(100000, 1000000));
            $this->verification_code = $verificationCode;
            $this->code_created_at = Carbon::now();
            $this->save();
            return $verificationCode;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
