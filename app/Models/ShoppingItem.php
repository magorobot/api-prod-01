<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShoppingItem extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'household_id',
        'name',
        'quantity',
        'is_checked',
        'added_by',
    ];

    protected $casts = [
        'is_checked' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function adder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    public function scopeUnchecked($query)
    {
        return $query->where('is_checked', false);
    }
}
