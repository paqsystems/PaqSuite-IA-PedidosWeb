<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateLocalePreferenceRequest;
use App\Http\Responses\ApiResponse;
use App\Support\AuthErrorCodes;
use App\Support\LocaleNormalizer;
use App\Support\PreferencesErrorCodes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class UserPreferencesController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/users/me/preferences",
     *     summary="Preferencias del usuario autenticado",
     *     tags={"Preferences"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\Response(response=200, description="Preferencias actuales", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeUserPreferences")),
     *     @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(
                AuthErrorCodes::unauthenticated,
                'auth.unauthenticated',
                401
            );
        }

        return ApiResponse::success([
            'locale' => LocaleNormalizer::normalize($user->locale),
            'theme' => (string) ($user->theme ?? 'generic.light'),
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/users/me/preferences/locale",
     *     summary="Actualizar idioma del usuario",
     *     tags={"Preferences"},
     *     security={{"sanctum":{}},{"tenant":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"locale"},
     *             @OA\Property(property="locale", type="string", example="it")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Locale actualizado", @OA\JsonContent(ref="#/components/schemas/ApiEnvelopeLocaleUpdated")),
     *     @OA\Response(response=401, description="No autenticado"),
     *     @OA\Response(response=422, description="Locale invalido")
     * )
     */
    public function updateLocale(UpdateLocalePreferenceRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error(
                AuthErrorCodes::unauthenticated,
                'auth.unauthenticated',
                401
            );
        }

        $locale = LocaleNormalizer::toCatalogCode((string) $request->validated('locale'));

        if ($locale === null) {
            return ApiResponse::error(
                PreferencesErrorCodes::invalidLocale,
                'preferences.invalidLocale',
                422
            );
        }

        $user->locale = $locale;
        $user->save();

        return ApiResponse::success(
            ['locale' => $locale],
            'preferences.localeUpdated'
        );
    }
}
