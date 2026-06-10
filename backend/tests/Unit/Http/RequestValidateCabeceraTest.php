<?php

namespace Tests\Unit\Http;

use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class RequestValidateCabeceraTest extends TestCase
{
    #[Test]
    public function validateSoloConservaClavesDeclaradasEnCabeceraAnidada(): void
    {
        $request = Request::create('/test', 'POST', [
            'cabecera' => [
                'cod_cliente' => '101074',
                'cod_vended' => 'JCB',
                'lista_precios' => 1,
            ],
            'renglones' => [['cod_articulo' => 'AMC']],
        ]);

        $validated = $request->validate([
            'cabecera' => ['required', 'array'],
            'cabecera.cod_cliente' => ['required', 'string'],
            'renglones' => ['required', 'array', 'min:1'],
        ]);

        $this->assertSame(['cod_cliente' => '101074'], $validated['cabecera']);
    }
}
