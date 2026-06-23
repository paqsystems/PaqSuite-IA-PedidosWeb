<?php

namespace App\Http\Requests\ChatAssistant;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateChatAssistantConfigurationStatusRequest extends FormRequest
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
            'isEnabled' => ['required', 'boolean'],
            'credentialId' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
