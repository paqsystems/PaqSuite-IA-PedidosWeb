import { describe, expect, it } from 'vitest';
import {
  getDataGridDxContainerTestId,
  getDataGridDxToolbarTestId,
  getDataGridRowActionTestId,
} from './dataGridDxTestIds';

describe('dataGridDxTestIds', () => {
  it('genera testids estables por gridId y acción', () => {
    expect(getDataGridDxContainerTestId('main')).toBe('dataGridDx-main');
    expect(getDataGridDxToolbarTestId('main')).toBe('dataGridDxToolbar-main');
    expect(getDataGridRowActionTestId('edit')).toBe('dataGridRowAction-edit');
  });
});
