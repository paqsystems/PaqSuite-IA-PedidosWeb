<?php

namespace Tests\Unit\PedidosWeb\Models;

use App\Models\PqPedidoswebPedidoCabecera;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class PqPedidoswebPedidoCabeceraTest extends TestCase
{
    #[Test]
    public function usaFormatoFechaSqlServerInequívoco(): void
    {
        $model = new PqPedidoswebPedidoCabecera();

        $this->assertSame('Ymd H:i:s', $model->getDateFormat());
    }
}
