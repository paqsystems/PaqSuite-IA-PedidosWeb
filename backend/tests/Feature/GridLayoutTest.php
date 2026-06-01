<?php

namespace Tests\Feature;

use App\Models\PqGridLayout;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class GridLayoutTest extends TestCase
{
    public function testListLayoutsRequiresAuth(): void
    {
        $this->getJson('/api/v1/grid-layouts?proceso=pw_dashboard&gridId=main', $this->tenantHeaders())
            ->assertUnauthorized();
    }

    public function testCreateListActiveAndUpdateLayout(): void
    {
        $user = $this->mvpUser();
        Sanctum::actingAs($user);

        $state = ['columns' => [['dataField' => 'name', 'visible' => true]]];

        $create = $this->postJson('/api/v1/grid-layouts', [
            'proceso' => 'pw_dashboard',
            'gridId' => 'main',
            'layoutName' => 'Mi vista',
            'stateJson' => $state,
        ], $this->tenantHeaders());

        $create->assertCreated()
            ->assertJsonPath('resultado.layoutName', 'Mi vista');

        $layoutId = (int) $create->json('resultado.layoutId');

        $this->getJson('/api/v1/grid-layouts?proceso=pw_dashboard&gridId=main', $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.items.0.layoutName', 'Mi vista')
            ->assertJsonPath('resultado.items.0.isOwner', true);

        $this->getJson('/api/v1/grid-layouts/active?proceso=pw_dashboard&gridId=main', $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.layoutId', $layoutId)
            ->assertJsonPath('resultado.stateJson.columns.0.dataField', 'name');

        $updatedState = ['columns' => [['dataField' => 'name', 'visible' => false]]];

        $this->putJson("/api/v1/grid-layouts/{$layoutId}", [
            'stateJson' => $updatedState,
        ], $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.stateJson.columns.0.visible', false);
    }

    public function testDuplicateLayoutNameReturns409(): void
    {
        $user = $this->mvpUser();
        Sanctum::actingAs($user);
        $state = ['columns' => []];

        $this->postJson('/api/v1/grid-layouts', [
            'proceso' => 'pw_dashboard',
            'gridId' => 'main',
            'layoutName' => 'Duplicado',
            'stateJson' => $state,
        ], $this->tenantHeaders())->assertCreated();

        $this->postJson('/api/v1/grid-layouts', [
            'proceso' => 'pw_dashboard',
            'gridId' => 'main',
            'layoutName' => 'Duplicado',
            'stateJson' => $state,
        ], $this->tenantHeaders())
            ->assertStatus(409)
            ->assertJsonPath('error', 2001)
            ->assertJsonPath('respuesta', 'gridLayout.duplicateName');
    }

    public function testNonOwnerCannotUpdateOrDelete(): void
    {
        $owner = $this->mvpUser();
        $other = User::query()->where('codigo', 'supervisor.mvp')->firstOrFail();

        $layout = PqGridLayout::query()->create([
            'proceso' => 'pw_dashboard',
            'grid_id' => 'main',
            'layout_name' => 'Solo owner',
            'created_by_user_id' => $owner->id,
            'state_json' => json_encode(['columns' => []], JSON_THROW_ON_ERROR),
        ]);

        Sanctum::actingAs($other);

        $this->putJson("/api/v1/grid-layouts/{$layout->id}", [
            'stateJson' => ['columns' => [['visible' => false]]],
        ], $this->tenantHeaders())
            ->assertForbidden()
            ->assertJsonPath('error', 3001);

        $this->deleteJson("/api/v1/grid-layouts/{$layout->id}", [], $this->tenantHeaders())
            ->assertForbidden()
            ->assertJsonPath('error', 3001);
    }

    public function testSetActiveLayoutRoundTrip(): void
    {
        $user = $this->mvpUser();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/v1/grid-layouts', [
            'proceso' => 'pw_dashboard',
            'gridId' => 'main',
            'layoutName' => 'Activo',
            'stateJson' => ['columns' => [['dataField' => 'id']]],
        ], $this->tenantHeaders())->assertCreated();

        $layoutId = (int) $create->json('resultado.layoutId');

        $this->putJson('/api/v1/grid-layouts/active', [
            'proceso' => 'pw_dashboard',
            'gridId' => 'main',
            'layoutId' => null,
        ], $this->tenantHeaders())->assertOk();

        $this->putJson('/api/v1/grid-layouts/active', [
            'proceso' => 'pw_dashboard',
            'gridId' => 'main',
            'layoutId' => $layoutId,
        ], $this->tenantHeaders())->assertOk();

        $this->getJson('/api/v1/grid-layouts/active?proceso=pw_dashboard&gridId=main', $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.layoutId', $layoutId);
    }

    public function testPublicConfigExposesGridLayoutsFlag(): void
    {
        $user = $this->mvpUser();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/config/public', $this->tenantHeaders())
            ->assertOk()
            ->assertJsonPath('resultado.gridLayoutsEnabled', true);
    }

    /**
     * @return array<string, string>
     */
    private function tenantHeaders(): array
    {
        return [
            'X-Paq-Cliente' => 'desarrollo',
        ];
    }

    private function mvpUser(): User
    {
        return User::query()->where('codigo', 'cliente.mvp')->firstOrFail();
    }
}
