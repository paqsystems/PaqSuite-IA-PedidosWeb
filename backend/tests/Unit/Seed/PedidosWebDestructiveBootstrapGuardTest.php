<?php

namespace Tests\Unit\Seed;

use App\Services\Seed\PedidosWebDestructiveBootstrapGuard;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

final class PedidosWebDestructiveBootstrapGuardTest extends TestCase
{
    #[Test]
    public function blocksSharedErpDatabaseWithoutExplicitAllow(): void
    {
        config()->set('database.connections.sqlsrv.database', 'Ankas_del_sur');
        putenv('ALLOW_PEDIDOSWEB_DESTRUCTIVE_BOOTSTRAP=false');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ankas_del_sur');

        app(PedidosWebDestructiveBootstrapGuard::class)->assertAllowed('test');
    }

    #[Test]
    public function allowsDevDatabaseSuffix(): void
    {
        config()->set('database.connections.sqlsrv.database', 'pedidosweb_dev');
        putenv('ALLOW_PEDIDOSWEB_DESTRUCTIVE_BOOTSTRAP=false');

        app(PedidosWebDestructiveBootstrapGuard::class)->assertAllowed('test');

        $this->addToAssertionCount(1);
    }

    #[Test]
    public function allowsWhenExplicitEnvFlagIsTrue(): void
    {
        config()->set('database.connections.sqlsrv.database', 'Ankas_del_sur');
        putenv('ALLOW_PEDIDOSWEB_DESTRUCTIVE_BOOTSTRAP=true');

        app(PedidosWebDestructiveBootstrapGuard::class)->assertAllowed('test');

        $this->addToAssertionCount(1);
    }
}
