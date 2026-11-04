<?php

namespace App\Models\Gen;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class PqEmissionProcess extends Model
{
    protected $table = 'pq_emission_processes';

    protected $primaryKey = 'process_code';

    public $incrementing = false;

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = [
        'process_code',
        'name',
        'description',
        'menu_process_code',
        'permite_consolidado',
        'permite_segmentado',
        'requiere_vista_previa',
        'canal_pdf',
        'canal_print',
        'canal_excel',
        'canal_csv',
        'canal_mail',
        'canal_zip',
        'output_policy_json',
        'is_active',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'permite_consolidado' => 'boolean',
        'permite_segmentado' => 'boolean',
        'requiere_vista_previa' => 'boolean',
        'canal_pdf' => 'boolean',
        'canal_print' => 'boolean',
        'canal_excel' => 'boolean',
        'canal_csv' => 'boolean',
        'canal_mail' => 'boolean',
        'canal_zip' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function reports(): HasMany
    {
        return $this->hasMany(PqEmissionReport::class, 'process_code', 'process_code');
    }
}
