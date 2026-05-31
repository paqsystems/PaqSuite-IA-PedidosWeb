<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $minLength = (int) config('paqsuite_password.minLength', 8);
        $pattern = (string) config('paqsuite_password.pattern');

        return [
            'token' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:'.$minLength, 'regex:'.$pattern],
            'newPasswordConfirmation' => ['required', 'same:newPassword'],
        ];
    }
}
