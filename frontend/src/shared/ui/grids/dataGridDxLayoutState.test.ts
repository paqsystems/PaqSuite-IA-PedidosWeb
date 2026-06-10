import { describe, expect, it, vi } from 'vitest';
import {
  filterRealSummaryItems,
  PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY,
  type TotalSummaryItem,
} from './utils/dataGridSummaryFooter';

function captureGridLayoutState(
  baseState: Record<string, unknown>,
  totalItems: TotalSummaryItem[],
): Record<string, unknown> {
  const realItems = filterRealSummaryItems(totalItems);

  if (realItems.length === 0) {
    return baseState;
  }

  return {
    ...baseState,
    [PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY]: realItems,
  };
}

function applyGridLayoutState(
  instance: {
    state: (value?: Record<string, unknown> | null) => Record<string, unknown> | null;
    option: (name: string, value?: unknown) => unknown;
  },
  state: Record<string, unknown> | null,
): void {
  if (!state) {
    instance.state(null);
    return;
  }

  const summaryTotalItems = state[PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY];
  const gridState = { ...state };
  delete gridState[PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY];

  instance.state(gridState);

  if (Array.isArray(summaryTotalItems) && summaryTotalItems.length > 0) {
    instance.option('summary.totalItems', summaryTotalItems);
  }
}

describe('dataGridDx layout state', () => {
  it('persiste y restaura totalizadores del footer', () => {
    const totalItems: TotalSummaryItem[] = [
      { column: 'importe', summaryType: 'sum', displayFormat: '{0}' },
    ];
    const captured = captureGridLayoutState({ columns: [] }, totalItems);

    expect(captured[PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY]).toEqual(totalItems);

    const stateMock = vi.fn();
    const optionMock = vi.fn();
    const instance = {
      state: stateMock,
      option: optionMock,
    };

    applyGridLayoutState(instance, captured);

    expect(stateMock).toHaveBeenCalledWith({ columns: [] });
    expect(optionMock).toHaveBeenCalledWith('summary.totalItems', totalItems);
  });

  it('no persiste el placeholder técnico del footer', () => {
    const captured = captureGridLayoutState(
      { columns: [] },
      [{ name: 'paqSummaryFooterPlaceholder', column: 'id', summaryType: 'custom' }],
    );

    expect(captured[PAQ_SUMMARY_TOTAL_ITEMS_STATE_KEY]).toBeUndefined();
  });
});
