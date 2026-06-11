<?php

namespace Tests\Feature\Api\Pivots;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\Support\AuthenticatesPaqTenant;
use Tests\Support\SeedsPedidosWebFeatureData;
use Tests\Support\SeedsPivotCatalog;
use Tests\TestCase;

final class PivotMetadataFeatureTest extends TestCase
{
    use AuthenticatesPaqTenant;
    use SeedsPedidosWebFeatureData;
    use SeedsPivotCatalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthenticatesPaqTenant();
        $this->setUpPedidosWebFeature();
        $this->seedPivotCatalog();
    }

    public function testMetadataRequiresAuth(): void
    {
        $this->getJson('/api/v1/pivots/consultas/CONSULTA_PILOTO_PIVOT/metadata', $this->tenantHeaders())
            ->assertUnauthorized();
    }

    public function testMetadataReturnsEffectiveDefinition(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $this->getJson('/api/v1/pivots/consultas/CONSULTA_PILOTO_PIVOT/metadata', $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.consultaId', 'CONSULTA_PILOTO_PIVOT')
            ->assertJsonPath('resultado.pivotHabilitado', true)
            ->assertJsonPath('resultado.admiteDrilldown', true)
            ->assertJsonPath('resultado.pivotBase.filas.0', 'codCliente')
            ->assertJsonPath('resultado.campos.0.caption', 'Cliente')
            ->assertJsonPath('resultado.campos.0.dataField', 'codCliente')
            ->assertJsonPath('resultado.campos.0.agregacionesPermitidas', ['count', 'min', 'max'])
            ->assertJsonPath('resultado.campos.0.rolesPermitidos', fn ($roles) => in_array('valor', $roles, true))
            ->assertJsonPath('resultado.filtrosGenerales.0.filtroId', 'codCliente');
    }

    public function testMetadataNotFoundReturns404(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $this->getJson('/api/v1/pivots/consultas/CONSULTA_INEXISTENTE/metadata', $this->tenantHeaders())
            ->assertNotFound()
            ->assertJsonPath('error', 4004)
            ->assertJsonPath('respuesta', 'pivot.consultaNotFound');
    }

    public function testSoloGrillaMetadataDisablesPivotToggle(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $this->getJson('/api/v1/pivots/consultas/CONSULTA_SOLO_GRILLA/metadata', $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.pivotHabilitado', false)
            ->assertJsonPath('resultado.configuracionGeneral.permiteCambiarAVistaPivot', false);
    }

    public function testDataRequiresMandatoryFilter(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        \Illuminate\Support\Facades\DB::table('pq_pivots_validaciones')->insert([
            'consulta_id' => 'CONSULTA_PILOTO_PIVOT',
            'tipo_validacion' => 'filtro_obligatorio',
            'configuracion_json' => json_encode([
                'filtroId' => 'campoTestObligatorio',
                'dataField' => 'campoTestObligatorio',
                'caption' => 'Campo test',
                'obligatorio' => true,
            ], JSON_THROW_ON_ERROR),
            'activo' => true,
        ]);

        $this->postJson('/api/v1/pivots/consultas/CONSULTA_PILOTO_PIVOT/data', [
            'filtros' => [],
            'pagina' => 1,
            'tamanoPagina' => 100,
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('error', 1000)
            ->assertJsonPath('respuesta', 'pivot.requiredFilterMissing');
    }

    public function testDataReturnsFlatRowsWithFilter(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $response = $this->postJson('/api/v1/pivots/consultas/CONSULTA_PILOTO_PIVOT/data', [
            'filtros' => ['codCliente' => 'CLI-VEN-A'],
            'pagina' => 1,
            'tamanoPagina' => 100,
        ], $this->tenantHeaders());

        $response->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonStructure([
                'resultado' => ['items', 'totalRegistros', 'truncado'],
            ]);
    }

    public function testValidateStructureRejectsExcessMetrics(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $valores = [];

        for ($index = 0; $index < 20; $index++) {
            $valores[] = ['campoId' => 'cantidad', 'agregacion' => 'sum'];
        }

        $this->postJson('/api/v1/pivots/consultas/CONSULTA_PILOTO_PIVOT/validate-structure', [
            'filas' => [],
            'columnas' => [],
            'valores' => $valores,
        ], $this->tenantHeaders())
            ->assertStatus(422)
            ->assertJsonPath('error', 3002)
            ->assertJsonPath('respuesta', 'pivot.structureInvalid');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function informesConsultaIdsProvider(): array
    {
        return [
            'detalle_pedidos' => ['CONSULTA_DETALLE_PEDIDOS'],
            'deuda' => ['CONSULTA_DEUDA'],
            'cheques' => ['CONSULTA_CHEQUES'],
            'stock' => ['CONSULTA_STOCK'],
        ];
    }

    /** @dataProvider informesConsultaIdsProvider */
    public function testInformesMetadataReturnsPivotEnabled(string $consultaId): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $this->getJson('/api/v1/pivots/consultas/'.$consultaId.'/metadata', $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('error', 0)
            ->assertJsonPath('resultado.consultaId', $consultaId)
            ->assertJsonPath('resultado.pivotHabilitado', true)
            ->assertJsonPath('resultado.configuracionGeneral.mostrarGrillaYPivot', true);
    }

    private function supervisorUser(): User
    {
        return User::query()->where('codigo', 'supervisor.mvp')->firstOrFail();
    }

}
