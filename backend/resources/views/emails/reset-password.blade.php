<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('mail.passwordReset.subject') }}</title>
</head>
<body>
    <p>{{ __('mail.passwordReset.greeting') }}</p>
    <p>{{ __('mail.passwordReset.instructions') }}</p>
    <p>
        <a href="{{ $resetUrl }}">{{ __('mail.passwordReset.cta') }}</a>
    </p>
    <p>{{ __('mail.passwordReset.expiration', ['minutes' => $expirationMinutes]) }}</p>
    <p>{{ __('mail.passwordReset.ignore') }}</p>
</body>
</html>
