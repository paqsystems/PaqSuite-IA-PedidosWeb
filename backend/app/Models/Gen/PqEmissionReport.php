<?php

namespace App\Models\Gen;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PqEmissionReport extends Model
{
    protected $table = 'pq_emission_reports';

    /** @var list<string> */
    protected $fillable = [
        'process_code',
        'report_code',
        'name',
        'is_standard',
        'is_principal',
        'visible_mobile',
        'layout_definition',
        'layout_mime',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'is_standard' => 'boolean',
        'is_principal' => 'boolean',
        'visible_mobile' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(PqEmissionProcess::class, 'process_code', 'process_code');
    }
}
