import { describe, expect, it, vi, afterEach } from 'vitest';
import { buildSuggestedExportFileName } from './buildSuggestedExportFileName';

describe('buildSuggestedExportFileName', () => {
  afterEach(() => {
    vi.useRealTimers();
  });

  it('sanitiza proceso y agrega gridId opcional', () => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date('2026-06-01T14:05:00'));

    expect(buildSuggestedExportFileName('pw/dashboard', 'main')).toBe('pw_dashboard_main_20260601_1405.xlsx');
  });

  it('usa segmento export si proceso vacio', () => {
    vi.useFakeTimers();
    vi.setSystemTime(new Date('2026-01-02T08:09:00'));

    expect(buildSuggestedExportFileName('   ')).toBe('export_20260102_0809.xlsx');
  });
});
