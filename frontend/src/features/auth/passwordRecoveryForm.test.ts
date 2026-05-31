import { describe, expect, it } from 'vitest';
import { validateForgotPasswordForm, validateResetPasswordForm } from './passwordRecoveryForm';

describe('validateForgotPasswordForm', () => {
  it('valida email requerido', () => {
    expect(validateForgotPasswordForm({ email: '' })).toEqual({
      email: 'auth.passwordRequired',
    });
  });

  it('valida formato de email', () => {
    expect(validateForgotPasswordForm({ email: 'sin-formato' })).toEqual({
      email: 'validation.failed',
    });
  });
});

describe('validateResetPasswordForm', () => {
  it('valida token requerido', () => {
    expect(
      validateResetPasswordForm({
        token: '',
        newPassword: 'Password123!',
        newPasswordConfirmation: 'Password123!',
      }),
    ).toEqual({
      token: 'auth.passwordResetTokenInvalidOrExpired',
    });
  });

  it('valida politica y confirmacion', () => {
    expect(
      validateResetPasswordForm({
        token: 'abc',
        newPassword: 'corta',
        newPasswordConfirmation: 'otra',
      }),
    ).toEqual({
      newPassword: 'auth.passwordPolicy',
      newPasswordConfirmation: 'auth.passwordConfirmationMismatch',
    });
  });
});
