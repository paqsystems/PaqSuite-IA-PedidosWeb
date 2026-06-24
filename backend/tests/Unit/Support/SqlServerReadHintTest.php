<?php

namespace Tests\Unit\Support;

use App\Support\SqlServerReadHint;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class SqlServerReadHintTest extends TestCase
{
    #[Test]
    public function fromAsGeneraHintNolock(): void
    {
        $fragment = SqlServerReadHint::fromAs('pq_pedidosweb_articulos', 'a');

        $this->assertSame('[pq_pedidosweb_articulos] AS [a] WITH (NOLOCK)', $fragment);
    }
}
