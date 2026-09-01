export const leyendaMaxCaracteres = 60;

export function recortarLeyendaCabecera(valor: unknown): string | null {
  if (valor === null || valor === undefined) {
    return null;
  }

  const texto = String(valor).trim();
  if (texto === '') {
    return null;
  }

  const caracteres = [...texto];
  if (caracteres.length <= leyendaMaxCaracteres) {
    return texto;
  }

  return caracteres.slice(0, leyendaMaxCaracteres).join('');
}
