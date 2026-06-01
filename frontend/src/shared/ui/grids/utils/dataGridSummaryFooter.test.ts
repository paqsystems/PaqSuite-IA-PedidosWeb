import { describe, expect, it, vi } from 'vitest';
import {
  ensureDataGridSummaryFooter,
  filterRealSummaryItems,
  isSummaryFooterPlaceholder,
  PAQ_SUMMARY_FOOTER_PLACEHOLDER_NAME,
} from './dataGridSummaryFooter';

describe('dataGridSummaryFooter', () => {
  it('identifies placeholder items', () => {
    expect(
      isSummaryFooterPlaceholder({ name: PAQ_SUMMARY_FOOTER_PLACEHOLDER_NAME, column: 'id' }),
    ).toBe(true);
    expect(isSummaryFooterPlaceholder({ column: 'id', summaryType: 'count' })).toBe(false);
  });

  it('filters placeholder items from summary list', () => {
    const items = [
      { name: PAQ_SUMMARY_FOOTER_PLACEHOLDER_NAME, column: 'id', summaryType: 'custom' },
      { column: 'amount', summaryType: 'sum' },
    ];

    expect(filterRealSummaryItems(items)).toEqual([{ column: 'amount', summaryType: 'sum' }]);
  });

  it('adds placeholder when totalItems is empty', () => {
    const grid = {
      getVisibleColumns: () => [{ dataField: 'name', type: 'data' }],
      option: vi.fn(),
    };

    ensureDataGridSummaryFooter(grid as never);

    expect(grid.option).toHaveBeenCalledWith('summary.totalItems', [
      {
        name: PAQ_SUMMARY_FOOTER_PLACEHOLDER_NAME,
        column: 'name',
        summaryType: 'custom',
        displayFormat: '',
      },
    ]);
  });

  it('does not add placeholder when real summaries exist', () => {
    const grid = {
      getVisibleColumns: () => [{ dataField: 'name', type: 'data' }],
      option: vi.fn((key: string) => {
        if (key === 'summary.totalItems') {
          return [{ column: 'name', summaryType: 'count' }];
        }

        return undefined;
      }),
    };

    ensureDataGridSummaryFooter(grid as never);

    expect(grid.option).toHaveBeenCalledTimes(1);
    expect(grid.option).not.toHaveBeenCalledWith('summary.totalItems', expect.anything());
  });
});
