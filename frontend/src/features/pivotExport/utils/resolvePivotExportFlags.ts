export type PivotExportFlags = {
  excelBasicoHabilitado: boolean;
  excelFormateadoHabilitado: boolean;
  incluirFiltrosAplicados: boolean;
  incluirMetadatos: boolean;
};

function readBooleanFlag(exportacion: Record<string, unknown>, keys: string[]): boolean {
  for (const key of keys) {
    if (exportacion[key] === true) {
      return true;
    }
  }

  return false;
}

/**
 * Normaliza flags de metadata (especificación técnica + alias legacy del seeder piloto).
 */
export function resolvePivotExportFlags(exportacion: Record<string, unknown> | undefined | null): PivotExportFlags {
  const source = exportacion ?? {};

  return {
    excelBasicoHabilitado: readBooleanFlag(source, [
      'excelBasicoHabilitado',
      'habilitarExcelBasico',
    ]),
    excelFormateadoHabilitado: readBooleanFlag(source, [
      'excelFormateadoHabilitado',
      'habilitarExcelTablaDinamica',
    ]),
    incluirFiltrosAplicados: readBooleanFlag(source, ['incluirFiltrosAplicados']),
    incluirMetadatos: readBooleanFlag(source, ['incluirMetadatos']),
  };
}

export function hasAnyPivotExportMode(flags: PivotExportFlags): boolean {
  return flags.excelBasicoHabilitado || flags.excelFormateadoHabilitado;
}
