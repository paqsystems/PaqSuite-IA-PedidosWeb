<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use App\Support\AuthErrorCodes;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'currentPassword',
        'newPassword',
        'newPasswordConfirmation',
        'password',
        'password_confirmation',
        'apiKey',
        'token',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (AuthenticationException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    AuthErrorCodes::unauthenticated,
                    'auth.unauthenticated',
                    401
                );
            }

            return null;
        });

        $this->renderable(function (ValidationException $exception, $request) {
            if ($request->is('api/*')) {
                $respuesta = 'validation.failed';

                if (
                    $exception->validator->errors()->has('newPasswordConfirmation')
                    && filled($request->input('newPassword'))
                    && filled($request->input('newPasswordConfirmation'))
                ) {
                    $respuesta = 'auth.passwordConfirmationMismatch';
                }

                return ApiResponse::error(
                    AuthErrorCodes::validationFailed,
                    $respuesta,
                    422,
                    ['fields' => $exception->errors()]
                );
            }

            return null;
        });

        $this->renderable(function (PedidosWebBusinessException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $exception->errorCode(),
                    $exception->respuestaKey(),
                    $exception->httpStatus()
                );
            }

            return null;
        });

        $this->renderable(function (PedidosWebBusinessValidationException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $exception->errorCode(),
                    'business.validationFailed',
                    $exception->httpStatus(),
                    ['errores' => $exception->respuestaKeys()]
                );
            }

            return null;
        });

        $this->renderable(function (ChatAssistantConfigurationException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $exception->errorCode,
                    $exception->respuestaKey,
                    $exception->httpStatus,
                );
            }

            return null;
        });

        $this->renderable(function (ChatAssistantMessageException $exception, $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    $exception->errorCode,
                    $exception->respuestaKey,
                    $exception->httpStatus,
                );
            }

            return null;
        });

        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
