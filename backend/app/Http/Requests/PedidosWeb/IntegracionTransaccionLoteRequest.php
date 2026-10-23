<?php

namespace App\Http\Requests\PedidosWeb;

use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\Integracion\Maestros\IntegracionMaestroPayloadGuard;
use App\Support\AuthErrorCodes;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

final class IntegracionTransaccionLoteRequest extends FormRequest
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
        return [
            'modo' => ['required', 'string', Rule::in(['delta', 'snapshot'])],
            'fechaProceso' => ['required', 'date'],
            'origen' => ['nullable', 'string', 'max:50'],
            'items' => ['present', 'array', 'max:'.(int) config('integracion_transacciones.max_items_per_lote', 1500)],
            'items.*' => ['array'],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $respuesta = $validator->errors()->first() ?: 'integracion.transaccion.jsonInvalido';

        if ($validator->errors()->has('items') && str_contains((string) $validator->errors()->first('items'), 'max')) {
            $respuesta = 'integracion.transaccion.loteDemasiadoGrande';
        }

        throw new HttpResponseException(
            ApiResponse::error(AuthErrorCodes::validationFailed, $respuesta, 422),
        );
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $guard = app(IntegracionMaestroPayloadGuard::class);

            if ($guard->containsProhibitedSqlFragment($this->input('items'))) {
                $validator->errors()->add('items', 'integracion.transaccion.payloadProhibido');
            }
        });
    }
}
