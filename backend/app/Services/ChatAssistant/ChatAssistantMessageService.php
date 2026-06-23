<?php

namespace App\Services\ChatAssistant;

use App\Exceptions\ChatAssistantMessageException;
use App\Models\User;
use App\Services\ChatAssistant\Llm\ChatAssistantLlmGateway;
use App\Support\ChatAssistantConfigurationReadiness;
use App\Support\ChatAssistantImageAttachmentValidator;
use App\Support\ChatAssistantMessageErrorCodes;

final class ChatAssistantMessageService
{
    public function __construct(
        private readonly ChatAssistantConfigurationReadiness $configurationReadiness,
        private readonly ChatAssistantCorpusResolver $corpusResolver,
        private readonly ChatAssistantImageAttachmentValidator $imageAttachmentValidator,
        private readonly ChatAssistantLlmGateway $llmGateway,
    ) {}

    /**
     * @param  list<array{fileName?: mixed, mimeType?: mixed, contentBase64?: mixed}>  $images
     * @return array{
     *     reply: string,
     *     references: list<array{title: string, path: string}>,
     *     requiresSupportFollowup: bool
     * }
     */
    public function sendMessage(User $user, string $message, array $images = [], ?int $credentialId = null): array
    {
        $this->configurationReadiness->assertOperational($user, $credentialId);

        $normalizedImages = $this->imageAttachmentValidator->validateAndNormalize($images);

        if ($normalizedImages !== []) {
            $configuration = $this->configurationReadiness->getConfiguration($user, $credentialId);

            if (! $configuration['supportsVision']) {
                throw new ChatAssistantMessageException(
                    ChatAssistantMessageErrorCodes::visionUnsupported,
                    'chatAssistant.visionUnsupported',
                );
            }
        }

        $searchQuery = trim($message);

        if ($searchQuery === '' && $normalizedImages !== []) {
            $searchQuery = 'consulta con imagen adjunta';
        }

        $matches = $this->corpusResolver->searchRelevantDocuments($searchQuery);

        $reply = $this->llmGateway->generateReply(
            $user,
            $message,
            $matches,
            $normalizedImages,
            $credentialId,
        );

        if ($normalizedImages !== []) {
            $reply .= ' También recibí '.$this->formatImageCount(count($normalizedImages))
                .' para orientación visual; el análisis es orientativo y no reemplaza la revisión funcional.';
        }

        $reply .= ' Esta respuesta es orientativa y no reemplaza la validación funcional ni el soporte humano.';

        return [
            'reply' => trim($reply),
            'requiresSupportFollowup' => $matches === [],
        ];
    }

    private function formatImageCount(int $count): string
    {
        return $count === 1 ? '1 imagen' : $count.' imágenes';
    }
}
