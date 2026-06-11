<?php

namespace Tests\Unit\Services\Pivots;

use App\Exceptions\PivotFlowException;
use App\Services\Pivots\PivotMetadataResolver;
use App\Support\PivotErrorCodes;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PivotMetadataResolverTest extends TestCase
{
    private PivotMetadataResolver $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resolver = new PivotMetadataResolver();
    }

    #[Test]
    public function assertPivotBaseIntegrityRejectsUnknownCampo(): void
    {
        try {
            $this->resolver->assertPivotBaseIntegrity(
            [
                'filas' => ['campoInexistente'],
                'columnas' => [],
                'valores' => [],
            ],
            [
                ['campoId' => 'codCliente', 'dataField' => 'codCliente', 'caption' => 'Cliente'],
            ]
            );
            $this->fail('Se esperaba PivotFlowException');
        } catch (PivotFlowException $exception) {
            $this->assertSame(PivotErrorCodes::metadataInvalid, $exception->errorCode());
        }
    }

    #[Test]
    public function buildRestriccionesMergesCatalogRules(): void
    {
        $validaciones = [
            (object) [
                'tipo_validacion' => 'restricciones',
                'configuracion_json' => json_encode([
                    'maximoFilas' => 3,
                    'maximoRegistrosBase' => 1000,
                ]),
            ],
        ];

        $restricciones = $this->resolver->buildRestricciones($validaciones);

        $this->assertSame(3, $restricciones['maximoFilas']);
        $this->assertSame(1000, $restricciones['maximoRegistrosBase']);
        $this->assertTrue($restricciones['bloquearSiExcedeVolumen']);
    }
}
