<?php

namespace App\Support;

final class AuthErrorCodes
{
    public const invalidCredentials = 2001;

    public const unauthenticated = 2002;

    public const noPermission = 3002;

    public const noCommercialProfile = 3001;

    public const tenantInvalid = 1001;

    public const validationFailed = 1002;

    public const invalidCurrentPassword = 2003;

    public const newPasswordSameAsCurrent = 2004;

    public const accountDisabled = 2005;

    public const passwordResetTokenInvalidOrExpired = 2006;

    public const notFound = 4000;
}
