<?php

namespace App\Services\Auth;

use App\Exceptions\AuthFlowException;
use App\Mail\ResetPasswordMail;
use App\Models\PqPedidoswebLogin;
use App\Models\User;
use App\Support\AuthErrorCodes;
use App\Support\LocaleNormalizer;
use App\Support\SqlServerIsolation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

final class PasswordRecoveryService
{
    public function requestReset(string $email, string $locale): void
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $user = $this->findUserByEmail($normalizedEmail);

        if ($user === null || blank($user->email)) {
            return;
        }

        $existingToken = $this->passwordResetTokens()
            ->where('email', $normalizedEmail)
            ->first();

        if ($this->isThrottled($existingToken)) {
            return;
        }

        $plainTextToken = Str::random(64);

        $this->passwordResetTokens()->updateOrInsert(
            ['email' => $normalizedEmail],
            [
                'token' => Hash::make($plainTextToken),
                'created_at' => $this->currentTimestampValue(),
            ]
        );

        try {
            Mail::to($normalizedEmail)
                ->locale($locale)
                ->send(new ResetPasswordMail(
                    $this->buildResetUrl($plainTextToken, $locale),
                    $this->expirationMinutes()
                ));
        } catch (\Throwable $throwable) {
            Log::error('auth.password_recovery.mail_failed', [
                'userId' => $user->id,
                'email' => $normalizedEmail,
                'message' => $throwable->getMessage(),
            ]);
        }
    }

    public function resetPassword(string $token, string $newPassword): void
    {
        $tokenRecord = $this->findTokenRecord($token);

        if ($tokenRecord === null) {
            throw new AuthFlowException(
                AuthErrorCodes::passwordResetTokenInvalidOrExpired,
                'auth.passwordResetTokenInvalidOrExpired',
                422
            );
        }

        if ($this->isExpired($tokenRecord->created_at ?? null)) {
            $this->passwordResetTokens()
                ->where('email', $tokenRecord->email)
                ->delete();

            throw new AuthFlowException(
                AuthErrorCodes::passwordResetTokenInvalidOrExpired,
                'auth.passwordResetTokenInvalidOrExpired',
                422
            );
        }

        $user = $this->findUserByEmail((string) $tokenRecord->email);

        if ($user === null) {
            $this->passwordResetTokens()
                ->where('email', $tokenRecord->email)
                ->delete();

            throw new AuthFlowException(
                AuthErrorCodes::passwordResetTokenInvalidOrExpired,
                'auth.passwordResetTokenInvalidOrExpired',
                422
            );
        }

        SqlServerIsolation::transaction(function () use ($tokenRecord, $user, $newPassword): void {
            $passwordHash = Hash::make($newPassword);

            $this->passwordResetTokens()
                ->where('email', $tokenRecord->email)
                ->delete();

            $user->password_hash = $passwordHash;
            $user->first_login = false;
            $user->save();

            $this->syncLegacyLogin($user, $passwordHash);
        });
    }

    private function buildResetUrl(string $token, string $locale): string
    {
        $frontendUrl = rtrim((string) config('app.frontend_url', 'http://localhost:5173'), '/');
        $normalizedLocale = LocaleNormalizer::normalize($locale);

        return $frontendUrl.'/reset-password?'.http_build_query([
            'token' => $token,
            'locale' => $normalizedLocale,
        ]);
    }

    private function expirationMinutes(): int
    {
        return (int) config('auth.passwords.users.expire', 60);
    }

    private function throttleSeconds(): int
    {
        return (int) config('auth.passwords.users.throttle', 60);
    }

    private function isThrottled(object|null $tokenRecord): bool
    {
        if ($tokenRecord === null || ! isset($tokenRecord->created_at)) {
            return false;
        }

        return Carbon::parse($tokenRecord->created_at)
            ->addSeconds($this->throttleSeconds())
            ->isFuture();
    }

    private function isExpired(mixed $createdAt): bool
    {
        if ($createdAt === null) {
            return true;
        }

        return Carbon::parse($createdAt)
            ->addMinutes($this->expirationMinutes())
            ->isPast();
    }

    private function findTokenRecord(string $plainTextToken): object|null
    {
        foreach ($this->passwordResetTokens()->get() as $tokenRecord) {
            if (Hash::check($plainTextToken, (string) $tokenRecord->token)) {
                return $tokenRecord;
            }
        }

        return null;
    }

    private function findUserByEmail(string $email): User|null
    {
        return User::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])
            ->first();
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
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

    private function passwordResetTokens()
    {
        return DB::table('password_reset_tokens');
    }

    private function currentTimestampValue(): Carbon|string
    {
        $currentTimestamp = now();

        if ($this->passwordResetTokens()->getConnection()->getDriverName() === 'sqlsrv') {
            return $currentTimestamp->format('Ymd H:i:s');
        }

        return $currentTimestamp;
    }
}
