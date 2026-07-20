<?php

namespace Tests\Unit\Services\PedidosWeb\CargaAsistente;

use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteClienteTool;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

final class CargaAsistenteClienteSpeechFallbackTest extends TestCase
{
    public function testSpeechAlternatesCoverVernasconiToBernascone(): void
    {
        $reflection = new ReflectionClass(CargaAsistenteClienteTool::class);
        /** @var CargaAsistenteClienteTool $tool */
        $tool = $reflection->newInstanceWithoutConstructor();

        $method = new ReflectionMethod(CargaAsistenteClienteTool::class, 'speechAlternateClienteQueries');
        $method->setAccessible(true);

        /** @var list<string> $alts */
        $alts = $method->invoke($tool, 'vernasconi');
        $normalized = array_map(static fn (string $value): string => mb_strtolower($value), $alts);

        $this->assertContains('bernasconi', $normalized);
        $this->assertContains('bernascone', $normalized);
        $this->assertContains('vernascone', $normalized);
    }
}
