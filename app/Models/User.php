<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use Notifiable;
    use HasApiTokens;

    protected $fillable = [
        'fullname',
        'username',
        'email',
        'phone',
        'password',
        'role',
        'default_kam_id',
        'status',
    ];

    protected $hidden = [
        'password',
        'supervisor_mappings',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
 protected $appends = [
        'supervisor_ids', // ✅ auto add
    ];

    public function supervisorMappings()
    {
        return $this->hasMany(UserSupervisorMapping::class, 'user_id');
    }

    public function getSupervisorIdsAttribute()
    {
        return $this->supervisorMappings()
            ->pluck('supervisor_id')
            ->values();
    }
}
