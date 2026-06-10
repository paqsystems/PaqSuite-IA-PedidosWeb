<?php

namespace App\Services\Auth;

use App\Exceptions\AuthFlowException;
use App\Models\PqPedidoswebLogin;
use App\Models\User;
use App\Support\AuthErrorCodes;
use Illuminate\Support\Facades\Hash;

final class ChangePasswordService
{
    public function __construct(
        private readonly SessionContextBuilder $sessionContextBuilder,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function change(User $user, string $currentPassword, string $newPassword): array
    {
        if (! $user->activo || $user->inhabilitado) {
            throw new AuthFlowException(
                AuthErrorCodes::accountDisabled,
                'auth.accountDisabled',
                403
            );
        }

        if (! Hash::check($currentPassword, $user->getAuthPassword())) {
            throw new AuthFlowException(
                AuthErrorCodes::invalidCurrentPassword,
                'auth.invalidCurrentPassword',
                422
            );
        }

        if (Hash::check($newPassword, $user->getAuthPassword())) {
            throw new AuthFlowException(
                AuthErrorCodes::newPasswordSameAsCurrent,
                'auth.newPasswordSameAsCurrent',
                422
            );
        }

        $passwordHash = Hash::make($newPassword);

        $user->password_hash = $passwordHash;
        $user->first_login = false;
        $user->save();

        $this->syncLegacyLogin($user, $passwordHash);

        return $this->sessionContextBuilder->build($user);
    }

    private function syncLegacyLogin(User $user, string $passwordHash): void
    {
        $legacyLogin = PqPedidoswebLogin::query()
            ->where('usuario', $user->codigo)
            ->first();

        if ($legacyLogin === null) {
            return;
        }

        $legacyLogin->password_bcrypt = $passwordHash;
        $legacyLogin->primer_login = false;
        $legacyLogin->save();
    }
}
