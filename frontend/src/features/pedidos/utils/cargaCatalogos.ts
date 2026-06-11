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

function formatCantidadStock(valor: number): string {
  return valor.toLocaleString('es-AR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

export function formatArticuloCargaDisplay(articulo: ArticuloOption, t: TFunction): string {
  const disponible = formatCantidadStock(articulo.disponibleNeto ?? 0);

  if (articulo.disponibleNetoBase !== null && articulo.disponibleNetoBase !== undefined) {
    return t('pedidos.carga.articuloDisplayConBase', {
      codigo: articulo.codArticulo,
      descripcion: articulo.descripcion,
      disponible,
      disponibleBase: formatCantidadStock(articulo.disponibleNetoBase),
    });
  }

  return t('pedidos.carga.articuloDisplay', {
    codigo: articulo.codArticulo,
    descripcion: articulo.descripcion,
    disponible,
  });
}
