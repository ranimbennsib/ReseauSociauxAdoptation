<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    // ─── Helpers ─────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // ─── Relations ───────────────────────────────────────────

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function sentAdoptionRequests()
    {
        return $this->hasMany(AdoptionRequest::class, 'requester_id');
    }

    public function receivedAdoptionRequests()
    {
        return $this->hasMany(AdoptionRequest::class, 'owner_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function receivedDonations()
    {
        return $this->hasMany(Donation::class, 'owner_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }
}