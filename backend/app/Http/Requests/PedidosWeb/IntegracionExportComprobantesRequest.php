<?php

namespace App\Http\Requests\PedidosWeb;

use App\Http\Responses\ApiResponse;
use App\Services\PedidosWeb\Integracion\Export\EstadoRangosParser;
use App\Services\PedidosWeb\Integracion\Export\ExportComprobantesQuery;
use App\Support\AuthErrorCodes;
use App\Support\ConsultaPaginacion;
use App\Support\PedidosWeb\IntegracionExportFechaRange;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator as IlluminateValidator;

final class IntegracionExportComprobantesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('page_size') && ! $this->has('pageSize')) {
            $this->merge(['pageSize' => $this->input('page_size')]);
        }

        $this->merge([
            'estadoRangosTokens' => EstadoRangosParser::normalize($this->query('estadoRangos')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'estadoRangosTokens' => ['required', 'array', 'min:1'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'pageSize' => ['sometimes', 'integer', 'min:1', 'max:'.ConsultaPaginacion::MAX_PAGE_SIZE],
            'codCliente' => ['sometimes', 'nullable', 'string', 'max:32'],
            'codPedido' => ['sometimes', 'nullable', 'string', 'max:64'],
            'fechaDesde' => ['sometimes', 'nullable', 'date'],
            'fechaHasta' => ['sometimes', 'nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'estadoRangosTokens.required' => 'integracion.export.estadoRangosRequerido',
            'estadoRangosTokens.min' => 'integracion.export.estadoRangosRequerido',
            'page.min' => 'integracion.export.paginacionInvalida',
            'pageSize.min' => 'integracion.export.paginacionInvalida',
            'pageSize.max' => 'integracion.export.paginacionInvalida',
            'fechaDesde.date' => 'integracion.export.rangoFechasInvalido',
            'fechaHasta.date' => 'integracion.export.rangoFechasInvalido',
        ];
    }

    public function withValidator(IlluminateValidator $validator): void
    {
        $validator->after(function (IlluminateValidator $validator): void {
            $tokens = $this->input('estadoRangosTokens', []);
            if (is_array($tokens) && $tokens !== []) {
                try {
                    EstadoRangosParser::parsePairs($tokens);
                } catch (\InvalidArgumentException $exception) {
                    $validator->errors()->add('estadoRangosTokens', $exception->getMessage());
                }
            }

            $bounds = IntegracionExportFechaRange::resolveBounds(
                $this->input('fechaDesde'),
                $this->input('fechaHasta'),
            );
            if ($bounds['fechaDesde'] !== null && $bounds['fechaHasta'] !== null
                && $bounds['fechaDesde']->gt($bounds['fechaHasta'])) {
                $validator->errors()->add('fechaHasta', 'integracion.export.rangoFechasInvalido');
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        $messages = $validator->errors()->all();
        $respuesta = $messages[0] ?? 'integracion.export.paginacionInvalida';

        throw new HttpResponseException(ApiResponse::error(
            AuthErrorCodes::validationFailed,
            $respuesta,
            422,
        ));
    }

    public function toQuery(): ExportComprobantesQuery
    {
        /** @var list<string> $tokens */
        $tokens = $this->input('estadoRangosTokens', []);
        $pairs = EstadoRangosParser::parsePairs($tokens);

        $bounds = IntegracionExportFechaRange::resolveBounds(
            $this->input('fechaDesde'),
            $this->input('fechaHasta'),
        );

        return new ExportComprobantesQuery(
            $pairs,
            ConsultaPaginacion::resolvePage($this->input('page')),
            ConsultaPaginacion::resolvePageSize($this->input('pageSize')),
            filled($this->input('codCliente')) ? (string) $this->input('codCliente') : null,
            filled($this->input('codPedido')) ? (string) $this->input('codPedido') : null,
            $bounds['fechaDesde'],
            $bounds['fechaHasta'],
        );
    }

    /**
     * @return list<array{desde: int, hasta: int}>
     */
    public function estadoRangoPairs(): array
    {
        /** @var list<string> $tokens */
        $tokens = $this->input('estadoRangosTokens', []);

        return EstadoRangosParser::parsePairs($tokens);
    }
}