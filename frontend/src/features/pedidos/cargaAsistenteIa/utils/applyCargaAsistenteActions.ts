import type { CargaAsistenteAction } from '../model/cargaAsistenteTypes';

export type CargaAsistenteAddRenglonPayload = {
  codArticulo: string;
  cantidad: number;
  precio?: number;
  porcBonif?: number;
  descripcion?: string;
};

export type CargaAsistenteUpdateRenglonPayload = {
  renglon: number;
  cantidad?: number;
  precio?: number;
  porcBonif?: number;
};

export type CargaAsistenteActionHandlers = {
  selectCliente: (codCliente: string) => Promise<void>;
  clearDraft: () => void;
  addRenglon: (payload: CargaAsistenteAddRenglonPayload) => void;
  updateRenglon?: (payload: CargaAsistenteUpdateRenglonPayload) => void;
  removeRenglon?: (renglon: number) => void;
  updateCabeceraField: (field: string, value: unknown) => void;
  patchCabeceraFields?: (fields: Record<string, unknown>) => void;
  grabarPedido: () => void;
  grabarPresupuesto: () => void;
  applyImageExtract?: (payload: Record<string, unknown>) => void;
};

const ignoredActions = new Set([
  'needsChoice',
  'needsRefine',
  'needsConfirm',
  'denied',
  'validationError',
  'showConsulta',
  'noop',
]);

export async function applyCargaAsistenteActions(
  actions: CargaAsistenteAction[],
  handlers: CargaAsistenteActionHandlers,
): Promise<void> {
  for (const item of actions) {
    const actionName = String(item.action ?? '');
    const payload = item.payload ?? {};

    if (ignoredActions.has(actionName)) {
      continue;
    }

    if (actionName === 'clearDraftForClienteChange') {
      handlers.clearDraft();
      continue;
    }

    if (actionName === 'selectCliente') {
      const codCliente = String(payload.codCliente ?? '').trim();
      if (codCliente !== '') {
        await handlers.selectCliente(codCliente);
      }
      continue;
    }

    if (actionName === 'addRenglon') {
      const codArticulo = String(payload.codArticulo ?? '').trim();
      if (codArticulo === '') {
        continue;
      }

      handlers.addRenglon({
        codArticulo,
        cantidad: Number(payload.cantidad) > 0 ? Number(payload.cantidad) : 1,
        precio: payload.precio !== undefined ? Number(payload.precio) : undefined,
        porcBonif: payload.porcBonif !== undefined ? Number(payload.porcBonif) : undefined,
        descripcion:
          payload.descripcion !== undefined ? String(payload.descripcion) : undefined,
      });
      continue;
    }

    if (actionName === 'updateRenglon') {
      const renglon = Number(payload.renglon);
      if (Number.isFinite(renglon) && renglon > 0) {
        handlers.updateRenglon?.({
          renglon,
          cantidad:
            payload.cantidad !== undefined && Number(payload.cantidad) > 0
              ? Number(payload.cantidad)
              : undefined,
          precio: payload.precio !== undefined ? Number(payload.precio) : undefined,
          porcBonif: payload.porcBonif !== undefined ? Number(payload.porcBonif) : undefined,
        });
      }
      continue;
    }

    if (actionName === 'removeRenglon') {
      const renglon = Number(payload.renglon);
      if (Number.isFinite(renglon) && renglon > 0) {
        handlers.removeRenglon?.(renglon);
      }
      continue;
    }

    if (actionName === 'setCampoLibre' || actionName === 'setCabeceraField') {
      const field = String(payload.field ?? '').trim();
      if (field !== '') {
        handlers.updateCabeceraField(field, payload.value);
      }
      continue;
    }

    if (actionName === 'setCabeceraFields') {
      const fields = payload.fields;
      if (fields && typeof fields === 'object' && !Array.isArray(fields)) {
        if (handlers.patchCabeceraFields) {
          handlers.patchCabeceraFields(fields as Record<string, unknown>);
        } else {
          for (const [field, value] of Object.entries(fields as Record<string, unknown>)) {
            if (field.trim() !== '') {
              handlers.updateCabeceraField(field, value);
            }
          }
        }
      }
      continue;
    }

    if (actionName === 'grabarPedido') {
      handlers.grabarPedido();
      continue;
    }

    if (actionName === 'grabarPresupuesto') {
      handlers.grabarPresupuesto();
      continue;
    }

    if (actionName === 'applyImageExtract') {
      handlers.applyImageExtract?.(payload);
    }
  }
}
