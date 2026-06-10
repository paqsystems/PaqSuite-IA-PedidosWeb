<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PqMenu extends Model
{
    protected $table = 'pq_menus';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'text',
        'procedimiento',
        'orden',
        'enabled',
        'routeName',
        'tipo',
        'tipo_proceso',
        'expanded',
        'idparent',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'expanded' => 'boolean',
        'orden' => 'integer',
        'idparent' => 'integer',
        'id' => 'integer',
    ];
}
