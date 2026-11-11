<?php

namespace Tests\Unit\Gen\Tasks;

use App\Repositories\Gen\Tasks\InMemoryMonoRdsBackupGateway;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class InMemoryMonoRdsBackupGatewayTest extends TestCase
{
    #[Test]
    public function start_export_returns_stable_task_id(): void
    {
        $gateway = new InMemoryMonoRdsBackupGateway();

        $first = $gateway->startExport('paq_demo', 'arn:aws:s3:::bucket/prefix', 'backup', true);
        $second = $gateway->startExport('paq_demo', 'arn:aws:s3:::bucket/prefix', 'backup', true);

        $this->assertArrayHasKey('taskId', $first);
        $this->assertSame($first['taskId'], $second['taskId']);
    }

    #[Test]
    public function get_task_status_reports_success(): void
    {
        $gateway = new InMemoryMonoRdsBackupGateway();
        $started = $gateway->startExport('demo_db', 'arn:aws:s3:::b/p', 'bk', false);

        $status = $gateway->getTaskStatus((string) $started['taskId']);

        $this->assertSame('success', $status['status']);
        $this->assertSame(100, $status['percentProgress']);
    }
}
