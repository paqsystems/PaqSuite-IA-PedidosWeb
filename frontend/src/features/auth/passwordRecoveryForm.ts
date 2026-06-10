const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const passwordPattern = /^(?=.*[A-Za-z])(?=.*\d).+$/;

export type ForgotPasswordFormValues = {
  email: string;
};

export type ResetPasswordFormValues = {
  token: string;
  newPassword: string;
  newPasswordConfirmation: string;
};

export function validateForgotPasswordForm(values: ForgotPasswordFormValues): Record<string, string> {
  const errors: Record<string, string> = {};

  if (values.email.trim() === '') {
    errors.email = 'auth.passwordRequired';
  } else if (!emailPattern.test(values.email.trim())) {
    errors.email = 'validation.failed';
  }

  return errors;
}

export function validateResetPasswordForm(values: ResetPasswordFormValues): Record<string, string> {
  const errors: Record<string, string> = {};

  if (values.token.trim() === '') {
    errors.token = 'auth.passwordResetTokenInvalidOrExpired';
  }

  if (values.newPassword.trim() === '') {
    errors.newPassword = 'auth.passwordRequired';
  } else if (values.newPassword.length < 8 || !passwordPattern.test(values.newPassword)) {
    errors.newPassword = 'auth.passwordPolicy';
  }

  if (values.newPasswordConfirmation.trim() === '') {
    errors.newPasswordConfirmation = 'auth.passwordRequired';
  } else if (values.newPasswordConfirmation !== values.newPassword) {
    errors.newPasswordConfirmation = 'auth.passwordConfirmationMismatch';
  }

  return errors;
}
