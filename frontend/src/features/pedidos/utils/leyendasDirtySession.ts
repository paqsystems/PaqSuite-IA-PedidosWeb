import type { ComprobanteCabecera } from '../types/comprobanteCabecera';

export type LeyendasSnapshot = {
  leyenda1: string | null;
  leyenda2: string | null;
  leyenda3: string | null;
  leyenda4: string | null;
  leyenda5: string | null;
};

export type LeyendasDirtyFlags = {
  leyenda1: boolean;
  leyenda2: boolean;
  leyenda3: boolean;
  leyenda4: boolean;
  leyenda5: boolean;
};

export function createLeyendasSnapshot(cabecera: ComprobanteCabecera): LeyendasSnapshot {
  return {
    leyenda1: cabecera.leyenda1 ?? null,
    leyenda2: cabecera.leyenda2 ?? null,
    leyenda3: cabecera.leyenda3 ?? null,
    leyenda4: cabecera.leyenda4 ?? null,
    leyenda5: cabecera.leyenda5 ?? null,
  };
}

function normalizeLeyenda(value: string | null | undefined): string {
  return (value ?? '').trim();
}

export function computeLeyendasDirtyFlags(
  cabecera: ComprobanteCabecera,
  snapshot: LeyendasSnapshot | null,
): LeyendasDirtyFlags {
  if (!snapshot) {
    return {
      leyenda1: false,
      leyenda2: false,
      leyenda3: false,
      leyenda4: false,
      leyenda5: false,
    };
  }

  return {
    leyenda1: normalizeLeyenda(cabecera.leyenda1) !== normalizeLeyenda(snapshot.leyenda1),
    leyenda2: normalizeLeyenda(cabecera.leyenda2) !== normalizeLeyenda(snapshot.leyenda2),
    leyenda3: normalizeLeyenda(cabecera.leyenda3) !== normalizeLeyenda(snapshot.leyenda3),
    leyenda4: normalizeLeyenda(cabecera.leyenda4) !== normalizeLeyenda(snapshot.leyenda4),
    leyenda5: normalizeLeyenda(cabecera.leyenda5) !== normalizeLeyenda(snapshot.leyenda5),
  };
}

export function mapLeyendasDirtyToApi(flags: LeyendasDirtyFlags) {
  return {
    leyenda_1_dirty: flags.leyenda1,
    leyenda_2_dirty: flags.leyenda2,
    leyenda_3_dirty: flags.leyenda3,
    leyenda_4_dirty: flags.leyenda4,
    leyenda_5_dirty: flags.leyenda5,
  };
}
