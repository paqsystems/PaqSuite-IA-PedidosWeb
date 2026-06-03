<?php

namespace Tests\Unit\Seed;

use App\Models\PqPedidoswebLogin;
use App\Models\User;
use App\Services\Seed\PedidoswebLoginFromUsersSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class PedidoswebLoginFromUsersSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testSyncMissingInsertsLoginForUserWithoutUsuarioRow(): void
    {
        if (! Schema::hasTable('pq_pedidosweb_login')) {
            $this->markTestSkipped('pq_pedidosweb_login no disponible.');
        }

        $user = User::factory()->create([
            'codigo' => 'sync.test.user',
            'email' => 'sync.test@paqsuite.local',
            'password_hash' => Hash::make('secret'),
            'activo' => true,
            'inhabilitado' => false,
            'first_login' => false,
        ]);

        $result = $this->app->make(PedidoswebLoginFromUsersSyncService::class)->syncMissing();

        $this->assertGreaterThanOrEqual(1, $result['inserted']);

        $this->assertDatabaseHas('pq_pedidosweb_login', [
            'usuario' => 'sync.test.user',
            'cod_usuario_web' => 'sync.test.user',
            'tipo_cuenta' => 'V',
        ]);

        PqPedidoswebLogin::query()->where('usuario', 'sync.test.user')->delete();
    }
}
