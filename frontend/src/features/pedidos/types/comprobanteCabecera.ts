export type CatalogoCondicionVenta = {
  codigo: number;
  descripcion: string;
};

export type CatalogoTransporte = {
  codigo: string;
  descripcion: string;
};

export type CatalogoListaPrecios = {
  cod_lista: number;
  descripcion: string;
  moneda: number;
  incluye_iva: boolean;
};

export type CatalogoDireccionEntrega = {
  id_de: number;
  direccion: string;
  localidad: string;
  habitual: boolean;
};

export type CatalogoPerfil = {
  cod_perfil: string;
  descripcion: string;
};

export type CabeceraCatalogos = {
  condicionesVenta: CatalogoCondicionVenta[];
  transportes: CatalogoTransporte[];
  listasPrecios: CatalogoListaPrecios[];
  direccionesEntrega: CatalogoDireccionEntrega[];
  perfiles: CatalogoPerfil[];
};

export type ComprobanteCabecera = {
  codCliente: string;
  razonSocial: string;
  codVended: string | null;
  vendedorNombre: string;
  codCondvta: number | null;
  codTranspor: string | null;
  idDe: number | null;
  direccionEntrega: string;
  expreso: string | null;
  expresoDire: string | null;
  nivel: number;
  listaPrecios: number | null;
  listaPreciosDescripcion: string;
  moneda: number;
  incluyeIva: boolean;
  bonif1: number;
  bonif2: number;
  bonif3: number;
  descuento: number;
  observaciones: string;
  codPerfil: string | null;
  leyenda1: string | null;
  leyenda2: string | null;
  leyenda3: string | null;
  leyenda4: string | null;
  leyenda5: string | null;
  fechaEntrega: string | null;
};

export const emptyComprobanteCabecera = (codCliente: string): ComprobanteCabecera => ({
  codCliente,
  razonSocial: '',
  codVended: null,
  vendedorNombre: '',
  codCondvta: null,
  codTranspor: null,
  idDe: null,
  direccionEntrega: '',
  expreso: null,
  expresoDire: null,
  nivel: 0,
  listaPrecios: null,
  listaPreciosDescripcion: '',
  moneda: 1,
  incluyeIva: false,
  bonif1: 0,
  bonif2: 0,
  bonif3: 0,
  descuento: 0,
  observaciones: '',
  codPerfil: null,
  leyenda1: null,
  leyenda2: null,
  leyenda3: null,
  leyenda4: null,
  leyenda5: null,
  fechaEntrega: null,
});
