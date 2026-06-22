<?php

namespace App\Support;

use App\Exceptions\ChatAssistantMessageException;

final class ChatAssistantImageAttachmentValidator
{
    public const MAX_IMAGES = 4;

    public const MAX_BYTES = 5 * 1024 * 1024;

    /**
     * @var list<string>
     */
    private const ALLOWED_MIME_TYPES = [
        'image/png',
        'image/jpeg',
        'image/webp',
    ];

    /**
     * @var list<string>
     */
    private const ALLOWED_EXTENSIONS = [
        'png',
        'jpg',
        'jpeg',
        'webp',
    ];

    /**
     * @param  list<array{fileName?: mixed, mimeType?: mixed, contentBase64?: mixed}>  $images
     * @return list<array{fileName: string, mimeType: string, content: string}>
     */
    public function validateAndNormalize(array $images): array
    {
        if ($images === []) {
            return [];
        }

        if (count($images) > self::MAX_IMAGES) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::imagesTooMany,
                'chatAssistant.imagesTooMany',
            );
        }

        $normalized = [];

        foreach ($images as $index => $image) {
            if (! is_array($image)) {
                throw new ChatAssistantMessageException(
                    ChatAssistantMessageErrorCodes::imageInvalidPayload,
                    'chatAssistant.imageInvalidPayload',
                );
            }

            $normalized[] = $this->validateSingleImage($image, (int) $index);
        }

        return $normalized;
    }

    /**
     * @param  array{fileName?: mixed, mimeType?: mixed, contentBase64?: mixed}  $image
     * @return array{fileName: string, mimeType: string, content: string}
     */
    private function validateSingleImage(array $image, int $index): array
    {
        $fileName = trim((string) ($image['fileName'] ?? ''));
        $mimeType = strtolower(trim((string) ($image['mimeType'] ?? '')));
        $contentBase64 = trim((string) ($image['contentBase64'] ?? ''));

        if ($fileName === '' || $mimeType === '' || $contentBase64 === '') {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::imageInvalidPayload,
                'chatAssistant.imageInvalidPayload',
            );
        }

        if (! $this->isAllowedExtension($fileName) || ! $this->isAllowedMimeType($mimeType)) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::imageInvalidFormat,
                'chatAssistant.imageInvalidFormat',
            );
        }

        $decoded = base64_decode($contentBase64, true);

        if ($decoded === false) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::imageInvalidPayload,
                'chatAssistant.imageInvalidPayload',
            );
        }

        $byteLength = strlen($decoded);

        if ($byteLength === 0 || $byteLength > self::MAX_BYTES) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::imageTooLarge,
                'chatAssistant.imageTooLarge',
            );
        }

        if (! $this->matchesDeclaredMimeType($decoded, $mimeType)) {
            throw new ChatAssistantMessageException(
                ChatAssistantMessageErrorCodes::imageInvalidFormat,
                'chatAssistant.imageInvalidFormat',
            );
        }

        return [
            'fileName' => $fileName,
            'mimeType' => $mimeType,
            'content' => $decoded,
        ];
    }

    private function isAllowedExtension(string $fileName): bool
    {
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }

    private function isAllowedMimeType(string $mimeType): bool
    {
        return in_array($mimeType, self::ALLOWED_MIME_TYPES, true);
    }

    private function matchesDeclaredMimeType(string $binaryContent, string $mimeType): bool
    {
        if (! function_exists('finfo_open')) {
            return true;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            return true;
        }

        $detectedMimeType = finfo_buffer($finfo, $binaryContent) ?: '';
        finfo_close($finfo);

        if ($detectedMimeType === '') {
            return true;
        }

        if ($detectedMimeType === $mimeType) {
            return true;
        }

        return $mimeType === 'image/jpeg' && $detectedMimeType === 'image/jpeg';
    }
}
