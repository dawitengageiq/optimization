<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
    use Notifiable;

    protected $connection;

    use Authenticatable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'affiliate_id',
        'advertiser_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'position',
        'password',
        'email',
        'address',
        'mobile_number',
        'phone_number',
        'role_id',
        'instant_messaging',
        'account_type',
        'profile_image',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if (config('app.type') == 'reports') {
            $this->connection = 'secondary';
        }
    }

    /**
     * will determine if user is admin
     */
    public function isAdministrator(): bool
    {
        return $this->account_type == 2;
    }

    /**
     * will determine if user is affiliate or advertiser
     */
    public function isUser(): bool
    {
        return $this->account_type == 1;
    }

    /**
     * scope for all non admin users
     *
     * @return mixed
     */
    public function scopeAllNonAdmin($query)
    {
        return $query->whereRaw('account_type != ?', [2]);
    }

    /**
     * scope for all admin users
     *
     * @return mixed
     */
    public function scopeAllAdmin($query)
    {
        return $query->whereRaw('account_type != ?', [1]);
    }

    /**
     * check if user is affiliate
     */
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }

    public function advertiser()
    {
        return $this->belongsTo(Advertiser::class);
    }

    /**
     * check user's role
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * check if use is super user
     */
    public function isSuperUser(): bool
    {
        return $this->role_id == 1;
    }

    public function scopeUserByEmailAndID($query, $param)
    {
        return $query->where('email', $param['email'])
            ->where('id', $param['id']);
    }
}
