<?php

namespace Tests\Feature\Gen;

use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class Gen13TaskRoutesSmokeTest extends TestCase
{
    #[Test]
    public function task_routes_are_registered_when_enabled(): void
    {
        if (! config('paqsuite.tasks.routesEnabled', false)) {
            $this->markTestSkipped('PAQSUITE_TASKS_ROUTES_ENABLED=false');
        }

        $uris = collect(Route::getRoutes())->map(static fn ($route) => $route->uri())->all();

        $this->assertContains('api/v1/task-processes', $uris);
        $this->assertContains('api/v1/task-definitions', $uris);
        $this->assertContains('api/v1/task-executions', $uris);
    }
}
