<?php

namespace App\Services\Auth;

use App\Exceptions\AuthFlowException;
use App\Models\User;
use App\Support\AuthErrorCodes;
use Illuminate\Support\Facades\Hash;

final class LoginService
{
    public function __construct(
        private readonly SessionContextBuilder $sessionContextBuilder,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function login(string $codigo, string $password): array
    {
        $user = User::query()->where('codigo', $codigo)->first();

        if ($user === null || ! $this->credentialsAreValid($user, $password)) {
            throw new AuthFlowException(
                AuthErrorCodes::invalidCredentials,
                'auth.invalidCredentials',
                401
            );
        }

        if (! $user->activo || $user->inhabilitado) {
            throw new AuthFlowException(
                AuthErrorCodes::invalidCredentials,
                'auth.invalidCredentials',
                401
            );
        }

        $token = $user->createToken('pedidosweb-api')->plainTextToken;

        return $this->sessionContextBuilder->build($user, $token);
    }

    private function credentialsAreValid(User $user, string $password): bool
    {
        $storedHash = $user->getAuthPassword();

        if ($storedHash === '') {
            return false;
        }

        return Hash::check($password, $storedHash);
    }
}
