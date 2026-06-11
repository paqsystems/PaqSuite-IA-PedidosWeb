<?php

namespace Tests\Feature\Api\Pivots;

use App\Models\PqPivotConfig;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\Support\AuthenticatesPaqTenant;
use Tests\Support\SeedsPedidosWebFeatureData;
use Tests\Support\SeedsPivotCatalog;
use Tests\TestCase;

final class PivotConfigFeatureTest extends TestCase
{
    use AuthenticatesPaqTenant;
    use SeedsPedidosWebFeatureData;
    use SeedsPivotCatalog;

    private const consultaId = 'CONSULTA_PILOTO_PIVOT';

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpAuthenticatesPaqTenant();
        $this->setUpPedidosWebFeature();
        $this->seedPivotCatalog();
    }

    public function testListConfigsRequiresAuth(): void
    {
        $this->getJson('/api/v1/pivot-configs?consultaId='.self::consultaId, $this->tenantHeaders())
            ->assertUnauthorized();
    }

    public function testFirstVisitActiveReturnsPivotBase(): void
    {
        Sanctum::actingAs($this->clienteUser());

        $this->getJson('/api/v1/pivot-configs/active?consultaId='.self::consultaId, $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.configId', null)
            ->assertJsonPath('resultado.restoreMode', 'pivotBase');
    }

    public function testCreateListActiveAndUpdateConfig(): void
    {
        Sanctum::actingAs($this->clienteUser());

        $configuracionJson = [
            'fields' => [
                ['dataField' => 'codCliente', 'area' => 'row'],
            ],
        ];

        $create = $this->postJson('/api/v1/pivot-configs', [
            'consultaId' => self::consultaId,
            'nombre' => 'Mi diseño',
            'configuracionJson' => $configuracionJson,
        ], $this->tenantHeaders());

        $create->assertCreated()
            ->assertJsonPath('resultado.nombre', 'Mi diseño')
            ->assertJsonPath('resultado.restoreMode', 'saved');

        $configId = (int) $create->json('resultado.configId');

        $this->getJson('/api/v1/pivot-configs?consultaId='.self::consultaId, $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.items.0.nombre', 'Mi diseño')
            ->assertJsonPath('resultado.items.0.isOwner', true);

        $this->getJson('/api/v1/pivot-configs/active?consultaId='.self::consultaId, $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.configId', $configId)
            ->assertJsonPath('resultado.configuracionJson.fields.0.dataField', 'codCliente');

        $updatedJson = [
            'fields' => [
                ['dataField' => 'cantidad', 'area' => 'data', 'summaryType' => 'sum'],
            ],
        ];

        $this->putJson("/api/v1/pivot-configs/{$configId}", [
            'configuracionJson' => $updatedJson,
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.configuracionJson.fields.0.dataField', 'cantidad');
    }

    public function testDuplicateConfigNameReturns409(): void
    {
        Sanctum::actingAs($this->clienteUser());
        $configuracionJson = ['fields' => []];

        $this->postJson('/api/v1/pivot-configs', [
            'consultaId' => self::consultaId,
            'nombre' => 'Duplicado',
            'configuracionJson' => $configuracionJson,
        ], $this->tenantHeaders())->assertCreated();

        $this->postJson('/api/v1/pivot-configs', [
            'consultaId' => self::consultaId,
            'nombre' => 'Duplicado',
            'configuracionJson' => $configuracionJson,
        ], $this->tenantHeaders())
            ->assertStatus(409)
            ->assertJsonPath('error', 2001)
            ->assertJsonPath('respuesta', 'pivotLayout.duplicateName');
    }

    public function testNonOwnerCannotUpdateOrDelete(): void
    {
        $owner = $this->clienteUser();
        $other = $this->supervisorUser();

        $config = PqPivotConfig::query()->create([
            'consulta_id' => self::consultaId,
            'nombre' => 'Solo owner',
            'configuracion_json' => json_encode(['fields' => []], JSON_THROW_ON_ERROR),
            'version_definicion_consulta' => 1,
            'created_by_user_id' => $owner->id,
            'eliminado' => false,
            'activo' => true,
        ]);

        Sanctum::actingAs($other);

        $this->putJson("/api/v1/pivot-configs/{$config->pivot_id}", [
            'configuracionJson' => ['fields' => [['area' => 'row']]],
        ], $this->tenantHeaders())
            ->assertForbidden()
            ->assertJsonPath('error', 3001);

        $this->deleteJson("/api/v1/pivot-configs/{$config->pivot_id}", [], $this->tenantHeaders())
            ->assertForbidden()
            ->assertJsonPath('error', 3001);
    }

    public function testSetActiveConfigRoundTrip(): void
    {
        Sanctum::actingAs($this->clienteUser());

        $create = $this->postJson('/api/v1/pivot-configs', [
            'consultaId' => self::consultaId,
            'nombre' => 'Activo',
            'configuracionJson' => ['fields' => [['dataField' => 'codCliente', 'area' => 'row']]],
        ], $this->tenantHeaders())->assertCreated();

        $configId = (int) $create->json('resultado.configId');

        $this->putJson('/api/v1/pivot-configs/active', [
            'consultaId' => self::consultaId,
            'configId' => null,
        ], $this->tenantHeaders())->assertOk();

        $this->getJson('/api/v1/pivot-configs/active?consultaId='.self::consultaId, $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.restoreMode', 'empty');

        $this->putJson('/api/v1/pivot-configs/active', [
            'consultaId' => self::consultaId,
            'configId' => $configId,
        ], $this->tenantHeaders())->assertOk();

        $this->getJson('/api/v1/pivot-configs/active?consultaId='.self::consultaId, $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.configId', $configId)
            ->assertJsonPath('resultado.restoreMode', 'saved');
    }

    public function testSupervisorSeedIncludesVistaResumen(): void
    {
        Sanctum::actingAs($this->supervisorUser());

        $this->getJson('/api/v1/pivot-configs?consultaId='.self::consultaId, $this->tenantHeaders())
            ->assertOk()
            ->assertJsonFragment(['nombre' => 'Vista resumen']);
    }

    public function testPublicConfigExposesPivotLayoutsFlag(): void
    {
        Sanctum::actingAs($this->clienteUser());

        $this->getJson('/api/v1/config/public', $this->tenantHeaders())
            ->assertOk()
            ->assertJsonStructure([
                'resultado' => ['pivotsEnabled', 'pivotLayoutsEnabled'],
            ]);
    }

    private function clienteUser(): User
    {
        return User::query()->where('codigo', 'cliente.mvp')->firstOrFail();
    }

    private function supervisorUser(): User
    {
        return User::query()->where('codigo', 'supervisor.mvp')->firstOrFail();
    }
}
