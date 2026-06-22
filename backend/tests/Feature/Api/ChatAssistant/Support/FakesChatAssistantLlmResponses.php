<?php

namespace Tests\Feature\Api\ChatAssistant\Support;

use Illuminate\Support\Facades\Http;

trait FakesChatAssistantLlmResponses
{
    protected function fakeChatAssistantLlmResponses(string $reply = 'Según la documentación disponible, orientación operativa para grabar pedidos.'): void
    {
        Http::fake(function (\Illuminate\Http\Client\Request $request) use ($reply) {
            $url = $request->url();

            if (str_contains($url, '/api/chat')) {
                return Http::response([
                    'message' => ['content' => $reply],
                ]);
            }

            if (str_contains($url, 'anthropic.com')) {
                return Http::response([
                    'content' => [
                        ['type' => 'text', 'text' => $reply],
                    ],
                ]);
            }

            if (str_contains($url, 'generativelanguage.googleapis.com')) {
                return Http::response([
                    'candidates' => [
                        [
                            'content' => [
                                'parts' => [
                                    ['text' => $reply],
                                ],
                            ],
                        ],
                    ],
                ]);
            }

            return Http::response([
                'choices' => [
                    [
                        'message' => [
                            'content' => $reply,
                        ],
                    ],
                ],
            ]);
        });
    }
}
