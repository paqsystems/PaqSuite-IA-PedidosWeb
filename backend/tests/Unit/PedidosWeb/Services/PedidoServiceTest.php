<?php

namespace Tests\Unit\PedidosWeb\Services;

use App\Contracts\PedidosWeb\PedidoDetalleRepositoryInterface;
use App\Contracts\PedidosWeb\PedidoRepositoryInterface;
use App\Exceptions\PedidosWebBusinessException;
use App\Exceptions\PedidosWebBusinessValidationException;
use App\Models\PqPedidoswebPedidoCabecera;
use App\Models\User;
use App\Services\Auth\CommercialProfileResolver;
use App\Services\PedidosWeb\CabeceraInicialService;
use App\Services\PedidosWeb\CalculoTotalesService;
use App\Services\PedidosWeb\ComprobanteGrabacionValidator;
use Illuminate\Support\Carbon;
use App\Services\PedidosWeb\ComprobanteCopiaService;
use App\Services\PedidosWeb\ComprobanteMailService;
use App\Services\PedidosWeb\LogIntegracionService;
use App\Services\PedidosWeb\PedidoService;
use App\Services\PedidosWeb\PedidosWebParameterService;
use App\Services\PedidosWeb\PedidosWebSchemaBootstrap;
use App\Services\PedidosWeb\PresupuestoCierreService;
use App\Services\Visibility\PedidosWebVisibilityGuard;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PedidoServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::shouldReceive('hasTable')->andReturn(false);
        Schema::shouldReceive('hasColumn')->andReturn(false);
    }

    #[Test]
    public function eliminarPedidoPermiteBorrarEstadoCero(): void
    {
        $pedido = new PqPedidoswebPedidoCabecera();
        $pedido->estado = 0;

        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->expects($this->never())->method('findByCodPedido');
        $pedidoRepository->expects($this->once())
            ->method('deleteFisicoCabecera')
            ->with('PED-1');

        $detalleRepository = $this->createMock(PedidoDetalleRepositoryInterface::class);
        $detalleRepository->expects($this->once())
            ->method('deleteByCodPedido')
            ->with('PED-1');

        $visibilityGuard = $this->createMock(PedidosWebVisibilityGuard::class);
        $visibilityGuard->expects($this->once())
            ->method('ensureComprobanteVisible')
            ->with($this->isInstanceOf(User::class), 'PED-1', true)
            ->willReturn($pedido);

        $service = $this->buildService($pedidoRepository, $detalleRepository, false, $visibilityGuard);
        $service->eliminarPedido('PED-1', $this->buildUser());
    }

    #[Test]
    public function eliminarPedidoRechazaSiEstadoNoEsCero(): void
    {
        $pedido = new PqPedidoswebPedidoCabecera();
        $pedido->estado = 1;

        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->expects($this->never())->method('deleteFisicoCabecera');

        $detalleRepository = $this->createMock(PedidoDetalleRepositoryInterface::class);
        $detalleRepository->expects($this->never())->method('deleteByCodPedido');

        $visibilityGuard = $this->createMock(PedidosWebVisibilityGuard::class);
        $visibilityGuard->method('ensureComprobanteVisible')->willReturn($pedido);

        $service = $this->buildService($pedidoRepository, $detalleRepository, false, $visibilityGuard);

        $this->expectException(PedidosWebBusinessException::class);
        $service->eliminarPedido('PED-2', $this->buildUser());
    }

    #[Test]
    public function eliminarPedidoRechazaCuandoParametroNoEliminaEstaActivo(): void
    {
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $detalleRepository = $this->createMock(PedidoDetalleRepositoryInterface::class);
        $detalleRepository->expects($this->never())->method('deleteByCodPedido');
        $pedidoRepository->expects($this->never())->method('deleteFisicoCabecera');

        $service = $this->buildService($pedidoRepository, $detalleRepository, true);

        $this->expectException(PedidosWebBusinessException::class);
        $service->eliminarPedido('PED-3', $this->buildUser());
    }

    #[Test]
    public function grabarComprobanteRechazaAccionInvalida(): void
    {
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $detalleRepository = $this->createMock(PedidoDetalleRepositoryInterface::class);
        $service = $this->buildService($pedidoRepository, $detalleRepository, false);
        $user = new \App\Models\User();
        $user->codigo = 'supervisor.mvp';

        $this->expectException(PedidosWebBusinessException::class);
        $service->grabarComprobante([
            'accionGrabacion' => 'invalida',
            'cabecera' => ['cod_cliente' => 'CLI001'],
            'renglones' => [['cod_articulo' => 'A', 'cantidad' => 1, 'precio' => 1]],
        ], $user);
    }

    #[Test]
    public function grabarComprobanteRechazaCabeceraSinRenglones(): void
    {
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $detalleRepository = $this->createMock(PedidoDetalleRepositoryInterface::class);
        $service = $this->buildService($pedidoRepository, $detalleRepository, false);
        $user = new \App\Models\User();
        $user->codigo = 'supervisor.mvp';

        $this->expectException(PedidosWebBusinessValidationException::class);
        $service->grabarComprobante([
            'accionGrabacion' => 'pedido',
            'cabecera' => $this->validCabeceraPayload(),
            'renglones' => [],
        ], $user);
    }

    #[Test]
    public function iniciarEdicionPasaEstadoCeroAMenosUno(): void
    {
        $pedido = $this->buildPedido(estado: 0);
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->method('findByCodPedido')->willReturn($pedido);
        $pedidoRepository->expects($this->once())
            ->method('updateCabecera')
            ->with(
                'PED-ED-1',
                $this->callback(static fn (array $attrs): bool => ($attrs['estado'] ?? null) === -1)
            );

        $service = $this->buildService($pedidoRepository, $this->createMock(PedidoDetalleRepositoryInterface::class), false);
        $resultado = $service->iniciarEdicion('PED-ED-1', $this->buildUser());

        $this->assertSame(-1, $resultado['estado']);
        $this->assertSame('PED-ED-1', $resultado['cod_pedido']);
    }

    #[Test]
    public function iniciarEdicionRechazaEstadoNoEditable(): void
    {
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->method('findByCodPedido')->willReturn($this->buildPedido(estado: 1));
        $pedidoRepository->expects($this->never())->method('updateCabecera');

        $service = $this->buildService($pedidoRepository, $this->createMock(PedidoDetalleRepositoryInterface::class), false);

        $this->expectException(PedidosWebBusinessException::class);
        $service->iniciarEdicion('PED-ED-2', $this->buildUser());
    }

    #[Test]
    public function iniciarEdicionRechazaCuandoOtroUsuarioEdita(): void
    {
        $pedido = $this->buildPedido(estado: -1, codUsuarioWeb: 'otro.usuario');
        $pedido->fechahora_ultima_actividad = Carbon::now();

        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->method('findByCodPedido')->willReturn($pedido);
        $pedidoRepository->expects($this->never())->method('updateCabecera');

        $service = $this->buildService($pedidoRepository, $this->createMock(PedidoDetalleRepositoryInterface::class), false);

        try {
            $service->iniciarEdicion('PED-ED-3', $this->buildUser());
            $this->fail('Se esperaba PedidosWebBusinessException');
        } catch (PedidosWebBusinessException $exception) {
            $this->assertSame(409, $exception->httpStatus());
            $this->assertSame('business.edicionEnCurso', $exception->respuestaKey());
        }
    }

    #[Test]
    public function touchActividadActualizaSoloEnEstadoMenosUno(): void
    {
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->method('findByCodPedido')->willReturn($this->buildPedido(estado: -1));
        $pedidoRepository->expects($this->once())
            ->method('updateCabecera')
            ->with(
                'PED-TOUCH-1',
                $this->callback(static fn (array $attrs): bool => array_key_exists('fechahora_ultima_actividad', $attrs))
            );

        $service = $this->buildService($pedidoRepository, $this->createMock(PedidoDetalleRepositoryInterface::class), false);
        $resultado = $service->touchActividad('PED-TOUCH-1', $this->buildUser());

        $this->assertSame('PED-TOUCH-1', $resultado['cod_pedido']);
        $this->assertArrayHasKey('fechahora_ultima_actividad', $resultado);
    }

    #[Test]
    public function touchActividadRechazaSiEstadoNoEsMenosUno(): void
    {
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->method('findByCodPedido')->willReturn($this->buildPedido(estado: 0));
        $pedidoRepository->expects($this->never())->method('updateCabecera');

        $service = $this->buildService($pedidoRepository, $this->createMock(PedidoDetalleRepositoryInterface::class), false);

        $this->expectException(PedidosWebBusinessException::class);
        $service->touchActividad('PED-TOUCH-2', $this->buildUser());
    }

    #[Test]
    public function cancelarEdicionVuelveEstadoCero(): void
    {
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->method('findByCodPedido')->willReturn($this->buildPedido(estado: -1));
        $pedidoRepository->expects($this->once())
            ->method('updateCabecera')
            ->with(
                'PED-CAN-1',
                $this->callback(static fn (array $attrs): bool => ($attrs['estado'] ?? null) === 0)
            );

        $service = $this->buildService($pedidoRepository, $this->createMock(PedidoDetalleRepositoryInterface::class), false);
        $resultado = $service->cancelarEdicion('PED-CAN-1', $this->buildUser());

        $this->assertSame(0, $resultado['estado']);
    }

    #[Test]
    public function cancelarEdicionRechazaSiEstadoNoEsMenosUno(): void
    {
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->method('findByCodPedido')->willReturn($this->buildPedido(estado: 0));
        $pedidoRepository->expects($this->never())->method('updateCabecera');

        $service = $this->buildService($pedidoRepository, $this->createMock(PedidoDetalleRepositoryInterface::class), false);

        $this->expectException(PedidosWebBusinessException::class);
        $service->cancelarEdicion('PED-CAN-2', $this->buildUser());
    }

    #[Test]
    public function grabarComprobanteRechazaPresupuestoOrigenInvalido(): void
    {
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->method('findByCodPedido')->willReturn($this->buildPedido(estado: 0));

        $service = $this->buildService($pedidoRepository, $this->createMock(PedidoDetalleRepositoryInterface::class), false);

        $this->expectException(PedidosWebBusinessException::class);
        $service->grabarComprobante($this->grabarPayloadPedido([
            'cod_presupuesto_origen' => 'PRE-ORIGEN-1',
        ]), $this->buildUser());
    }

    #[Test]
    public function grabarComprobanteRechazaPedidoOrigenInvalidoParaPresupuesto(): void
    {
        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $pedidoRepository->method('findByCodPedido')->willReturn(null);

        $service = $this->buildService($pedidoRepository, $this->createMock(PedidoDetalleRepositoryInterface::class), false);

        $this->expectException(PedidosWebBusinessException::class);
        $service->grabarComprobante([
            'accionGrabacion' => 'presupuesto',
            'cod_pedido_origen' => 'PED-ORIGEN-1',
            'cabecera' => $this->validCabeceraPayload(),
            'renglones' => [['cod_articulo' => 'A1', 'cantidad' => 1, 'precio' => 100, 'precio_neto' => 100, 'importe_total' => 100]],
        ], $this->buildUser());
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function grabarPayloadPedido(array $extra = []): array
    {
        return [
            ...[
                'accionGrabacion' => 'pedido',
                'cabecera' => $this->validCabeceraPayload(),
                'renglones' => [['cod_articulo' => 'A1', 'cantidad' => 1, 'precio' => 100, 'precio_neto' => 100, 'importe_total' => 100]],
            ],
            ...$extra,
        ];
    }

    private function buildPedido(int $estado, ?string $codUsuarioWeb = null): PqPedidoswebPedidoCabecera
    {
        $pedido = new PqPedidoswebPedidoCabecera();
        $pedido->estado = $estado;
        $pedido->cod_usuario_web = $codUsuarioWeb;

        return $pedido;
    }

    private function buildUser(): User
    {
        $user = new User();
        $user->codigo = 'supervisor.mvp';

        return $user;
    }

    private function buildService(
        PedidoRepositoryInterface $pedidoRepository,
        PedidoDetalleRepositoryInterface $detalleRepository,
        bool $noEliminaPedido,
        ?PedidosWebVisibilityGuard $visibilityGuard = null
    ): PedidoService {
        config()->set('paqsuite_pedidosweb.readFromErp', false);
        config()->set('paqsuite_pedidosweb.defaults.NOeliminaPedido', $noEliminaPedido ? 1 : 0);
        config()->set('paqsuite_pedidosweb.defaults.NOmodificaPedido', 0);
        config()->set('paqsuite_pedidosweb.defaults.MinutosWeb', 30);

        $parameterService = new PedidosWebParameterService();
        $visibilityGuard ??= $this->createPermissiveVisibilityGuard();
        $copiaService = new ComprobanteCopiaService($pedidoRepository);
        $cierreService = new PresupuestoCierreService($pedidoRepository, $parameterService, $visibilityGuard);
        $mailService = new ComprobanteMailService($parameterService, new LogIntegracionService());
        $schemaBootstrap = new PedidosWebSchemaBootstrap();

        $cabeceraInicialService = new CabeceraInicialService($visibilityGuard, $parameterService);
        $comprobanteGrabacionValidator = new ComprobanteGrabacionValidator($parameterService);

        return new PedidoService(
            $pedidoRepository,
            $detalleRepository,
            $parameterService,
            new CalculoTotalesService(),
            $copiaService,
            $cierreService,
            $mailService,
            $visibilityGuard,
            $this->createPermissiveCommercialProfileResolver(),
            $schemaBootstrap,
            $cabeceraInicialService,
            $comprobanteGrabacionValidator,
        );
    }

    #[Test]
    public function grabarComprobanteRechazaPrecioModificadoSinPermiso(): void
    {
        config()->set('paqsuite_pedidosweb.defaults.ModificaPrecioS', 0);

        $pedidoRepository = $this->createMock(PedidoRepositoryInterface::class);
        $detalleRepository = $this->createMock(PedidoDetalleRepositoryInterface::class);
        $service = $this->buildService($pedidoRepository, $detalleRepository, false);

        $this->expectException(PedidosWebBusinessValidationException::class);
        $service->grabarComprobante([
            'accionGrabacion' => 'pedido',
            'cabecera' => $this->validCabeceraPayload(),
            'renglones' => [[
                'cod_articulo' => 'A1',
                'cantidad' => 1,
                'precio' => 100,
                'precio_modificado' => 1,
                'precio_neto' => 100,
                'importe_total' => 100,
            ]],
        ], $this->buildUser());
    }

    private function validCabeceraPayload(): array
    {
        return [
            'cod_cliente' => 'CLI001',
            'cod_vended' => 'V001',
            'cod_perfil' => 'MVP',
            'cod_condvta' => 1,
            'cod_transpor' => 'T001',
            'id_de' => 1,
            'lista_precios' => 1,
            'nivel' => 0,
        ];
    }

    private function createPermissiveCommercialProfileResolver(): CommercialProfileResolver
    {
        return new CommercialProfileResolver();
    }

    private function createPermissiveVisibilityGuard(): PedidosWebVisibilityGuard
    {
        $guard = $this->createMock(PedidosWebVisibilityGuard::class);
        $guard->method('ensureCodClienteVisible');
        $guard->method('ensureComprobanteVisible')->willReturn(new PqPedidoswebPedidoCabecera());

        return $guard;
    }
}
