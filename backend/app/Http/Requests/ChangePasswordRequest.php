<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ChangePasswordRequest extends FormRequest
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
        $minLength = (int) config('paqsuite_password.minLength', 8);
        $pattern = (string) config('paqsuite_password.pattern');

        return [
            'currentPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:'.$minLength, 'regex:'.$pattern],
            'newPasswordConfirmation' => ['required', 'same:newPassword'],
        ];
    }
}
