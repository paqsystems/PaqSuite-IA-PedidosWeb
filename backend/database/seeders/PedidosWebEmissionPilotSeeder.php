<?php

namespace Database\Seeders;

use App\Models\Gen\PqEmissionProcess;
use App\Models\Gen\PqEmissionReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo GEN-15 — 8 informes emisibles (CC #19 / TR-SPEC-101-07).
 */
final class PedidosWebEmissionPilotSeeder extends Seeder
{
    /** @var array<string, mixed> */
    public const OUTPUT_POLICY = [
        'mode' => 'HYBRID',
        'outputs' => [
            [
                'channel' => 'pdf',
                'kind' => 'documentary',
                'mandatory' => false,
                'automatic' => false,
                'blocking' => true,
            ],
        ],
        'recipientEditMode' => 'NONE',
    ];

    /**
     * processCode => menu_process_code + nombre visible del proceso.
     * El reporte estándar de cada proceso se llama `<Original>` (no el caption del proceso).
     *
     * @var array<string, array{menu: string, name: string, reportCode: string}>
     */
    private const EMISSION_PROCESSES = [
        'pw_presupuestosingresados' => [
            'menu' => 'pw_presupuestosingresados',
            'name' => 'Presupuestos ingresados',
            'reportCode' => 'pw_presupuestosingresados',
        ],
        'pw_pedidosingresados' => [
            'menu' => 'pw_pedidosingresados',
            'name' => 'Pedidos ingresados',
            'reportCode' => 'pw_pedidosingresados',
        ],
        'pw_pedidospendientes' => [
            'menu' => 'pw_pedidospendientes',
            'name' => 'Pedidos pendientes',
            'reportCode' => 'pw_pedidospendientes',
        ],
        'pw_detallepedidos' => [
            'menu' => 'pw_detallepedidos',
            'name' => 'Detalle de pedidos',
            'reportCode' => 'pw_detallepedidos',
        ],
        'pw_deudaclientes' => [
            'menu' => 'pw_deudaclientes',
            'name' => 'Deudas',
            'reportCode' => 'pw_deudaclientes',
        ],
        'pw_consultacheques' => [
            'menu' => 'pw_consultacheques',
            'name' => 'Cheques',
            'reportCode' => 'pw_consultacheques',
        ],
        'pw_historialventas' => [
            'menu' => 'pw_historialventas',
            'name' => 'Historial ventas',
            'reportCode' => 'pw_historialventas',
        ],
        'pw_consultastock' => [
            'menu' => 'pw_consultastock',
            'name' => 'Stock',
            'reportCode' => 'pw_consultastock',
        ],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('pq_emission_processes') || ! Schema::hasTable('pq_emission_reports')) {
            return;
        }

        $policyJson = json_encode(self::OUTPUT_POLICY, JSON_THROW_ON_ERROR);

        foreach (self::EMISSION_PROCESSES as $processCode => $meta) {
            PqEmissionProcess::query()->updateOrCreate(
                ['process_code' => $processCode],
                [
                    'name' => $meta['name'],
                    'description' => 'Emisión PedidosWeb (GEN-15)',
                    'menu_process_code' => $meta['menu'],
                    'permite_consolidado' => true,
                    'permite_segmentado' => false,
                    'requiere_vista_previa' => false,
                    'canal_pdf' => true,
                    'canal_print' => false,
                    'canal_excel' => false,
                    'canal_csv' => false,
                    'canal_mail' => false,
                    'canal_zip' => false,
                    'output_policy_json' => $policyJson,
                    'is_active' => true,
                ],
            );

            PqEmissionReport::query()->updateOrCreate(
                [
                    'process_code' => $processCode,
                    'report_code' => $meta['reportCode'],
                ],
                [
                    'name' => '<Original>',
                    'is_standard' => true,
                    'is_principal' => true,
                    'visible_mobile' => false,
                    'is_active' => true,
                ],
            );
        }
    }
}
