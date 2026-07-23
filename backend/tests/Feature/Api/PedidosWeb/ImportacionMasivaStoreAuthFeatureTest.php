<?php

namespace Tests\Feature\Api\PedidosWeb;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\SeedsPedidosWebFeatureData;
use Tests\TestCase;

final class ImportacionMasivaStoreAuthFeatureTest extends TestCase
{
    use SeedsPedidosWebFeatureData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpPedidosWebFeature();
    }

    public function testUsuarioSoloConImportacionMasivaPuedeGrabarPedido(): void
    {
        if (! Schema::hasTable('pq_rol_atributos')) {
            $this->markTestSkipped('Tablas de seguridad no disponibles.');
        }

        $user = User::query()->where('codigo', 'vendedor.acotado.mvp')->firstOrFail();
        $rolId = DB::table('pq_permisos')->where('id_usuario', $user->id)->value('id_rol');

        DB::table('pq_rol_atributos')->updateOrInsert(
            [
                'id_rol' => $rolId,
                'procedimiento' => 'pw_importacionmasiva',
            ],
            [
                'alta' => true,
                'baja' => false,
                'modi' => false,
                'repo' => false,
            ]
        );

        DB::table('pq_rol_atributos')->updateOrInsert(
            [
                'id_rol' => $rolId,
                'procedimiento' => 'pw_cargapedidos',
            ],
            [
                'alta' => false,
                'baja' => false,
                'modi' => false,
                'repo' => false,
            ]
        );

        $payload = $this->sampleGrabacionPayload();

        $this->actingAs($user)
            ->postJson('/api/v1/pedidos', $payload, $this->tenantHeaders())
            ->assertOk();
    }
}
