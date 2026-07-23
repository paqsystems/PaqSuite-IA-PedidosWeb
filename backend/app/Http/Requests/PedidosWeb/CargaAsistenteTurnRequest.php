<?php

namespace App\Http\Requests\PedidosWeb;

use App\Exceptions\ChatAssistantMessageException;
use App\Support\ChatAssistantImageAttachmentValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class CargaAsistenteTurnRequest extends FormRequest
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
            'modality' => is_string($this->input('modality'))
                ? mb_strtolower(trim($this->input('modality')))
                : $this->input('modality'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['nullable', 'string'],
            'modality' => ['required', 'string', Rule::in(['texto', 'audio', 'imagen'])],
            'credentialId' => ['nullable', 'integer', 'min:1'],
            'pendingChoice' => ['nullable', 'array'],
            'draftContext' => ['required', 'array'],
            'draftContext.modo' => ['sometimes', 'nullable', 'string'],
            'draftContext.perfilUsuario' => ['sometimes', 'nullable', 'string'],
            'draftContext.codCliente' => ['sometimes', 'nullable'],
            'draftContext.cabecera' => ['sometimes', 'nullable', 'array'],
            'draftContext.renglones' => ['sometimes', 'nullable', 'array'],
            'draftContext.readOnly' => ['sometimes', 'boolean'],
            'draftContext.codLista' => ['sometimes', 'nullable', 'integer'],
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

    public function normalizedModality(): string
    {
        return (string) $this->input('modality');
    }

    public function normalizedCredentialId(): ?int
    {
        $credentialId = $this->input('credentialId');

        if ($credentialId === null || $credentialId === '') {
            return null;
        }

        return (int) $credentialId;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function normalizedPendingChoice(): ?array
    {
        $pendingChoice = $this->input('pendingChoice');

        return is_array($pendingChoice) ? $pendingChoice : null;
    }

    /**
     * @return array{
     *     modo: string|null,
     *     perfilUsuario: string|null,
     *     codCliente: string|null,
     *     cabecera: array<string, mixed>,
     *     renglones: list<array<string, mixed>>,
     *     readOnly: bool,
     *     codLista: int
     * }
     */
    public function normalizedDraftContext(): array
    {
        $draft = $this->input('draftContext', []);
        $draft = is_array($draft) ? $draft : [];

        $codCliente = $draft['codCliente'] ?? null;
        if ($codCliente !== null && $codCliente !== '') {
            $codCliente = trim((string) $codCliente);
        } else {
            $codCliente = null;
        }

        $cabecera = $draft['cabecera'] ?? [];
        $renglones = $draft['renglones'] ?? [];

        return [
            'modo' => isset($draft['modo']) ? (string) $draft['modo'] : null,
            'perfilUsuario' => isset($draft['perfilUsuario']) ? (string) $draft['perfilUsuario'] : null,
            'codCliente' => $codCliente,
            'cabecera' => is_array($cabecera) ? $cabecera : [],
            'renglones' => is_array($renglones) ? array_values($renglones) : [],
            'readOnly' => (bool) ($draft['readOnly'] ?? false),
            'codLista' => (int) ($draft['codLista'] ?? ($cabecera['listaPrecios'] ?? $cabecera['lista_precios'] ?? 0)),
        ];
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
