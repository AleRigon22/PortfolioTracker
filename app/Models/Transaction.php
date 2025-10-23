<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'asset_id',
        'broker_id',
        'type',
        'quantity',
        'price_per_unit',
        'fees',
        'currency',
        'exchange_rate',
        'amount_in_base_currency',
        'traded_at',
        'settled_at',
        'status',
        'notes',
        'tax_lot_id',
        'cost_basis',
        'external_id',
        'api_provider',
        'imported_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:8',
        'price_per_unit' => 'decimal:6',
        'fees' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'amount_in_base_currency' => 'decimal:2',
        'cost_basis' => 'decimal:2',
        'traded_at' => 'datetime',
        'settled_at' => 'datetime',
        'imported_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($transaction) {
            // Assegna user_id se non impostato
            if (auth()->check() && !$transaction->user_id) {
                $transaction->user_id = auth()->id();
            }

            // Assegna un broker_id di default se non impostato
            if (!$transaction->broker_id) {
                $broker = Broker::firstOrCreate(
                    ['user_id' => $transaction->user_id, 'name' => 'Default'],
                    ['name' => 'Default']
                );
                $transaction->broker_id = $broker->id;
            }
        });
    }

    // Relazioni
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    // Accessor - Calcolo total row
    public function getRowTotalAttribute()
    {
        if ($this->type === 'tax' || $this->type === 'interest') {
            return $this->fees;
        }

        return ($this->quantity ?? 0) * ($this->price_per_unit ?? 0) + ($this->fees ?? 0);
    }
}