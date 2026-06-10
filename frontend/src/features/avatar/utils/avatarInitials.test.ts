import { describe, expect, it } from 'vitest';
import { resolveAvatarInitials } from './avatarInitials';

describe('resolveAvatarInitials', () => {
  it('usa las iniciales de dos palabras', () => {
    expect(resolveAvatarInitials('Cliente MVP')).toBe('CM');
  });

  it('usa las dos primeras letras con una sola palabra', () => {
    expect(resolveAvatarInitials('Supervisor')).toBe('SU');
  });

  it('devuelve signo de interrogacion cuando el nombre esta vacio', () => {
    expect(resolveAvatarInitials('   ')).toBe('?');
  });
});
