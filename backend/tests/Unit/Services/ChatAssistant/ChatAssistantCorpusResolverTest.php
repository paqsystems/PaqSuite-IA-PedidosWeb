<?php

namespace Tests\Unit\Services\ChatAssistant;

use App\Services\ChatAssistant\ChatAssistantCorpusResolver;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ChatAssistantCorpusResolverTest extends TestCase
{
    #[Test]
    public function it_returns_leyendas_faq_excerpt_for_client_legends_question(): void
    {
        $resolver = new ChatAssistantCorpusResolver();

        $matches = $resolver->searchRelevantDocuments(
            '¿Por qué no aparecen las leyendas que tiene cargadas el cliente?',
            3,
        );

        $this->assertNotEmpty($matches);

        $pedidosWeb = collect($matches)->first(
            static fn (array $match): bool => str_ends_with($match['path'], '99-manual-usuario/PedidosWeb.md'),
        );

        $this->assertNotNull($pedidosWeb, 'PedidosWeb.md debe figurar entre los documentos relevantes');
        $this->assertSame(
            $matches[0]['path'],
            $pedidosWeb['path'],
            'PedidosWeb.md debe rankear primero para consultas sobre leyendas',
        );

        $excerpt = mb_strtolower($pedidosWeb['excerpt']);

        $this->assertTrue(
            str_contains($excerpt, 'inicializar leyenda')
            || str_contains($excerpt, 'leyendas al pie'),
            'El extracto debe incluir la sección de leyendas o la FAQ correspondiente',
        );
        $this->assertStringContainsString('cliente', $excerpt);
    }

    #[Test]
    public function it_returns_presupuesto_to_pedido_faq_excerpt_for_conversion_question(): void
    {
        $resolver = new ChatAssistantCorpusResolver();

        $matches = $resolver->searchRelevantDocuments(
            '¿Cómo puedo pasar de un presupuesto a un pedido?',
            3,
        );

        $this->assertNotEmpty($matches);

        $pedidosWeb = collect($matches)->first(
            static fn (array $match): bool => str_ends_with($match['path'], '99-manual-usuario/PedidosWeb.md'),
        );

        $this->assertNotNull($pedidosWeb, 'PedidosWeb.md debe figurar entre los documentos relevantes');
        $this->assertSame(
            $matches[0]['path'],
            $pedidosWeb['path'],
            'PedidosWeb.md debe rankear primero para consultas de conversión presupuesto → pedido',
        );

        $excerpt = mb_strtolower($pedidosWeb['excerpt']);

        $this->assertStringContainsString('presupuestos ingresados', $excerpt);
        $this->assertTrue(
            str_contains($excerpt, 'convertir a pedido')
            || str_contains($excerpt, 'paso un presupuesto a pedido'),
            'El extracto debe incluir la sección o FAQ de conversión presupuesto → pedido',
        );
    }

    #[Test]
    public function it_returns_pedido_to_presupuesto_faq_excerpt_for_conversion_question(): void
    {
        $resolver = new ChatAssistantCorpusResolver();

        $matches = $resolver->searchRelevantDocuments(
            '¿Y puedo pasar un pedido a estado de presupuesto?',
            3,
        );

        $this->assertNotEmpty($matches);

        $pedidosWeb = collect($matches)->first(
            static fn (array $match): bool => str_ends_with($match['path'], '99-manual-usuario/PedidosWeb.md'),
        );

        $this->assertNotNull($pedidosWeb, 'PedidosWeb.md debe figurar entre los documentos relevantes');
        $this->assertSame(
            $matches[0]['path'],
            $pedidosWeb['path'],
            'PedidosWeb.md debe rankear primero para consultas de conversión pedido → presupuesto',
        );

        $excerpt = mb_strtolower($pedidosWeb['excerpt']);

        $this->assertStringContainsString('pedidos ingresados', $excerpt);
        $this->assertTrue(
            str_contains($excerpt, 'grabar presupuesto')
            || str_contains($excerpt, 'pasar un pedido a presupuesto'),
            'El extracto debe incluir la sección o FAQ de conversión pedido → presupuesto',
        );
        $this->assertStringNotContainsString('presupuestos ingresados', $excerpt);
    }

    #[Test]
    public function it_returns_articulo_disponibilidad_faq_excerpt_for_article_numbers_question(): void
    {
        $resolver = new ChatAssistantCorpusResolver();

        $matches = $resolver->searchRelevantDocuments(
            '¿Qué son los dos números que aparecen en la lista de artículos, junto al código y la descripción, en la carga de pedidos?',
            3,
        );

        $this->assertNotEmpty($matches);

        $pedidosWeb = collect($matches)->first(
            static fn (array $match): bool => str_ends_with($match['path'], '99-manual-usuario/PedidosWeb.md'),
        );

        $this->assertNotNull($pedidosWeb, 'PedidosWeb.md debe figurar entre los documentos relevantes');
        $this->assertSame(
            $matches[0]['path'],
            $pedidosWeb['path'],
            'PedidosWeb.md debe rankear primero para consultas sobre números en lista de artículos',
        );

        $excerpt = mb_strtolower($pedidosWeb['excerpt']);

        $this->assertTrue(
            str_contains($excerpt, 'disponible neto')
            || str_contains($excerpt, 'dos números')
            || str_contains($excerpt, 'dos numeros'),
            'El extracto debe explicar el disponible neto o la FAQ de los dos números',
        );
        $this->assertStringContainsString('base', $excerpt);
        $this->assertStringNotContainsString('convertir pedido a presupuesto', $excerpt);
    }
}
