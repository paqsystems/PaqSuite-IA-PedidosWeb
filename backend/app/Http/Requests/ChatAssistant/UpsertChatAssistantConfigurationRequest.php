<?php

namespace App\Http\Requests\ChatAssistant;

use Illuminate\Foundation\Http\FormRequest;

final class UpsertChatAssistantConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'providerId' => ['required', 'string', 'max:50'],
            'modelId' => ['required', 'string', 'max:120'],
            'baseUrl' => ['nullable', 'string', 'max:255'],
            'apiKey' => ['nullable', 'string', 'max:500'],
        ];
    }
}
