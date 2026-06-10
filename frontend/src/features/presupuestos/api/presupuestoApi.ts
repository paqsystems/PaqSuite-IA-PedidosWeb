import { apiRequest } from '../../../shared/http/client';

export type MotivoCierreOption = {
  idMotivo: number;
  descripcion: string;
};

type ApiMotivoCierreRow = {
  id_motivo: number;
  descripcion: string;
};

export async function fetchMotivosCierreNegativos(): Promise<MotivoCierreOption[]> {
  const response = await apiRequest<{ items?: ApiMotivoCierreRow[] }>(
    '/motivos-cierre?tipo_cierre=negativo&activo=1',
  );
  const items = response.resultado.items ?? [];

  return items.map((motivo) => ({
    idMotivo: motivo.id_motivo,
    descripcion: motivo.descripcion,
  }));
}

export async function cerrarPresupuesto(
  codPresupuesto: string,
  idMotivo: number,
  observacion?: string,
): Promise<void> {
  await apiRequest(`/presupuestos/${encodeURIComponent(codPresupuesto)}/cerrar`, {
    method: 'POST',
    body: JSON.stringify({
      id_motivo: idMotivo,
      observacion: observacion ?? null,
    }),
  });
}
