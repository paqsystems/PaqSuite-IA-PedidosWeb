<?php

namespace Tests\Support;

use App\Contracts\PedidosWeb\PedidoDetalleRepositoryInterface;
use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Models\User;
use App\Services\PedidosWeb\PedidosWebSchemaBootstrap;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

trait SeedsPedidosWebFeatureData
{
    use AuthenticatesPaqTenant;

    protected function setUpPedidosWebFeature(): void
    {
        $this->setUpAuthenticatesPaqTenant();

        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.NOeliminaPedido', 0);
        config()->set('paqsuite_pedidosweb.defaults.NOmodificaPedido', 0);
        config()->set('paqsuite_pedidosweb.defaults.MinutosWeb', 30);
        config()->set('paqsuite_pedidosweb.defaults.DetallePorMail', 1);
        config()->set('paqsuite_pedidosweb.defaults.Mail_DireccionRemitente', 'pedidos@empresa.test');
        config()->set('paqsuite_pedidosweb.defaults.MailDestinatariosAdicionales', '');
        config()->set('paqsuite_pedidosweb.defaults.mailCCO', '');

        if (! $this->bootstrapPedidosWebTenant()) {
            $this->markTestSkipped('Tenant desarrollo / SQL Server no disponible para feature 200.');
        }

        Mail::fake();
    }

    protected function bootstrapPedidosWebTenant(): bool
    {
        try {
            if ($this->artisan('paqsuite:seed-menus-mvp')->run() !== 0) {
                return false;
            }

            if ($this->artisan('paqsuite:seed-seguridad-mvp')->run() !== 0) {
                return false;
            }

            $this->ensureComprobanteReferences();
            $this->ensureMailDestinatariosFixtures();
            app(PedidosWebSchemaBootstrap::class)->ensureMvpSchema();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function sampleGrabacionPayload(string $codCliente = 'CLIMVP001'): array
    {
        return [
            'cabecera' => ['cod_cliente' => $codCliente],
            'renglones' => [
                [
                    'cod_articulo' => 'ART-HP-001',
                    'descripcion_articulo' => 'Articulo feature test',
                    'cantidad' => 2,
                    'precio' => 100,
                    'porc_bonif' => 0,
                    'porc_iva' => 21,
                ],
            ],
        ];
    }

    protected function uniqueComprobanteCod(string $prefix): string
    {
        $suffix = strtoupper(substr(str_replace('.', '', uniqid('', true)), -8));

        return substr($prefix.$suffix, 0, 20);
    }

    protected function uniqueClienteCod(string $prefix): string
    {
        $suffix = strtoupper(substr(str_replace('.', '', uniqid('', true)), -4));

        return substr($prefix.$suffix, 0, 10);
    }

    protected function motivoRechazoFeatureId(): int
    {
        $idMotivo = DB::table('pq_pedidosweb_motivos_cierre')
            ->where('tipo_cierre', 'negativo')
            ->where('descripcion', 'Rechazo feature test')
            ->value('id_motivo');

        if ($idMotivo === null) {
            $this->fail('Motivo de rechazo feature test no disponible en pq_pedidosweb_motivos_cierre.');
        }

        return (int) $idMotivo;
    }

    protected function insertComprobanteConDetalle(string $codPedido, int $estado, string $codCliente = 'CLIMVP001'): void
    {
        /** @var PedidoRepositoryInterface $pedidoRepository */
        $pedidoRepository = $this->app->make(PedidoRepositoryInterface::class);
        /** @var PedidoDetalleRepositoryInterface $detalleRepository */
        $detalleRepository = $this->app->make(PedidoDetalleRepositoryInterface::class);

        $pedidoRepository->insertCabecera($this->cabeceraSeedPayload($codPedido, $estado, $codCliente));
        $detalleRepository->syncDetalle($codPedido, [
            $this->renglonSeedPayload(1, 'ART-HP-SEED', 1, 150),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function cabeceraSeedPayload(string $codPedido, int $estado, string $codCliente): array
    {
        $sqlServerDateTime = CarbonImmutable::now()->format('Ymd H:i:s');

        return [
            'cod_pedido' => $codPedido,
            'cod_cliente' => $codCliente,
            'fecha' => $sqlServerDateTime,
            'nivel' => 0,
            'observaciones' => 'Seed feature PedidosWeb',
            'incluye_iva' => false,
            'moneda' => 1,
            'estado' => $estado,
            'tal_pedido_tango' => 1,
            'nro_pedido_tango' => substr($codPedido, 0, 20),
            'cod_usuario_web' => 'supervisor.mvp',
            'fecha_modif' => $sqlServerDateTime,
            'total' => 150,
            'total_iva' => 31.5,
            'descuento' => 0,
            'bonif_1' => 0,
            'bonif_2' => 0,
            'bonif_3' => 0,
            'cod_perfil' => 'MVP',
            'cod_vended' => 'VENACOT01',
            'cod_condvta' => 1,
            'cod_transpor' => 'MVP',
            'lista_precios' => 1,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function renglonSeedPayload(int $renglon, string $codArticulo, float $cantidad, float $precio): array
    {
        return [
            'renglon' => $renglon,
            'cod_articulo' => $codArticulo,
            'cantidad' => $cantidad,
            'porc_bonif' => 0,
            'precio' => $precio,
            'precio_neto' => $precio,
            'precio_bruto' => $precio,
            'porc_iva' => 21,
            'iva' => round($precio * $cantidad * 0.21, 2),
            'importe_total' => round($precio * $cantidad * 1.21, 2),
        ];
    }

    private function ensureComprobanteReferences(): void
    {
        if (Schema::hasTable('pq_pedidosweb_transportes')) {
            DB::table('pq_pedidosweb_transportes')->updateOrInsert(
                ['codigo' => 'MVP'],
                ['descripcion' => 'Transporte MVP']
            );
        }

        if (Schema::hasTable('pq_pedidosweb_perfil')) {
            DB::table('pq_pedidosweb_perfil')->updateOrInsert(
                ['cod_perfil' => 'MVP'],
                ['descripcion' => 'Perfil MVP']
            );
        }
    }

    protected function ensureClienteSinEmailDestinatarios(string $codCliente): void
    {
        if (! Schema::hasTable('pq_pedidosweb_clientes')) {
            return;
        }

        if (Schema::hasTable('pq_pedidosweb_vendedores')) {
            DB::table('pq_pedidosweb_vendedores')->updateOrInsert(
                ['cod_vended' => 'VEN-NOMAIL'],
                [
                    'nombre' => 'Vendedor sin mail test',
                    'e_mail' => null,
                    'mail_supervisor' => null,
                    'supervisor' => false,
                ]
            );
        }

        DB::table('pq_pedidosweb_clientes')->updateOrInsert(
            ['cod_client' => $codCliente],
            [
                'nombre' => 'Cliente sin mail test',
                'fantasia' => 'Cliente sin mail test',
                'cod_vended' => 'VEN-NOMAIL',
                'cod_login' => null,
                'e_mail' => null,
                'lista_precios' => 1,
                'cod_condvta' => 1,
                'bonificacion' => 0,
                'nivel' => 0,
            ]
        );
    }

    protected function seedVisibilityUniverse(): void
    {
        $this->grantVendedorAcotadoFeaturePermissions();
        $this->upsertClienteSeed('CLI-VEN-A', 'Cliente Vendedor A', 'VENACOT01');
        $this->upsertClienteSeed('CLI-VEN-B', 'Cliente Vendedor B', 'VENSINM01');
        $this->upsertComprobanteSeed('PED-VEN-B-0', 'CLI-VEN-B', 'VENSINM01', 0, 999.00);
    }

    protected function grantVendedorAcotadoFeaturePermissions(): void
    {
        $rol = \App\Models\PqRol::query()->where('nombre_rol', 'VendedorAcotado')->first();

        if ($rol === null) {
            return;
        }

        $procedimientos = [
            (string) config('paqsuite_visibility.procedimientos.cargaComprobantes'),
            (string) config('paqsuite_visibility.procedimientos.tratativasPresupuestos'),
            (string) config('paqsuite_visibility.procedimientos.consultasDeuda'),
            (string) config('paqsuite_visibility.procedimientos.consultasCheques'),
            (string) config('paqsuite_visibility.procedimientos.consultasHistorialVentas'),
        ];

        foreach ($procedimientos as $procedimiento) {
            \App\Models\PqRolAtributo::query()->updateOrInsert(
                [
                    'id_rol' => $rol->id,
                    'procedimiento' => $procedimiento,
                ],
                [
                    'permiso_alta' => true,
                    'permiso_baja' => true,
                    'permiso_modi' => true,
                    'permiso_repo' => true,
                ]
            );
        }
    }

    protected function upsertClienteSeed(string $codCliente, string $nombre, string $codVendedor): void
    {
        if (! Schema::hasTable('pq_pedidosweb_clientes')) {
            return;
        }

        DB::table('pq_pedidosweb_clientes')->updateOrInsert(
            ['cod_client' => $codCliente],
            [
                'nombre' => $nombre,
                'fantasia' => $nombre,
                'cod_vended' => $codVendedor,
                'cod_login' => null,
                'e_mail' => strtolower($codCliente).'@paqsuite.local',
                'lista_precios' => 1,
                'cod_condvta' => 1,
                'bonificacion' => 0,
                'nivel' => 0,
            ]
        );
    }

    protected function upsertComprobanteSeed(
        string $codPedido,
        string $codCliente,
        ?string $codVendedor,
        int $estado,
        float $total
    ): void {
        if (! Schema::hasTable('pq_pedidosweb_pedidoscabecera')) {
            return;
        }

        $sqlServerDateTime = CarbonImmutable::now()->format('Ymd H:i:s');

        DB::table('pq_pedidosweb_pedidoscabecera')->updateOrInsert(
            ['cod_pedido' => $codPedido],
            [
                'cod_cliente' => $codCliente,
                'fecha' => $sqlServerDateTime,
                'nivel' => 0,
                'observaciones' => 'Seed visibilidad PedidosWeb',
                'incluye_iva' => false,
                'moneda' => 1,
                'estado' => $estado,
                'tal_pedido_tango' => 1,
                'nro_pedido_tango' => substr($codPedido, 0, 20),
                'cod_usuario_web' => $codCliente,
                'fecha_modif' => $sqlServerDateTime,
                'total' => $total,
                'total_iva' => round($total * 0.21, 2),
                'descuento' => 0,
                'bonif_1' => 0,
                'bonif_2' => 0,
                'bonif_3' => 0,
                'cod_perfil' => 'MVP',
                'cod_vended' => $codVendedor,
                'cod_condvta' => 1,
                'id_de' => null,
                'cod_transpor' => 'MVP',
                'lista_precios' => 1,
            ]
        );
    }

    private function ensureMailDestinatariosFixtures(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_clientes')) {
            return;
        }

        DB::table('pq_pedidosweb_clientes')->updateOrInsert(
            ['cod_client' => 'CLIMVP001'],
            [
                'nombre' => 'Cliente MVP',
                'fantasia' => 'Cliente MVP',
                'cod_vended' => 'VENACOT01',
                'cod_login' => 'CLIMVP001',
                'e_mail' => 'cliente.mvp@paqsuite.local',
                'lista_precios' => 1,
                'cod_condvta' => 1,
                'bonificacion' => 0,
                'nivel' => 0,
            ]
        );

        if (Schema::hasTable('pq_pedidosweb_vendedores')) {
            DB::table('pq_pedidosweb_vendedores')->updateOrInsert(
                ['cod_vended' => 'VENACOT01'],
                [
                    'nombre' => 'Vendedor Acotado MVP',
                    'e_mail' => 'vendedor.acotado.mvp@paqsuite.local',
                    'mail_supervisor' => 'supervisor.mvp@paqsuite.local',
                    'supervisor' => false,
                ]
            );
        }
    }
}
