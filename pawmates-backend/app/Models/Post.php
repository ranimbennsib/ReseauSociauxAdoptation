<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'image',
        'category',
        'city',
        'localisation_detail',
        'is_adoption',
        'is_donation',
        'card_number',
        'card_holder_name',
        'status',
    ];

    protected $casts = [
        'is_adoption' => 'boolean',
        'is_donation' => 'boolean',
    ];

    // ─── Relations ───────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function adoptionRequests()
    {
        return $this->hasMany(AdoptionRequest::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('deleted_at')->latest();
    }

    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
