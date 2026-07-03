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

    if (key === 'parametros.pedidosWeb.ActualizarPrecioCopia.caption') {
      return 'Actualizar precios al copiar';
    }

    if (key === 'pedidos.carga.cabecera.si') {
      return 'Si';
    }

    return key;
  };

  it('traduce caption por clave i18n', () => {
    expect(resolveParametroCaption(translate, 'MinutosWeb', 'Minutos de bloqueo web')).toBe('Minutos web');
  });

  it('traduce caption de ActualizarPrecioCopia', () => {
    expect(
      resolveParametroCaption(translate, 'ActualizarPrecioCopia', 'Actualizar precios al copiar comprobante'),
    ).toBe('Actualizar precios al copiar');
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
