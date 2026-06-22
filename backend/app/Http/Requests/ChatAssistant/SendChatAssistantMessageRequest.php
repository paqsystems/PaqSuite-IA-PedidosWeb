<?php

namespace App\Http\Requests\ChatAssistant;

use App\Exceptions\ChatAssistantMessageException;
use App\Support\ChatAssistantImageAttachmentValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class SendChatAssistantMessageRequest extends FormRequest
{
    public const TEXT_ONLY_MAX_LENGTH = 2000;

    public const TEXT_WITH_IMAGES_MAX_LENGTH = 1000;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'message' => is_string($this->input('message')) ? $this->input('message') : '',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string'],
            'images' => ['sometimes', 'array', 'max:'.ChatAssistantImageAttachmentValidator::MAX_IMAGES],
            'images.*.fileName' => ['required_with:images', 'string', 'max:255'],
            'images.*.mimeType' => ['required_with:images', 'string', 'max:100'],
            'images.*.contentBase64' => ['required_with:images', 'string'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $message = trim((string) $this->input('message', ''));
            $images = $this->input('images', []);
            $hasImages = is_array($images) && $images !== [];

            if ($message === '' && ! $hasImages) {
                $validator->errors()->add('message', 'The message field is required when no images are provided.');

                return;
            }

            if (! $hasImages) {
                if (mb_strlen($message) > self::TEXT_ONLY_MAX_LENGTH) {
                    $validator->errors()->add(
                        'message',
                        'The message may not be greater than '.self::TEXT_ONLY_MAX_LENGTH.' characters.',
                    );
                }

                return;
            }

            if (mb_strlen($message) > self::TEXT_WITH_IMAGES_MAX_LENGTH) {
                $validator->errors()->add(
                    'message',
                    'The message may not be greater than '.self::TEXT_WITH_IMAGES_MAX_LENGTH.' characters when images are included.',
                );
            }

            try {
                app(ChatAssistantImageAttachmentValidator::class)->validateAndNormalize(
                    is_array($images) ? $images : [],
                );
            } catch (ChatAssistantMessageException $exception) {
                $validator->errors()->add('images', $exception->respuestaKey);
            }
        });
    }

    public function normalizedMessage(): string
    {
        return trim((string) $this->input('message', ''));
    }

    /**
     * @return list<array{fileName?: mixed, mimeType?: mixed, contentBase64?: mixed}>
     */
    public function normalizedImages(): array
    {
        $images = $this->input('images', []);

        return is_array($images) ? $images : [];
    }
}
