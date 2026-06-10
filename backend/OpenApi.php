<?php

/**
 * Raíz OpenAPI (L5-Swagger). Escanear junto con `app/` vía `config/l5-swagger.php`.
 *
 * @OA\Info(
 *     title="PedidosWeb API",
 *     version="1.1.0",
 *     description="API REST PedidosWeb (MONO). Todas las respuestas usan envelope JSON: error / respuesta / resultado."
 * )
 *
 * @OA\Server(
 *     url="/",
 *     description="Backend Laravel (paths incluyen /api/v1)"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="Sanctum",
 *     description="Token Bearer obtenido en POST /api/v1/auth/login"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="tenant",
 *     type="apiKey",
 *     in="header",
 *     name="X-Paq-Cliente",
 *     description="Cliente MONO resuelto (ej. desarrollo, demo)"
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelope",
 *     type="object",
 *     required={"error","respuesta","resultado"},
 *     @OA\Property(property="error", type="integer", example=0, description="0 = OK"),
 *     @OA\Property(property="respuesta", type="string", example="ok", description="Clave i18n o mensaje"),
 *     @OA\Property(
 *         property="resultado",
 *         type="object",
 *         description="Payload; nunca null (vacío = {})"
 *     )
 * )
 */
final class OpenApi
{
}
