<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PqGridLayout extends Model
{
    protected $table = 'pq_grid_layouts';

    protected $fillable = [
        'proceso',
        'grid_id',
        'layout_name',
        'created_by_user_id',
        'state_json',
    ];

    protected $casts = [
        'created_by_user_id' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}
