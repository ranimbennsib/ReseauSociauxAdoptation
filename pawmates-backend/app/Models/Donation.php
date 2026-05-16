<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'user_id',
        'owner_id',
        'donor_card_last_four',
        'donor_card_holder_name',
        'amount',
        'transaction_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Auto-generate transaction_id on creation
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($donation) {
            if (empty($donation->transaction_id)) {
                $donation->transaction_id = (string) Str::uuid();
            }
        });
    }

    // ─── Relations ───────────────────────────────────────────

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
