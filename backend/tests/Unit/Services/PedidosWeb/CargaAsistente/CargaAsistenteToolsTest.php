<?php

namespace Tests\Unit\Services\PedidosWeb\CargaAsistente;

use App\Services\PedidosWeb\ArticuloCargaLookupService;
use App\Services\PedidosWeb\CargaAsistente\CargaAsistenteConsultaFormatting;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteArticuloTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteCabeceraTool;
use App\Services\PedidosWeb\PedidosWebParameterService;
use Tests\TestCase;

final class CargaAsistenteToolsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('paqsuite_pedidosweb.readFromErp', false);
    }

    public function testMutateRemoveAmbiguousRenglonesReturnsChoiceFromDraftOnly(): void
    {
        $tool = new CargaAsistenteArticuloTool(
            new ArticuloCargaLookupService(),
            new PedidosWebParameterService(),
        );

        $result = $tool->mutateExistingRenglon(
            $this->draftWithRenglones([
                [
                    'renglon' => 1,
                    'codArticulo' => 'AB 3000',
                    'descripcion' => 'ARROZ BASMATI30 KG',
                    'cantidad' => 123,
                    'precio' => 3941,
                    'porcBonif' => 0,
                ],
                [
                    'renglon' => 2,
                    'codArticulo' => 'AB 0501',
                    'descripcion' => 'ARROZ BASMATI5 KG',
                    'cantidad' => 122,
                    'precio' => 3941,
                    'porcBonif' => 0,
                ],
            ], 'V'),
            'remove',
            'arroz',
        );

        $this->assertSame('pedidos.carga.asistente.elegirRenglon', $result['replyText']);
        $this->assertSame('needsChoice', $result['actions'][0]['resultado'] ?? null);
        $this->assertSame('renglonExistente', $result['pendingChoice']['kind'] ?? null);
        $this->assertCount(2, $result['pendingChoice']['options'] ?? []);
        $this->assertStringContainsString('cant 123', (string) ($result['pendingChoice']['options'][0]['label'] ?? ''));
    }

    public function testMutateNotFoundUsesConQReplyKeyAndPayload(): void
    {
        $tool = new CargaAsistenteArticuloTool(
            new ArticuloCargaLookupService(),
            new PedidosWebParameterService(),
        );

        $result = $tool->mutateExistingRenglon(
            $this->draftWithRenglones([
                [
                    'renglon' => 1,
                    'codArticulo' => 'ATS 0500',
                    'descripcion' => 'ALMENDRA TOSTADA',
                    'cantidad' => 10,
                    'precio' => 100,
                    'porcBonif' => 3,
                ],
            ], 'V'),
            'remove',
            'arroz',
        );

        $this->assertSame('pedidos.carga.asistente.renglonNoEncontradoConQ', $result['replyText']);
        $this->assertSame('arroz', $result['actions'][0]['payload']['q'] ?? null);
    }

    public function testMutateUpdateDeniedForClienteProfile(): void
    {
        $tool = new CargaAsistenteArticuloTool(
            new ArticuloCargaLookupService(),
            new PedidosWebParameterService(),
        );

        $result = $tool->mutateExistingRenglon(
            $this->draftWithRenglones([
                [
                    'renglon' => 1,
                    'codArticulo' => 'ABC',
                    'descripcion' => 'Articulo ABC',
                    'cantidad' => 1,
                    'precio' => 10,
                    'porcBonif' => 0,
                ],
            ], 'C'),
            'update',
            'ABC',
            false,
            null,
            1500.0,
            null,
        );

        $this->assertSame('pedidos.carga.asistente.denied', $result['replyText']);
        $this->assertSame('denied', $result['actions'][0]['resultado'] ?? null);
    }

    public function testCabeceraBonifDeniedForClienteProfile(): void
    {
        $tool = new CargaAsistenteCabeceraTool(new PedidosWebParameterService());
        $result = $tool->setCampoLibre('bonif1', 5, [
            'perfilUsuario' => 'C',
            'readOnly' => false,
            'codCliente' => 'C1',
            'cabecera' => [],
            'renglones' => [],
            'modo' => 'nuevo',
            'codLista' => 1,
        ]);

        $this->assertSame('pedidos.carga.asistente.denied', $result['replyText']);
        $this->assertSame('denied', $result['actions'][0]['resultado'] ?? null);
    }

    public function testConsultaFormattingDateOnlyAndTotals(): void
    {
        $this->assertSame('2026-07-13', CargaAsistenteConsultaFormatting::formatDateOnly('2026-07-13 15:30:00'));
        $this->assertSame('2026-08-13', CargaAsistenteConsultaFormatting::formatDateOnly('2026-08-13T10:00:00'));
        $this->assertNull(CargaAsistenteConsultaFormatting::formatDateOnly(null));

        $this->assertSame([], CargaAsistenteConsultaFormatting::totalsIfMultiple([
            ['saldo' => 10],
        ], 'saldo'));

        $this->assertSame(
            ['saldo' => 150.5],
            CargaAsistenteConsultaFormatting::totalsIfMultiple([
                ['saldo' => 100.5],
                ['saldo' => 50],
            ], 'saldo'),
        );

        $this->assertSame(
            ['importe' => 250.25],
            CargaAsistenteConsultaFormatting::totalsIfMultiple([
                ['importe' => 200],
                ['importe' => 50.25],
            ], 'importe'),
        );
    }

    /**
     * @param  list<array<string, mixed>>  $renglones
     * @return array{
     *     modo: string|null,
     *     perfilUsuario: string|null,
     *     codCliente: string|null,
     *     cabecera: array<string, mixed>,
     *     renglones: list<array<string, mixed>>,
     *     readOnly: bool,
     *     codLista: int
     * }
     */
    private function draftWithRenglones(array $renglones, string $perfilUsuario): array
    {
        return [
            'modo' => 'nuevo',
            'perfilUsuario' => $perfilUsuario,
            'codCliente' => 'C1',
            'cabecera' => [],
            'renglones' => $renglones,
            'readOnly' => false,
            'codLista' => 1,
        ];
    }
}
