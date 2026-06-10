import { beforeEach, describe, expect, it, vi } from 'vitest';

const loadMessages = vi.fn();
const locale = vi.fn();

vi.mock('devextreme/localization', () => ({
  loadMessages,
  locale,
}));

vi.mock('devextreme/localization/messages/es.json', () => ({
  default: { es: { 'dxDataGrid-sortingAscendingText': 'Orden Ascendente DX' } },
}));

vi.mock('devextreme/localization/messages/en.json', () => ({
  default: { en: { 'dxDataGrid-sortingAscendingText': 'Sort Ascending' } },
}));

vi.mock('devextreme/localization/messages/pt.json', () => ({
  default: { pt: {} },
}));

vi.mock('devextreme/localization/messages/fr.json', () => ({
  default: { fr: {} },
}));

vi.mock('devextreme/localization/messages/it.json', () => ({
  default: { it: {} },
}));

describe('syncDevExtremeLocale', () => {
  beforeEach(() => {
    loadMessages.mockClear();
    locale.mockClear();
    vi.resetModules();
  });

  it('carga mensajes DX sin anidar el locale dos veces', async () => {
    // Import dinámico + mocks DX: puede superar 5s en CI/local lento.
    const { syncDevExtremeLocale } = await import('./syncDevExtremeLocale');

    syncDevExtremeLocale('es');

    expect(loadMessages).toHaveBeenCalledWith(
      expect.objectContaining({
        es: expect.objectContaining({
          'dxDataGrid-sortingAscendingText': 'Orden Ascendente DX',
        }),
      }),
    );
    expect(locale).toHaveBeenCalledWith('es');
  }, 15_000);
});
