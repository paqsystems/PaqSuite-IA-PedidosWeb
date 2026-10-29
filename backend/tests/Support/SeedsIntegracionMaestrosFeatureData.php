<?php

namespace Tests\Support;

use App\Models\PqPermiso;
use App\Models\PqRol;
use App\Models\PqRolAtributo;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;

trait SeedsIntegracionMaestrosFeatureData
{
    use AuthenticatesPaqTenant;

    protected function setUpIntegracionMaestrosFeature(): void
    {
        $this->setUpAuthenticatesPaqTenant();

        if (! $this->provisionIntegracionUser()) {
            $this->markTestSkipped('No se pudo provisionar el usuario técnico de integración.');
        }

        foreach ([
            'pq_pedidosweb_integracion_maestros_stage',
            'pq_pedidosweb_logs_integracion',
        ] as $table) {
            if (! Schema::hasTable($table)) {
                $this->markTestSkipped("Tabla {$table} no disponible; ejecute el deploy SQL.");
            }
        }

        Mail::fake();
    }

    private function provisionIntegracionUser(): bool
    {
        try {
            $role = PqRol::query()->updateOrCreate(
                ['nombre_rol' => 'IntegracionErp'],
                ['descripcion_rol' => 'Usuario técnico integración ERP', 'acceso_total' => false],
            );

            $user = User::query()->where('codigo', 'integracion.erp.mvp')->first();
            $passwordHash = (string) ($user?->password_hash ?? '');
            if ($user === null || ! Hash::check((string) config('paqsuite_seed.mvpPassword'), $passwordHash)) {
                $passwordHash = Hash::make((string) config('paqsuite_seed.mvpPassword'));
            }

            $user = User::query()->updateOrCreate(
                ['codigo' => 'integracion.erp.mvp'],
                [
                    'name_user' => 'Integración ERP MVP',
                    'email' => 'integracion.erp.mvp@paqsuite.local',
                    'password_hash' => $passwordHash,
                    'activo' => true,
                    'inhabilitado' => false,
                    'first_login' => false,
                    'locale' => 'es-AR',
                    'theme' => 'light',
                ],
            );

            PqPermiso::query()->updateOrCreate(
                ['id_usuario' => $user->id],
                ['id_rol' => $role->id, 'id_empresa' => (int) config('paqsuite_seed.monoEmpresaId', 8)],
            );

            PqRolAtributo::query()->updateOrCreate(
                ['id_rol' => $role->id, 'procedimiento' => 'pw_integracion_importacion'],
                [
                    'permiso_alta' => true,
                    'permiso_baja' => false,
                    'permiso_modi' => false,
                    'permiso_repo' => false,
                ],
            );

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Los usuarios técnicos reciben tokens Sanctum por provisión; no necesitan
     * pasar por el login comercial del portal.
     *
     * @return array<string, string>
     */
    protected function integrationHeaders(): array
    {
        $user = User::query()->where('codigo', 'integracion.erp.mvp')->firstOrFail();

        return array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$user->createToken('integration-test')->plainTextToken,
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function actingAsUserHeaders(string $codigo): array
    {
        $user = User::query()->where('codigo', $codigo)->firstOrFail();
        Sanctum::actingAs($user);

        return $this->tenantHeaders();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    protected function importacionBatch(
        string $modo,
        array $items,
        ?string $importacionId = null,
        int $numeroLote = 1,
        bool $ultimoEnvio = true,
    ): array {
        static $sequence = 0;
        $sequence++;

        return [
            'importacionId' => $importacionId ?? ('TEST-IMP-'.$sequence.'-'.uniqid('', true)),
            'numeroLote' => $numeroLote,
            'ultimoEnvio' => $ultimoEnvio,
            'modo' => $modo,
            'fechaProceso' => '2026-10-22T12:00:00-03:00',
            'origen' => 'feature-test',
            'items' => $items,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function postImportacionAsync(string $path, array $payload, array $headers): array
    {
        $response = $this->postJson($path, $payload, $headers);
        $response->assertStatus(202)->assertJsonPath('error', 0);

        return (array) $response->json('resultado');
    }

    /**
     * @return array<string, mixed>
     */
    protected function getImportacionResultado(string $importacionId, array $headers): array
    {
        $response = $this->getJson('/api/v1/integracion/importaciones/'.$importacionId, $headers);
        $response->assertOk()->assertJsonPath('error', 0);

        return (array) $response->json('resultado');
    }
}
