<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'household_id',
        'user_id',
        'type',
        'amount',
        'description',
        'category',
        'spent_at',
        'settled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent_at' => 'datetime',
        'settled_at' => 'datetime',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function settlements(): BelongsToMany
    {
        return $this->belongsToMany(Settlement::class);
    }

    public function scopeUnsettled($query)
    {
        return $query->whereNull('settled_at');
    }

    public function scopeCommon($query)
    {
        return $query->where('type', 'common');
    }
}
