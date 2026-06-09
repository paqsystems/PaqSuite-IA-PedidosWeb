<?php

namespace Tests\Unit\PedidosWeb\Models;

use App\Models\PqPedidoswebArticulo;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PqPedidoswebArticuloScopeTest extends TestCase
{
    #[Test]
    public function excluirArticulosBaseCargaFiltraUsaEscB(): void
    {
        $query = PqPedidoswebArticulo::query()->excluirArticulosBaseCarga();
        $sql = $query->toSql();

        $this->assertStringContainsString('usa_esc', $sql);
        $this->assertStringContainsString('pw_art_presentacion', $sql);
        $this->assertSame('B', PqPedidoswebArticulo::MARCA_USA_ESC_BASE);
        $this->assertContains('B', $query->getBindings());
    }
}
