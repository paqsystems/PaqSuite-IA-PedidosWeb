<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PqGridLayoutLastUsed extends Model
{
    public $timestamps = false;

    protected $table = 'pq_grid_layout_last_used';

    protected $fillable = [
        'user_id',
        'proceso',
        'grid_id',
        'layout_id',
        'updated_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'layout_id' => 'integer',
        'updated_at' => 'datetime',
    ];

    public function layout(): BelongsTo
    {
        return $this->belongsTo(PqGridLayout::class, 'layout_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
