import { describe, expect, it } from 'vitest';
import { resolveThemePalette } from './resolveThemePalette';

describe('resolveThemePalette', () => {
  it('resuelve superficies oscuras con acento azul para material.blue.dark', () => {
    const palette = resolveThemePalette('material.blue.dark');

    expect(palette.accentColor).toBe('#3b82f6');
    expect(palette.panelColor).toBe('#15253d');
    expect(palette.textColor).toBe('#f8fafc');
  });

  it('resuelve superficies oscuras con identidad naranja', () => {
    const palette = resolveThemePalette('material.orange.dark');

    expect(palette.accentColor).toBe('#f97316');
    expect(palette.panelColor).toBe('#2f1d13');
    expect(palette.footerBackgroundColor).toBe('#140d08');
  });

  it('mantiene superficies claras y cambia el acento para temas light', () => {
    const palette = resolveThemePalette('material.teal.light');

    expect(palette.accentColor).toBe('#14b8a6');
    expect(palette.surfaceColor).toBe('#f5f7fb');
    expect(palette.textColor).toBe('#1f2937');
  });
});
