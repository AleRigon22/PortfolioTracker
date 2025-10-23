<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Broker extends Model
{
    protected $fillable = ['user_id', 'name', 'code'];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
