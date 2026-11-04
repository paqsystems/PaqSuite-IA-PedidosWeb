<?php

namespace Database\Seeders;

use App\Models\Gen\PqEmissionProcess;
use App\Models\Gen\PqEmissionReport;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo GEN-15 piloto CC #19 — pedidos ingresados + deuda clientes.
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

    /** @var array<string, array{menu: string, name: string, reportCode: string}> */
    private const PILOT_PROCESSES = [
        'pw_pedidosingresados' => [
            'menu' => 'pw_pedidosingresados',
            'name' => 'Pedidos ingresados',
            'reportCode' => 'pw_pedidosingresados',
        ],
        'pw_deudaclientes' => [
            'menu' => 'pw_deuda',
            'name' => 'Deuda clientes',
            'reportCode' => 'pw_deudaclientes',
        ],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('pq_emission_processes') || ! Schema::hasTable('pq_emission_reports')) {
            return;
        }

        $policyJson = json_encode(self::OUTPUT_POLICY, JSON_THROW_ON_ERROR);

        foreach (self::PILOT_PROCESSES as $processCode => $meta) {
            PqEmissionProcess::query()->updateOrCreate(
                ['process_code' => $processCode],
                [
                    'name' => $meta['name'],
                    'description' => 'Piloto emisión PedidosWeb (CC #19)',
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
                    'name' => $meta['name'],
                    'is_standard' => true,
                    'is_principal' => true,
                    'visible_mobile' => false,
                    'is_active' => true,
                ],
            );
        }
    }
}
