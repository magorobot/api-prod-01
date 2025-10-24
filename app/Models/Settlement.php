<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Settlement extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'household_id',
        'from_user_id',
        'to_user_id',
        'amount',
        'note',
        'settled_on',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'settled_on' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function expenses(): BelongsToMany
    {
        return $this->belongsToMany(Expense::class);
    }
}
