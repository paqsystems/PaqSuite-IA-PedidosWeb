import { describe, expect, it } from 'vitest';
import { isChangePasswordFormValid, validateChangePasswordForm } from './changePasswordForm';

describe('validateChangePasswordForm', () => {
  it('rechaza campos vacios', () => {
    const errors = validateChangePasswordForm({
      currentPassword: '',
      newPassword: '',
      newPasswordConfirmation: '',
    });

    expect(errors.currentPassword).toBe('auth.passwordRequired');
    expect(errors.newPassword).toBe('auth.passwordRequired');
    expect(errors.newPasswordConfirmation).toBe('auth.passwordRequired');
    expect(isChangePasswordFormValid({
      currentPassword: '',
      newPassword: '',
      newPasswordConfirmation: '',
    })).toBe(false);
  });

  it('rechaza confirmacion distinta', () => {
    const errors = validateChangePasswordForm({
      currentPassword: 'Actual123',
      newPassword: 'Password123!',
      newPasswordConfirmation: 'Password456!',
    });

    expect(errors.newPasswordConfirmation).toBe('auth.passwordConfirmationMismatch');
  });

  it('acepta datos validos', () => {
    const values = {
      currentPassword: 'Actual123',
      newPassword: 'Password123!',
      newPasswordConfirmation: 'Password123!',
    };

    expect(validateChangePasswordForm(values)).toEqual({});
    expect(isChangePasswordFormValid(values)).toBe(true);
  });
});
