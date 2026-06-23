<?php

namespace App\Services\ChatAssistant\Llm;

final class ChatAssistantLlmPromptBuilder
{
    /**
     * @param  list<array{title: string, path: string, excerpt: string}>  $corpusMatches
     */
    public function buildSystemPrompt(array $corpusMatches): string
    {
        $sections = [
            'Sos el asistente de ayuda de PedidosWeb.',
            'Respondé en español con orientación operativa, clara y breve.',
            'Usá como fuente principal la documentación del producto incluida abajo.',
            'Si la documentación no alcanza para responder con seguridad, decilo explícitamente.',
            'No inventes pantallas, permisos, validaciones ni pasos que no estén respaldados.',
            'No prometas resolución garantizada ni reemplaces al soporte humano.',
        ];

        if ($corpusMatches !== []) {
            $sections[] = 'Documentación relevante:';

            foreach ($corpusMatches as $match) {
                $excerpt = trim($match['excerpt']);

                if ($excerpt === '') {
                    continue;
                }

                $sections[] = sprintf(
                    "---\nTítulo: %s\nRuta: %s\n%s\n---",
                    $match['title'],
                    $match['path'],
                    $excerpt,
                );
            }
        } else {
            $sections[] = 'No se encontró documentación aprobada directamente relacionada con la consulta.';
        }

        return implode("\n\n", $sections);
    }

    public function buildUserPrompt(string $message, bool $hasImages): string
    {
        $normalizedMessage = trim($message);

        if ($normalizedMessage !== '') {
            return $normalizedMessage;
        }

        if ($hasImages) {
            return 'El usuario adjuntó captura(s) de pantalla para orientación visual. '
                .'Describí lo que observes y orientá según la documentación disponible.';
        }

        return $normalizedMessage;
    }
}
