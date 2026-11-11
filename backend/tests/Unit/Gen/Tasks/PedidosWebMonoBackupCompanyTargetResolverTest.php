<?php

namespace Tests\Unit\Gen\Tasks;

use App\Repositories\Gen\Tasks\PedidosWebMonoBackupCompanyTargetResolver;
use PaqSuite\LaravelCore\Security\EmpresaAdminRepository;
use PaqSuite\LaravelCore\Tasks\Backup\EmpresaAdminBackupCompanyTargetResolver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PedidosWebMonoBackupCompanyTargetResolverTest extends TestCase
{
    #[Test]
    public function list_targets_falls_back_to_operational_database_in_mono_unified(): void
    {
        config([
            'paqsuite.tenancy' => 'single',
            'paqsuite.db' => 'unified',
            'paqsuite.installation.database_name' => 'paqsystems_pedidosweb_demo',
        ]);

        $repository = $this->createMock(EmpresaAdminRepository::class);
        $repository->method('listAll')->willReturn([]);

        $resolver = new PedidosWebMonoBackupCompanyTargetResolver(
            new EmpresaAdminBackupCompanyTargetResolver($repository),
        );

        $targets = $resolver->listTargets();

        $this->assertCount(1, $targets);
        $this->assertSame(1, $targets[0]->companyId);
        $this->assertSame('paqsystems_pedidosweb_demo', $targets[0]->databaseName);
    }
}
