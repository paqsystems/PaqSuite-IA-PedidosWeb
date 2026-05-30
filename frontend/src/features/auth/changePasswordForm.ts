export type ChangePasswordFormValues = {
  currentPassword: string;
  newPassword: string;
  newPasswordConfirmation: string;
};

export type ChangePasswordFormErrors = Partial<Record<keyof ChangePasswordFormValues | 'form', string>>;

export function validateChangePasswordForm(
  values: ChangePasswordFormValues,
): ChangePasswordFormErrors {
  const errors: ChangePasswordFormErrors = {};

  if (!values.currentPassword.trim()) {
    errors.currentPassword = 'auth.passwordRequired';
  }

  if (!values.newPassword.trim()) {
    errors.newPassword = 'auth.passwordRequired';
  } else if (values.newPassword.length < 8) {
    errors.newPassword = 'auth.passwordPolicy';
  } else if (!/^(?=.*[A-Za-z])(?=.*\d).+$/.test(values.newPassword)) {
    errors.newPassword = 'auth.passwordPolicy';
  }

  if (!values.newPasswordConfirmation.trim()) {
    errors.newPasswordConfirmation = 'auth.passwordRequired';
  } else if (values.newPassword !== values.newPasswordConfirmation) {
    errors.newPasswordConfirmation = 'auth.passwordConfirmationMismatch';
  }

  return errors;
}

export function isChangePasswordFormValid(values: ChangePasswordFormValues): boolean {
  return Object.keys(validateChangePasswordForm(values)).length === 0;
}
