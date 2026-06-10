<?php

namespace App\Services\Seed;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Esquema mínimo PedidosWeb para desarrollo (Ankas sin script ERP completo).
 *
 * @see docs/02-producto/PedidosWeb/PedidosWeb_Modelo_Datos_Final.md
 */
final class PedidosWebDevSchemaBootstrap
{
    /** @var list<string> */
    private const DROP_ORDER = [
        'pq_pedidosweb_pedidosdetalle',
        'pq_pedidosweb_presupuestos_cierres',
        'pq_pedidosweb_tratativas',
        'pq_pedidosweb_pedidoscabecera',
        'pq_pedidosweb_listaprecios_articulos',
        'pq_pedidosweb_descuentocantidad',
        'pq_pedidosweb_escalas_detalle',
        'pq_pedidosweb_escalas_cabecera',
        'pq_pedidosweb_stock',
        'pq_pedidosweb_clientesde',
        'pq_pedidosweb_cheques',
        'pq_pedidosweb_deuda',
        'pq_pedidosweb_ventadetallada',
        'pq_pedidosweb_logs_integracion',
        'pq_pedidosweb_articulos',
        'pq_pedidosweb_login',
        'pq_pedidosweb_clientes',
        'pq_pedidosweb_vendedores',
        'pq_pedidosweb_listaprecios',
        'pq_pedidosweb_condventa',
        'pq_pedidosweb_transportes',
        'pq_pedidosweb_perfil',
        'pq_pedidosweb_provincias',
        'pq_pedidosweb_motivos_cierre',
        'pq_pedidosweb_tratativas_resultados',
    ];

    public function recreatePedidosWebTables(): void
    {
        app(PedidosWebDestructiveBootstrapGuard::class)
            ->assertAllowed('recreatePedidosWebTables');

        $this->dropPedidosWebTables();
        $this->createPedidosWebTables();
    }

    public function dropPedidosWebTables(): void
    {
        app(PedidosWebDestructiveBootstrapGuard::class)
            ->assertAllowed('dropPedidosWebTables');

        foreach (self::DROP_ORDER as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            DB::statement("DROP TABLE [{$table}]");
        }
    }

    public function createPedidosWebTables(): void
    {
        $this->createReferenceTables();
        $this->createMasterTables();
        $this->createTransactionalTables();
        $this->createConsultaTables();
        $this->createSupportTables();
    }

    public function recreateParametrosGralTable(): void
    {
        app(PedidosWebDestructiveBootstrapGuard::class)
            ->assertAllowed('recreateParametrosGralTable');

        if (Schema::hasTable('PQ_parametros_gral')) {
            DB::statement('DROP TABLE [PQ_parametros_gral]');
        }

        DB::statement(<<<'SQL'
CREATE TABLE [PQ_parametros_gral] (
    [Programa] nvarchar(50) NOT NULL,
    [Clave] nvarchar(100) NOT NULL,
    [tipo_valor] char(1) NULL,
    [Valor_String] nvarchar(255) NULL,
    [Valor_Text] nvarchar(max) NULL,
    [Valor_Int] int NULL,
    [Valor_DateTime] datetime NULL,
    [Valor_Bool] bit NULL,
    [Valor_Decimal] numeric(24, 6) NULL,
    [CAPTION] nvarchar(255) NULL,
    [TOOLTIP] nvarchar(500) NULL,
    CONSTRAINT [PK_PQ_parametros_gral] PRIMARY KEY ([Programa], [Clave])
)
SQL);
    }

    private function createReferenceTables(): void
    {
        $this->createIfMissing('pq_pedidosweb_listaprecios', <<<'SQL'
CREATE TABLE pq_pedidosweb_listaprecios (
    cod_lista int NOT NULL PRIMARY KEY,
    incluye_iva bit NOT NULL CONSTRAINT DF_pw_lp_iva DEFAULT (0),
    moneda int NOT NULL CONSTRAINT DF_pw_lp_mon DEFAULT (1),
    descripcion nvarchar(100) NULL,
    decimales int NOT NULL CONSTRAINT DF_pw_lp_dec DEFAULT (2)
)
SQL);

        $this->createIfMissing('pq_pedidosweb_condventa', <<<'SQL'
CREATE TABLE pq_pedidosweb_condventa (
    codigo int NOT NULL PRIMARY KEY,
    descripcion nvarchar(100) NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_transportes', <<<'SQL'
CREATE TABLE pq_pedidosweb_transportes (
    codigo nvarchar(20) NOT NULL PRIMARY KEY,
    descripcion nvarchar(120) NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_perfil', <<<'SQL'
CREATE TABLE pq_pedidosweb_perfil (
    cod_perfil nvarchar(20) NOT NULL PRIMARY KEY,
    descripcion nvarchar(120) NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_provincias', <<<'SQL'
CREATE TABLE pq_pedidosweb_provincias (
    cod_provin nvarchar(10) NOT NULL PRIMARY KEY,
    nombre_pro nvarchar(120) NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_motivos_cierre', <<<'SQL'
CREATE TABLE pq_pedidosweb_motivos_cierre (
    id_motivo int IDENTITY(1,1) NOT NULL PRIMARY KEY,
    tipo_cierre varchar(20) NOT NULL,
    descripcion varchar(100) NOT NULL,
    activo bit NOT NULL CONSTRAINT DF_pw_motivos_activo DEFAULT (1)
)
SQL);

        $this->createIfMissing('pq_pedidosweb_tratativas_resultados', <<<'SQL'
CREATE TABLE pq_pedidosweb_tratativas_resultados (
    id_resultado int IDENTITY(1,1) NOT NULL PRIMARY KEY,
    descripcion varchar(80) NOT NULL,
    activo bit NOT NULL CONSTRAINT DF_pw_trat_res_activo DEFAULT (1)
)
SQL);
    }

    private function createMasterTables(): void
    {
        $this->createIfMissing('pq_pedidosweb_vendedores', <<<'SQL'
CREATE TABLE pq_pedidosweb_vendedores (
    cod_vended nvarchar(20) NOT NULL PRIMARY KEY,
    nombre nvarchar(120) NULL,
    supervisor bit NOT NULL CONSTRAINT DF_pw_vend_sup DEFAULT (0),
    mail_supervisor nvarchar(120) NULL,
    cod_login nvarchar(50) NULL,
    e_mail nvarchar(120) NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_clientes', <<<'SQL'
CREATE TABLE pq_pedidosweb_clientes (
    cod_client nvarchar(20) NOT NULL PRIMARY KEY,
    nombre nvarchar(120) NULL,
    fantasia nvarchar(120) NULL,
    cod_vended nvarchar(20) NULL,
    lista_precios int NULL,
    cod_condvta int NULL,
    cod_transpor nvarchar(20) NULL,
    bonificacion decimal(18,4) NULL,
    nivel int NULL,
    expreso nvarchar(80) NULL,
    expreso_dire nvarchar(200) NULL,
    cod_login nvarchar(50) NULL,
    e_mail nvarchar(120) NULL,
    razon_soci nvarchar(120) NULL,
    leyenda_1 nvarchar(255) NULL,
    leyenda_2 nvarchar(255) NULL,
    leyenda_3 nvarchar(255) NULL,
    leyenda_4 nvarchar(255) NULL,
    leyenda_5 nvarchar(255) NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_clientesde', <<<'SQL'
CREATE TABLE pq_pedidosweb_clientesde (
    cod_client nvarchar(20) NOT NULL,
    id_de int NOT NULL,
    cod_DE nvarchar(20) NULL,
    direccion nvarchar(200) NULL,
    localidad nvarchar(120) NULL,
    c_postal nvarchar(20) NULL,
    cod_provin nvarchar(10) NULL,
    habitual bit NOT NULL CONSTRAINT DF_pw_cde_hab DEFAULT (0),
    CONSTRAINT PK_pw_clientesde PRIMARY KEY (cod_client, id_de)
)
SQL);

        $this->createIfMissing('pq_pedidosweb_login', <<<'SQL'
CREATE TABLE pq_pedidosweb_login (
    cod_usuario_web nvarchar(50) NOT NULL PRIMARY KEY,
    usuario nvarchar(120) NULL,
    password nvarchar(200) NULL,
    e_mail nvarchar(120) NULL,
    primer_login bit NOT NULL CONSTRAINT DF_pw_login_pl DEFAULT (0),
    tipo_cuenta char(1) NULL,
    cod_asociado nvarchar(50) NULL,
    password_bcrypt nvarchar(255) NULL,
    password_sha1 nvarchar(64) NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_articulos', <<<'SQL'
CREATE TABLE pq_pedidosweb_articulos (
    codigo nvarchar(50) NOT NULL PRIMARY KEY,
    descripcion nvarchar(255) NULL,
    bonificacion decimal(18,4) NULL,
    usa_esc bit NOT NULL CONSTRAINT DF_pw_art_esc DEFAULT (0),
    base nvarchar(50) NULL,
    valor1 decimal(18,4) NULL,
    valor2 decimal(18,4) NULL,
    porc_iva decimal(18,4) NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_stock', <<<'SQL'
CREATE TABLE pq_pedidosweb_stock (
    cod_articulo nvarchar(50) NOT NULL PRIMARY KEY,
    stock decimal(18,4) NOT NULL CONSTRAINT DF_pw_stk_stock DEFAULT (0),
    comprometido decimal(18,4) NOT NULL CONSTRAINT DF_pw_stk_comp DEFAULT (0),
    uma_fecha datetime NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_listaprecios_articulos', <<<'SQL'
CREATE TABLE pq_pedidosweb_listaprecios_articulos (
    cod_lista int NOT NULL,
    cod_articulo nvarchar(50) NOT NULL,
    precio decimal(18,4) NOT NULL,
    CONSTRAINT PK_pw_lp_art PRIMARY KEY (cod_lista, cod_articulo)
)
SQL);

        $this->createIfMissing('pq_pedidosweb_descuentocantidad', <<<'SQL'
CREATE TABLE pq_pedidosweb_descuentocantidad (
    cod_articu nvarchar(50) NOT NULL,
    cantidad decimal(18,4) NOT NULL,
    descuento decimal(18,4) NOT NULL,
    CONSTRAINT PK_pw_desc_cant PRIMARY KEY (cod_articu, cantidad)
)
SQL);

        $this->createIfMissing('pq_pedidosweb_escalas_cabecera', <<<'SQL'
CREATE TABLE pq_pedidosweb_escalas_cabecera (
    cod_escala varchar(2) NOT NULL PRIMARY KEY,
    descrip_es varchar(30) NULL,
    nro_escala int NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_escalas_detalle', <<<'SQL'
CREATE TABLE pq_pedidosweb_escalas_detalle (
    cod_escala varchar(2) NOT NULL,
    cod_valor varchar(10) NOT NULL,
    desc_valor varchar(10) NULL,
    CONSTRAINT PK_pw_escalas_det PRIMARY KEY (cod_escala, cod_valor)
)
SQL);
    }

    private function createTransactionalTables(): void
    {
        $this->createIfMissing('pq_pedidosweb_pedidoscabecera', <<<'SQL'
CREATE TABLE pq_pedidosweb_pedidoscabecera (
    cod_pedido nvarchar(50) NOT NULL PRIMARY KEY,
    cod_cliente nvarchar(20) NULL,
    fecha datetime NULL,
    nivel int NULL,
    observaciones nvarchar(max) NULL,
    incluye_iva bit NOT NULL CONSTRAINT DF_pw_cab_iva DEFAULT (0),
    moneda int NULL,
    estado int NOT NULL CONSTRAINT DF_pw_cab_est DEFAULT (0),
    tal_pedido_tango int NULL,
    nro_pedido_tango nvarchar(50) NULL,
    cod_usuario_web nvarchar(50) NULL,
    fecha_modif datetime NULL,
    total decimal(18,2) NULL,
    total_iva decimal(18,2) NULL,
    leyenda_1 nvarchar(255) NULL,
    leyenda_2 nvarchar(255) NULL,
    leyenda_3 nvarchar(255) NULL,
    leyenda_4 nvarchar(255) NULL,
    leyenda_5 nvarchar(255) NULL,
    descuento decimal(18,4) NULL,
    bonif_1 decimal(18,4) NULL,
    bonif_2 decimal(18,4) NULL,
    bonif_3 decimal(18,4) NULL,
    cod_perfil nvarchar(20) NULL,
    cod_vended nvarchar(20) NULL,
    cod_condvta int NULL,
    id_de int NULL,
    cod_transpor nvarchar(20) NULL,
    lista_precios int NULL,
    expreso nvarchar(80) NULL,
    expreso_dire nvarchar(200) NULL,
    fecha_entrega datetime NULL,
    nro_visible bigint NULL,
    usuario_creacion nvarchar(50) NULL,
    fecha_creacion datetime NULL,
    usuario_modificacion nvarchar(50) NULL,
    fechahora_inicio_proceso datetime NULL,
    fechahora_ultima_actividad datetime NULL,
    origen_comprobante nvarchar(50) NULL,
    cod_pedido_origen nvarchar(50) NULL,
    cod_presupuesto_origen nvarchar(50) NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_pedidosdetalle', <<<'SQL'
CREATE TABLE pq_pedidosweb_pedidosdetalle (
    cod_pedido nvarchar(50) NOT NULL,
    renglon int NOT NULL,
    cod_articulo nvarchar(50) NULL,
    cantidad decimal(18,4) NULL,
    porc_bonif decimal(18,4) NULL,
    precio decimal(18,4) NULL,
    precio_neto decimal(18,4) NULL,
    precio_bruto decimal(18,4) NULL,
    porc_iva decimal(18,4) NULL,
    iva decimal(18,2) NULL,
    descripcion_articulo nvarchar(100) NULL,
    importe_lista decimal(18,2) NULL,
    importe_neto decimal(18,2) NULL,
    importe_total decimal(18,2) NULL,
    descuento_origen nvarchar(20) NULL,
    precio_origen nvarchar(20) NULL,
    CONSTRAINT PK_pw_pedidosdetalle PRIMARY KEY (cod_pedido, renglon)
)
SQL);

        $this->createIfMissing('pq_pedidosweb_presupuestos_cierres', <<<'SQL'
CREATE TABLE pq_pedidosweb_presupuestos_cierres (
    id_cierre bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
    cod_presupuesto nvarchar(50) NOT NULL,
    cod_pedido_generado nvarchar(50) NULL,
    tipo_cierre varchar(20) NOT NULL,
    id_motivo int NULL,
    fecha_cierre datetime NOT NULL,
    cod_usuario_web nvarchar(50) NOT NULL,
    observacion nvarchar(max) NULL
)
SQL);

        $this->createIfMissing('pq_pedidosweb_tratativas', <<<'SQL'
CREATE TABLE pq_pedidosweb_tratativas (
    id_tratativa bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
    cod_pedido nvarchar(50) NOT NULL,
    fecha_hora datetime NOT NULL,
    cod_usuario_web nvarchar(50) NOT NULL,
    comentario nvarchar(max) NOT NULL,
    id_resultado int NULL,
    proxima_fecha datetime NULL,
    proxima_accion nvarchar(255) NULL,
    created_at datetime NULL,
    updated_at datetime NULL
)
SQL);
    }

    private function createConsultaTables(): void
    {
        $this->createIfMissing('pq_pedidosweb_deuda', <<<'SQL'
CREATE TABLE pq_pedidosweb_deuda (
    cod_cliente nvarchar(20) NOT NULL,
    t_comp nvarchar(20) NOT NULL,
    n_comp nvarchar(50) NOT NULL,
    fecha_vto datetime NOT NULL,
    fecha_emis datetime NULL,
    fecha_proceso datetime NULL,
    saldo decimal(18,2) NOT NULL,
    CONSTRAINT PK_pw_deuda PRIMARY KEY (cod_cliente, t_comp, n_comp, fecha_vto)
)
SQL);

        $this->createIfMissing('pq_pedidosweb_cheques', <<<'SQL'
CREATE TABLE pq_pedidosweb_cheques (
    interno nvarchar(30) NOT NULL,
    numero nvarchar(30) NOT NULL,
    cod_client nvarchar(20) NULL,
    banco nvarchar(120) NULL,
    importe decimal(18,2) NULL,
    fecha datetime NULL,
    origen nvarchar(50) NULL,
    estado nvarchar(50) NULL,
    fecha_proceso datetime NULL,
    CONSTRAINT PK_pw_cheques PRIMARY KEY (interno, numero)
)
SQL);

        $this->createIfMissing('pq_pedidosweb_ventadetallada', <<<'SQL'
CREATE TABLE pq_pedidosweb_ventadetallada (
    cod_cli varchar(10) NOT NULL,
    razon_soci varchar(60) NULL,
    n_remito varchar(14) NULL,
    t_comp varchar(3) NOT NULL,
    n_comp varchar(14) NOT NULL,
    fecha_emi datetime NULL,
    cond_vta int NULL,
    porc_desc decimal(6, 2) NULL,
    cotiz decimal(15, 2) NULL,
    moneda varchar(3) NULL,
    total_comp decimal(15, 2) NULL,
    cod_transp varchar(10) NULL,
    nom_transp varchar(60) NULL,
    cod_articu varchar(15) NULL,
    descripcio varchar(60) NULL,
    cod_dep varchar(10) NULL,
    um varchar(10) NULL,
    cantidad decimal(15, 2) NULL,
    precio decimal(15, 2) NULL,
    tot_s_imp decimal(15, 2) NULL,
    n_comp_rem varchar(15) NULL,
    cant_rem decimal(15, 2) NULL,
    fecha_rem datetime NULL,
    fecha_proceso datetime NULL,
    id_gva53 int IDENTITY(1,1) NOT NULL,
    CONSTRAINT PK_pw_ventadetallada PRIMARY KEY (id_gva53)
)
SQL);
    }

    private function createSupportTables(): void
    {
        $this->createIfMissing('pq_pedidosweb_logs_integracion', <<<'SQL'
CREATE TABLE pq_pedidosweb_logs_integracion (
    id_log bigint IDENTITY(1,1) NOT NULL PRIMARY KEY,
    fecha datetime NOT NULL,
    tipo varchar(50) NOT NULL,
    severidad varchar(20) NOT NULL,
    origen varchar(50) NOT NULL,
    mensaje nvarchar(max) NOT NULL,
    payload nvarchar(max) NULL,
    procesado bit NOT NULL CONSTRAINT DF_pw_logs_proc DEFAULT (0)
)
SQL);
    }

    private function createIfMissing(string $table, string $ddl): void
    {
        if (Schema::hasTable($table)) {
            return;
        }

        DB::statement($ddl);
    }
}
