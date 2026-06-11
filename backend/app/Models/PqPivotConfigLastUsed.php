<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PqPivotConfigLastUsed extends Model
{
    public $timestamps = false;

    protected $table = 'pq_pivots_config_last_used';

    protected $fillable = [
        'user_id',
        'consulta_id',
        'pivot_id',
        'updated_at',
    ];

    protected $casts = [
        'pivot_id' => 'integer',
        'updated_at' => 'datetime',
    ];

    public function pivotConfig(): BelongsTo
    {
        return $this->belongsTo(PqPivotConfig::class, 'pivot_id', 'pivot_id');
    }
}
