import type { TFunction } from 'i18next';
import type { ArticuloOption, ClienteOption } from '../api/comprobanteApi';

export function ordenarClientesPorRazonSocial(clientes: ClienteOption[]): ClienteOption[] {
  return [...clientes].sort((clienteA, clienteB) =>
    etiquetaCliente(clienteA).localeCompare(etiquetaCliente(clienteB), 'es', { sensitivity: 'base' }),
  );
}

export function etiquetaCliente(cliente: ClienteOption): string {
  return cliente.razonSocial?.trim() || cliente.nombre;
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
      descripcion: articulo.descripcion,
      disponible,
      disponibleBase: formatCantidadStock(articulo.disponibleNetoBase),
    });
  }

  return t('pedidos.carga.articuloDisplay', {
    descripcion: articulo.descripcion,
    disponible,
  });
}
