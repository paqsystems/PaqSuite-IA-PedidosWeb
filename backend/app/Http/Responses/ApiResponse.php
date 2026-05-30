<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Envelope JSON MONO: error / respuesta / resultado.
 *
 * @see docs/00-contexto/_mono/00-arquitectura-api/envelope-respuestas.md
 */
final class ApiResponse
{
    public static function success(
        array $resultado = [],
        string $respuesta = 'ok',
        int $httpStatus = 200
    ): JsonResponse {
        return response()->json([
            'error' => 0,
            'respuesta' => $respuesta,
            'resultado' => self::normalizeResultado($resultado),
        ], $httpStatus);
    }

    public static function error(
        int $error,
        string $respuesta,
        int $httpStatus = 400,
        array $resultado = []
    ): JsonResponse {
        return response()->json([
            'error' => $error,
            'respuesta' => $respuesta,
            'resultado' => self::normalizeResultado($resultado),
        ], $httpStatus);
    }

    /**
     * @param  array<string, mixed>  $resultado
     * @return array<string, mixed>|\stdClass
     */
    private static function normalizeResultado(array $resultado): array|\stdClass
    {
        if ($resultado === []) {
            return new \stdClass();
        }

        return $resultado;
    }
}
