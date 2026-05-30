<?php

namespace Tests\Feature;

use Tests\TestCase;

final class UserMenuTest extends TestCase
{
    private string $seedPassword;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seedPassword = (string) config('paqsuite_seed.mvpPassword');

        $this->artisan('paqsuite:seed-menus-mvp')->assertExitCode(0);
        $this->artisan('paqsuite:seed-seguridad-mvp')->assertExitCode(0);
    }

    public function testSupervisorReceivesAllEnabledMvpMenuItems(): void
    {
        $response = $this->getJson('/api/v1/user/menu', $this->authHeadersFor('supervisor.mvp'));

        $response->assertOk()
            ->assertJsonPath('error', 0);

        $items = $response->json('resultado');
        $this->assertIsArray($items);

        $returnedProcedimientos = collect($this->flattenProcedimientos($items));
        foreach (config('paqsuite_mvp.menuItems', []) as $menuItem) {
            $this->assertTrue(
                $returnedProcedimientos->contains($menuItem['procedimiento']),
                "Falta procedimiento {$menuItem['procedimiento']} en menu supervisor"
            );
        }
    }

    public function testVendedorAcotadoReceivesSubsetMenu(): void
    {
        $response = $this->getJson('/api/v1/user/menu', $this->authHeadersFor('vendedor.acotado.mvp'));

        $response->assertOk();

        $procedimientos = collect($response->json('resultado'))->pluck('procedimiento')->all();

        $this->assertSame(
            config('paqsuite_mvp.vendedorAcotadoProcedimientos'),
            $procedimientos
        );
    }

    public function testVendedorSinMenuReceivesEmptyTree(): void
    {
        $response = $this->getJson('/api/v1/user/menu', $this->authHeadersFor('vendedor.sinMenu.mvp'));

        $response->assertOk()
            ->assertJsonPath('resultado', []);
    }

    public function testClienteWithoutRolAtributosReceivesEmptyTree(): void
    {
        $response = $this->getJson('/api/v1/user/menu', $this->authHeadersFor('cliente.mvp'));

        $response->assertOk()
            ->assertJsonPath('resultado', []);
    }

    public function testMenuRequiresAuthentication(): void
    {
        $this->getJson('/api/v1/user/menu', $this->tenantHeaders())
            ->assertUnauthorized()
            ->assertJsonPath('respuesta', 'auth.unauthenticated');
    }

    public function testMenuRequiresValidTenant(): void
    {
        $token = $this->loginTokenFor('supervisor.mvp');

        $this->getJson('/api/v1/user/menu', [
            'X-Paq-Cliente' => 'tenant-invalido',
            'Authorization' => 'Bearer '.$token,
        ])->assertStatus(400)
            ->assertJsonPath('respuesta', 'tenant.invalid');
    }

    public function testMenuItemShapeMatchesContract(): void
    {
        $response = $this->getJson('/api/v1/user/menu', $this->authHeadersFor('vendedor.acotado.mvp'));

        $response->assertOk()
            ->assertJsonStructure([
                'resultado' => [
                    [
                        'id',
                        'menuKey',
                        'labelKey',
                        'text',
                        'routePath',
                        'procedimiento',
                        'tipoProceso',
                        'order',
                        'nodeType',
                        'children',
                    ],
                ],
            ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, string>
     */
    private function flattenProcedimientos(array $items): array
    {
        $procedimientos = [];

        foreach ($items as $item) {
            $procedimiento = (string) ($item['procedimiento'] ?? '');
            if ($procedimiento !== '') {
                $procedimientos[] = $procedimiento;
            }

            $children = $item['children'] ?? [];
            if (is_array($children) && $children !== []) {
                $procedimientos = array_merge($procedimientos, $this->flattenProcedimientos($children));
            }
        }

        return $procedimientos;
    }

    /**
     * @return array<string, string>
     */
    private function authHeadersFor(string $codigo): array
    {
        return array_merge($this->tenantHeaders(), [
            'Authorization' => 'Bearer '.$this->loginTokenFor($codigo),
        ]);
    }

    private function loginTokenFor(string $codigo): string
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'codigo' => $codigo,
            'password' => $this->seedPassword,
        ], $this->tenantHeaders());

        $response->assertOk();

        return (string) $response->json('resultado.token');
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
}
