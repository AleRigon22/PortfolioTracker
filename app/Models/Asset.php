<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asset extends Model
{
    protected $fillable = ['user_id', 'name', 'ticker', 'asset_type'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
