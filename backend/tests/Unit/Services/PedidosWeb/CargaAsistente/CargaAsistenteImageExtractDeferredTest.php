<?php

namespace Tests\Unit\Services\PedidosWeb\CargaAsistente;

use App\Services\ChatAssistant\Llm\ChatAssistantLlmGateway;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteArticuloTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteCabeceraTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteClienteTool;
use App\Services\PedidosWeb\CargaAsistente\Tools\CargaAsistenteImageExtractTool;
use Tests\TestCase;

final class CargaAsistenteImageExtractDeferredTest extends TestCase
{
    public function testAppendDeferredAfterChoiceAppliesRenglonesAfterSelectCliente(): void
    {
        $tool = new CargaAsistenteImageExtractTool(
            $this->createMock(ChatAssistantLlmGateway::class),
            app(CargaAsistenteArticuloTool::class),
            app(CargaAsistenteClienteTool::class),
            app(CargaAsistenteCabeceraTool::class),
        );

        $result = $tool->appendDeferredAfterChoice(
            [
                'replyText' => 'Cliente elegido.',
                'actions' => [
                    [
                        'action' => 'selectCliente',
                        'payload' => ['codCliente' => 'C99'],
                        'resultado' => 'ok',
                    ],
                ],
                'pendingChoice' => null,
                'configurationRequired' => false,
            ],
            [
                'transporteQ' => '',
                'renglonesValidos' => [
                    [
                        'codArticulo' => 'AJO 25',
                        'cantidad' => 100,
                        'precio' => 10,
                        'porcBonif' => 0,
                        'descripcion' => 'AJO EN POLVO25 KG',
                    ],
                ],
                'errores' => [],
            ],
            [
                'modo' => 'nuevo',
                'perfilUsuario' => 'V',
                'codCliente' => null,
                'cabecera' => [],
                'renglones' => [],
                'readOnly' => false,
                'codLista' => 1,
            ],
        );

        $this->assertNull($result['pendingChoice']);
        $this->assertCount(2, $result['actions']);
        $this->assertSame('selectCliente', $result['actions'][0]['action']);
        $this->assertSame('applyImageExtract', $result['actions'][1]['action']);
        $this->assertSame(
            'AJO 25',
            $result['actions'][1]['payload']['renglonesValidos'][0]['codArticulo'] ?? null,
        );
        $this->assertStringContainsString('1 renglón(es)', $result['replyText']);
    }
}
