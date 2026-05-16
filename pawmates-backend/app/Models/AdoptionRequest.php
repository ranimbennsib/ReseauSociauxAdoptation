<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdoptionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'requester_id',
        'owner_id',
        'message',
        'has_experience',
        'housing_type',
        'has_garden',
        'has_children',
        'phone_number',
        'status',
    ];

    protected $casts = [
        'has_experience' => 'boolean',
        'has_garden'     => 'boolean',
        'has_children'   => 'boolean',
    ];

    // ─── Relations ───────────────────────────────────────────

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
