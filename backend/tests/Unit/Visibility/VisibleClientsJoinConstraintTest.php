<?php

declare(strict_types=1);

namespace Tests\Unit\Visibility;

use App\Models\PqPedidoswebClienteContacto;
use App\Models\User;
use App\Services\Visibility\VisibleClientsResolver;
use Tests\TestCase;

final class VisibleClientsJoinConstraintTest extends TestCase
{
    public function testJoinVisibleClientsDoesNotBindAClientCodeList(): void
    {
        $user = User::query()->where('codigo', 'supervisor.mvp')->first();
        if ($user === null) {
            $this->markTestSkipped('Falta usuario supervisor.mvp');
        }

        $query = PqPedidoswebClienteContacto::query();
        app(VisibleClientsResolver::class)->joinVisibleClients(
            $query,
            $user,
            'pq_pedidosweb_clientescontactos.cod_client'
        );

        $sql = strtolower($query->toSql());

        $this->assertStringContainsString('join', $sql);
        $this->assertStringNotContainsString(' in (', $sql);
        $this->assertLessThan(20, count($query->getBindings()));
    }
}
