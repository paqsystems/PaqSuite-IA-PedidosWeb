<?php

namespace App\Http\Requests\PedidosWeb;

use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroPayloadGuard;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use App\Http\Responses\ApiResponse;
use App\Support\AuthErrorCodes;

final class IntegracionMaestroLoteRequest extends FormRequest
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
        $maxItems = (int) config('integracion_maestros.max_items_per_lote', 1500);

        return [
            'modo' => ['required', 'string', Rule::in(['delta', 'snapshot'])],
            'fechaProceso' => ['required', 'date'],
            'origen' => ['nullable', 'string', 'max:50'],
            'items' => ['present', 'array', 'max:'.$maxItems],
            'items.*' => ['array'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'modo.required' => 'integracion.maestro.modoInvalido',
            'modo.in' => 'integracion.maestro.modoInvalido',
            'fechaProceso.required' => 'integracion.maestro.jsonInvalido',
            'fechaProceso.date' => 'integracion.maestro.jsonInvalido',
            'items.required' => 'integracion.maestro.jsonInvalido',
            'items.max' => 'integracion.maestro.loteDemasiadoGrande',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $messages = $validator->errors()->all();
        $respuesta = $messages[0] ?? 'validation.failed';

        if ($validator->errors()->has('items') && str_contains((string) $validator->errors()->first('items'), 'max')) {
            $respuesta = 'integracion.maestro.loteDemasiadoGrande';
        }

        throw new HttpResponseException(
            ApiResponse::error(
                AuthErrorCodes::validationFailed,
                $respuesta,
                422,
            ),
        );
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $guard = app(IntegracionMaestroPayloadGuard::class);

            if ($guard->containsProhibitedSqlFragment($this->input('items'))) {
                $validator->errors()->add('items', 'integracion.maestro.payloadProhibido');
            }
        });
    }

}
