<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\FollowTrait;
use App\Traits\ChatUserTrait;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, FollowTrait,ChatUserTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'image',
        'notification_id',
        'current_room_chat',
        'online',
        'lan'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    public function follows()
    {
        return $this->hasMany(Follow::class, 'user_id');
    }

    public function followers()
    {
        return $this->hasMany(Follow::class, 'followed_user_id', 'id');
    }

    public function followeds()
    {
        return $this->hasMany(Follow::class, 'user_id', 'id');
    }
    public function followBack(User $user)
    {
        $userId = $user->id;
        return  $this->followers()->where('user_id', $userId)->exists();
    }
}
