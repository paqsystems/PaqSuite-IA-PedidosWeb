import { describe, expect, it } from 'vitest';
import {
  mapParametroConsultaRow,
  resolveParametroCaption,
} from './resolveParametroConsultaTexts';

describe('resolveParametroConsultaTexts', () => {
  const translate = (key: string) => {
    if (key === 'parametros.pedidosWeb.MinutosWeb.caption') {
      return 'Minutos web';
    }

    if (key === 'pedidos.carga.cabecera.si') {
      return 'Si';
    }

    return key;
  };

  it('traduce caption por clave i18n', () => {
    expect(resolveParametroCaption(translate, 'MinutosWeb', 'Minutos de bloqueo web')).toBe('Minutos web');
  });

  it('localiza booleanos en valorMostrado', () => {
    const row = mapParametroConsultaRow(translate, {
      clave: 'CargaRecurrente',
      caption: 'Carga recurrente',
      tooltip: 'Ayuda',
      tipoValor: 'B',
      valorMostrado: 'true',
    });

    expect(row.valorMostrado).toBe('Si');
  });
});
