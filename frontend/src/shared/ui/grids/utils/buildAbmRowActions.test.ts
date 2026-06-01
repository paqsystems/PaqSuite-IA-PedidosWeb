import { describe, expect, it, vi } from 'vitest';
import { abmTestIds } from '../../abm/abmTestIds';
import { abmFullPermissions } from '../../abm/types/abmTypes';
import { buildAbmRowActions, resolveAbmRowActionTestId } from './buildAbmRowActions';

describe('buildAbmRowActions', () => {
  it('genera acciones edit y delete segun permisos', () => {
    const onEdit = vi.fn();
    const onDelete = vi.fn();

    const actions = buildAbmRowActions({
      enabled: true,
      permissions: abmFullPermissions,
      onCreate: vi.fn(),
      onEdit,
      onDelete,
    });

    expect(actions).toHaveLength(2);
    expect(actions[0]?.actionKey).toBe(abmTestIds.edit);
    expect(actions[1]?.actionKey).toBe(abmTestIds.delete);
  });

  it('omite acciones sin permiso', () => {
    const actions = buildAbmRowActions({
      enabled: true,
      permissions: { alta: true, modi: false, baja: false, repo: true },
      onCreate: vi.fn(),
      onEdit: vi.fn(),
      onDelete: vi.fn(),
    });

    expect(actions).toHaveLength(0);
  });
});

describe('resolveAbmRowActionTestId', () => {
  it('usa testids abm para acciones estandar', () => {
    expect(resolveAbmRowActionTestId(abmTestIds.edit)).toBe('abmEdit');
    expect(resolveAbmRowActionTestId('custom')).toBe('dataGridRowAction-custom');
  });
});
