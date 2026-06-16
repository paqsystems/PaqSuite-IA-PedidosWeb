import type { TFunction } from 'i18next';
import type { ArticuloOption, ClienteOption } from '../api/comprobanteApi';

export type ClienteSortField = 'codCliente' | 'razonSocial' | 'nombreFantasia';

export function etiquetaCliente(cliente: ClienteOption): string {
  const razonSocial = cliente.razonSocial?.trim() || cliente.nombre;
  const nombreFantasia = cliente.nombreFantasia?.trim() || cliente.nombre;

  return `(${cliente.codCliente}) ${razonSocial} - ${nombreFantasia}`;
}

function sortValueCliente(cliente: ClienteOption, sortField: ClienteSortField): string {
  if (sortField === 'codCliente') {
    return cliente.codCliente;
  }

  if (sortField === 'nombreFantasia') {
    return cliente.nombreFantasia?.trim() || cliente.nombre;
  }

  return cliente.razonSocial?.trim() || cliente.nombre;
}

export function ordenarClientes(
  clientes: ClienteOption[],
  sortField: ClienteSortField = 'razonSocial',
): ClienteOption[] {
  return [...clientes].sort((clienteA, clienteB) =>
    sortValueCliente(clienteA, sortField).localeCompare(sortValueCliente(clienteB, sortField), 'es', {
      sensitivity: 'base',
    }),
  );
}

/** @deprecated Usar ordenarClientes(clientes, 'razonSocial') */
export function ordenarClientesPorRazonSocial(clientes: ClienteOption[]): ClienteOption[] {
  return ordenarClientes(clientes, 'razonSocial');
}

export function ordenarArticulosPorDescripcion(articulos: ArticuloOption[]): ArticuloOption[] {
  return [...articulos].sort((articuloA, articuloB) =>
    articuloA.descripcion.localeCompare(articuloB.descripcion, 'es', { sensitivity: 'base' }),
  );
}

export function etiquetaArticulo(articulo: ArticuloOption): string {
  return `${articulo.codArticulo} - ${articulo.descripcion}`;
}

/** @deprecated Provisional: solo código y descripción; usar etiquetaArticulo. */
export function formatArticuloCargaDisplay(articulo: ArticuloOption, _t: TFunction): string {
  return etiquetaArticulo(articulo);
}
