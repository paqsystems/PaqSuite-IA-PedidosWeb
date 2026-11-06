<?php

namespace Tests\Unit\Support;

use App\Support\Installation\PedidosWebSdkSqlDeployer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PedidosWebSdkSqlDeployerTest extends TestCase
{
    #[Test]
    public function splitGoBatchesSeparaLotesSqlServer(): void
    {
        $sql = <<<'SQL'
CREATE OR ALTER PROCEDURE dbo.pq_sp_emission_process_get
AS
BEGIN
    SELECT 1;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_emission_parameter_get
AS
BEGIN
    SELECT 2;
END;
GO
SQL;

        $batches = PedidosWebSdkSqlDeployer::splitGoBatches($sql);

        $this->assertCount(2, $batches);
        $this->assertStringContainsString('pq_sp_emission_process_get', $batches[0]);
        $this->assertStringContainsString('pq_sp_emission_parameter_get', $batches[1]);
    }

    #[Test]
    public function isMissingEmissionSqlDetectaSpAusente(): void
    {
        $exception = new \RuntimeException(
            "SQLSTATE[42000]: Could not find stored procedure 'dbo.pq_sp_emission_process_get'."
        );

        $this->assertTrue(PedidosWebSdkSqlDeployer::isMissingEmissionSql($exception));
        $this->assertFalse(PedidosWebSdkSqlDeployer::isMissingEmissionSql(new \RuntimeException('other')));
    }
}
