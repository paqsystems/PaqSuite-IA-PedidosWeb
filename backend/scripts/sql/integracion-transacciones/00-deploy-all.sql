/*
    SPEC-101-23 — contratos SP de importación de transacciones.
    Script idempotente: no ejecuta DROP/TRUNCATE y no recrea tablas de negocio.
    Ejecutar únicamente en una base dedicada o en un entorno autorizado.
*/

IF OBJECT_ID(N'pq_pedidosweb_integracion_transacciones_stock_stage', N'U') IS NULL
BEGIN
    CREATE TABLE pq_pedidosweb_integracion_transacciones_stock_stage (
        batch_id uniqueidentifier NOT NULL,
        tenant nvarchar(100) NOT NULL,
        cod_articulo nvarchar(50) NOT NULL,
        stock decimal(18,4) NOT NULL,
        comprometido decimal(18,4) NOT NULL,
        fecha_proceso datetime NULL,
        CONSTRAINT PK_pw_int_tx_stock_stage PRIMARY KEY (batch_id, cod_articulo)
    );
END
GO

IF OBJECT_ID(N'pq_pedidosweb_integracion_transacciones_deuda_stage', N'U') IS NULL
BEGIN
    CREATE TABLE pq_pedidosweb_integracion_transacciones_deuda_stage (
        batch_id uniqueidentifier NOT NULL,
        tenant nvarchar(100) NOT NULL,
        cod_cliente nvarchar(20) NOT NULL,
        t_comp nvarchar(20) NOT NULL,
        n_comp nvarchar(50) NOT NULL,
        fecha_emis datetime NULL,
        fecha_vto datetime NOT NULL,
        saldo decimal(18,2) NOT NULL,
        fecha_proceso datetime NULL,
        CONSTRAINT PK_pw_int_tx_deuda_stage PRIMARY KEY (batch_id, cod_cliente, t_comp, n_comp, fecha_vto)
    );
END
GO

IF OBJECT_ID(N'pq_pedidosweb_integracion_transacciones_cheques_stage', N'U') IS NULL
BEGIN
    CREATE TABLE pq_pedidosweb_integracion_transacciones_cheques_stage (
        batch_id uniqueidentifier NOT NULL,
        tenant nvarchar(100) NOT NULL,
        interno nvarchar(30) NOT NULL,
        numero nvarchar(30) NOT NULL,
        cod_cliente nvarchar(20) NULL,
        banco nvarchar(120) NULL,
        fecha datetime NULL,
        importe decimal(18,2) NULL,
        origen nvarchar(50) NULL,
        estado nvarchar(50) NULL,
        fecha_proceso datetime NULL,
        CONSTRAINT PK_pw_int_tx_cheques_stage PRIMARY KEY (batch_id, interno, numero)
    );
END
GO

CREATE OR ALTER PROCEDURE pq_sp_integracion_transacciones_stock_apply
    @batch_id nvarchar(36),
    @tenant nvarchar(100),
    @modo nvarchar(20),
    @fecha_proceso datetime,
    @origen nvarchar(50) = NULL,
    @item nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @batch uniqueidentifier = TRY_CONVERT(uniqueidentifier, @batch_id);
    DECLARE @codigo nvarchar(50) = JSON_VALUE(@item, '$.codArticulo');
    DECLARE @stock decimal(18,4) = TRY_CONVERT(decimal(18,4), JSON_VALUE(@item, '$.stock'));
    DECLARE @comprometido decimal(18,4) = TRY_CONVERT(decimal(18,4), JSON_VALUE(@item, '$.comprometido'));

    IF @batch IS NULL
    BEGIN SELECT 'rejected' AS status, @codigo AS clave, 'integracion.transaccion.itemInvalido' AS error_code; RETURN; END;
    IF @codigo IS NULL OR @stock IS NULL OR @comprometido IS NULL
    BEGIN SELECT 'rejected' AS status, @codigo AS clave, 'integracion.transaccion.itemInvalido' AS error_code; RETURN; END;
    IF NOT EXISTS (SELECT 1 FROM pq_pedidosweb_articulos WITH (NOLOCK) WHERE codigo = @codigo)
    BEGIN SELECT 'rejected' AS status, @codigo AS clave, 'integracion.transaccion.fkFaltante' AS error_code; RETURN; END;

    IF @modo = 'snapshot'
        INSERT INTO pq_pedidosweb_integracion_transacciones_stock_stage
            (batch_id, tenant, cod_articulo, stock, comprometido, fecha_proceso)
        VALUES (@batch, @tenant, @codigo, @stock, @comprometido, @fecha_proceso);
    ELSE
    BEGIN
        UPDATE pq_pedidosweb_stock
           SET stock = @stock, comprometido = @comprometido, uma_fecha = @fecha_proceso
         WHERE cod_articulo = @codigo;
        IF @@ROWCOUNT = 0
            INSERT INTO pq_pedidosweb_stock (cod_articulo, stock, comprometido, uma_fecha)
            VALUES (@codigo, @stock, @comprometido, @fecha_proceso);
    END
    SELECT 'applied' AS status, @codigo AS clave, NULL AS error_code;
END
GO

CREATE OR ALTER PROCEDURE pq_sp_integracion_transacciones_stock_finalize
    @batch_id nvarchar(36),
    @tenant nvarchar(100),
    @recurso nvarchar(100),
    @origen nvarchar(50) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @batch uniqueidentifier = TRY_CONVERT(uniqueidentifier, @batch_id);
    BEGIN TRY
        BEGIN TRANSACTION;
        DELETE FROM pq_pedidosweb_stock;
        INSERT INTO pq_pedidosweb_stock (cod_articulo, stock, comprometido, uma_fecha)
        SELECT cod_articulo, stock, comprometido, fecha_proceso
        FROM pq_pedidosweb_integracion_transacciones_stock_stage WITH (NOLOCK)
        WHERE batch_id = @batch AND tenant = @tenant;
        DELETE FROM pq_pedidosweb_integracion_transacciones_stock_stage WHERE batch_id = @batch AND tenant = @tenant;
        COMMIT;
        SELECT 'omitted' AS status, NULL AS clave, NULL AS error_code;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK;
        SELECT 'rejected' AS status, NULL AS clave, 'integracion.transaccion.snapshotAbortado' AS error_code;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE pq_sp_integracion_transacciones_deuda_apply
    @batch_id nvarchar(36),
    @tenant nvarchar(100),
    @modo nvarchar(20),
    @fecha_proceso datetime,
    @origen nvarchar(50) = NULL,
    @item nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @batch uniqueidentifier = TRY_CONVERT(uniqueidentifier, @batch_id);
    DECLARE @cliente nvarchar(20) = JSON_VALUE(@item, '$.codCliente');
    DECLARE @tipo nvarchar(20) = JSON_VALUE(@item, '$.tComp');
    DECLARE @numero nvarchar(50) = JSON_VALUE(@item, '$.nComp');
    DECLARE @vto datetime = TRY_CONVERT(datetime, JSON_VALUE(@item, '$.fechaVto'));
    DECLARE @emis datetime = TRY_CONVERT(datetime, JSON_VALUE(@item, '$.fechaEmis'));
    DECLARE @saldo decimal(18,2) = TRY_CONVERT(decimal(18,2), JSON_VALUE(@item, '$.saldo'));
    IF @batch IS NULL
    BEGIN SELECT 'rejected' AS status, @cliente AS clave, 'integracion.transaccion.itemInvalido' AS error_code; RETURN; END;
    IF @cliente IS NULL OR @tipo IS NULL OR @numero IS NULL OR @vto IS NULL OR @saldo IS NULL
    BEGIN SELECT 'rejected' AS status, @cliente AS clave, 'integracion.transaccion.itemInvalido' AS error_code; RETURN; END;
    IF NOT EXISTS (SELECT 1 FROM pq_pedidosweb_clientes WITH (NOLOCK) WHERE cod_client = @cliente)
    BEGIN SELECT 'rejected' AS status, @cliente AS clave, 'integracion.transaccion.fkFaltante' AS error_code; RETURN; END;
    IF @modo = 'snapshot'
        INSERT INTO pq_pedidosweb_integracion_transacciones_deuda_stage
            (batch_id, tenant, cod_cliente, t_comp, n_comp, fecha_emis, fecha_vto, saldo, fecha_proceso)
        VALUES (@batch, @tenant, @cliente, @tipo, @numero, @emis, @vto, @saldo, @fecha_proceso);
    ELSE
    BEGIN
        UPDATE pq_pedidosweb_deuda
           SET fecha_emis = @emis, saldo = @saldo, fecha_proceso = @fecha_proceso
         WHERE cod_cliente = @cliente AND t_comp = @tipo AND n_comp = @numero AND fecha_vto = @vto;
        IF @@ROWCOUNT = 0
            INSERT INTO pq_pedidosweb_deuda
                (cod_cliente, t_comp, n_comp, fecha_emis, fecha_vto, saldo, fecha_proceso)
            VALUES (@cliente, @tipo, @numero, @emis, @vto, @saldo, @fecha_proceso);
    END
    SELECT 'applied' AS status, CONCAT(@cliente, '|', @tipo, '|', @numero, '|', CONVERT(nvarchar(30), @vto, 126)) AS clave, NULL AS error_code;
END
GO

CREATE OR ALTER PROCEDURE pq_sp_integracion_transacciones_deuda_finalize
    @batch_id nvarchar(36), @tenant nvarchar(100), @recurso nvarchar(100), @origen nvarchar(50) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @batch uniqueidentifier = TRY_CONVERT(uniqueidentifier, @batch_id);
    BEGIN TRY
        BEGIN TRANSACTION;
        DELETE FROM pq_pedidosweb_deuda;
        INSERT INTO pq_pedidosweb_deuda
            (cod_cliente, t_comp, n_comp, fecha_emis, fecha_vto, saldo, fecha_proceso)
        SELECT cod_cliente, t_comp, n_comp, fecha_emis, fecha_vto, saldo, fecha_proceso
        FROM pq_pedidosweb_integracion_transacciones_deuda_stage WITH (NOLOCK)
        WHERE batch_id = @batch AND tenant = @tenant;
        DELETE FROM pq_pedidosweb_integracion_transacciones_deuda_stage WHERE batch_id = @batch AND tenant = @tenant;
        COMMIT;
        SELECT 'omitted' AS status, NULL AS clave, NULL AS error_code;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK;
        SELECT 'rejected' AS status, NULL AS clave, 'integracion.transaccion.snapshotAbortado' AS error_code;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE pq_sp_integracion_transacciones_cheques_apply
    @batch_id nvarchar(36), @tenant nvarchar(100), @modo nvarchar(20), @fecha_proceso datetime,
    @origen nvarchar(50) = NULL, @item nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @batch uniqueidentifier = TRY_CONVERT(uniqueidentifier, @batch_id);
    DECLARE @interno nvarchar(30) = JSON_VALUE(@item, '$.interno');
    DECLARE @numero nvarchar(30) = JSON_VALUE(@item, '$.numero');
    DECLARE @cliente nvarchar(20) = JSON_VALUE(@item, '$.codCliente');
    DECLARE @banco nvarchar(120) = JSON_VALUE(@item, '$.banco');
    DECLARE @fecha datetime = TRY_CONVERT(datetime, JSON_VALUE(@item, '$.fecha'));
    DECLARE @importe decimal(18,2) = TRY_CONVERT(decimal(18,2), JSON_VALUE(@item, '$.importe'));
    DECLARE @origen_item nvarchar(50) = JSON_VALUE(@item, '$.origen');
    DECLARE @estado nvarchar(50) = JSON_VALUE(@item, '$.estado');
    IF @interno IS NULL OR @numero IS NULL OR @cliente IS NULL OR @banco IS NULL OR @fecha IS NULL OR @importe IS NULL OR @origen_item IS NULL OR @estado IS NULL
    BEGIN SELECT 'rejected' AS status, @interno AS clave, 'integracion.transaccion.itemInvalido' AS error_code; RETURN; END;
    IF NOT EXISTS (SELECT 1 FROM pq_pedidosweb_clientes WITH (NOLOCK) WHERE cod_client = @cliente)
    BEGIN SELECT 'rejected' AS status, @interno AS clave, 'integracion.transaccion.fkFaltante' AS error_code; RETURN; END;
    IF @modo = 'snapshot'
        INSERT INTO pq_pedidosweb_integracion_transacciones_cheques_stage
            (batch_id, tenant, interno, numero, cod_cliente, banco, fecha, importe, origen, estado, fecha_proceso)
        VALUES (@batch, @tenant, @interno, @numero, @cliente, @banco, @fecha, @importe, @origen_item, @estado, @fecha_proceso);
    ELSE
    BEGIN
        UPDATE pq_pedidosweb_cheques
           SET cod_client = @cliente, banco = @banco, fecha = @fecha, importe = @importe,
               origen = @origen_item, estado = @estado, fecha_proceso = @fecha_proceso
         WHERE interno = @interno AND numero = @numero;
        IF @@ROWCOUNT = 0
            INSERT INTO pq_pedidosweb_cheques
                (interno, numero, cod_client, banco, fecha, importe, origen, estado, fecha_proceso)
            VALUES (@interno, @numero, @cliente, @banco, @fecha, @importe, @origen_item, @estado, @fecha_proceso);
    END
    SELECT 'applied' AS status, CONCAT(@interno, '|', @numero) AS clave, NULL AS error_code;
END
GO

CREATE OR ALTER PROCEDURE pq_sp_integracion_transacciones_cheques_finalize
    @batch_id nvarchar(36), @tenant nvarchar(100), @recurso nvarchar(100), @origen nvarchar(50) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @batch uniqueidentifier = TRY_CONVERT(uniqueidentifier, @batch_id);
    BEGIN TRY
        BEGIN TRANSACTION;
        DELETE FROM pq_pedidosweb_cheques;
        INSERT INTO pq_pedidosweb_cheques
            (interno, numero, cod_client, banco, fecha, importe, origen, estado, fecha_proceso)
        SELECT interno, numero, cod_cliente, banco, fecha, importe, origen, estado, fecha_proceso
        FROM pq_pedidosweb_integracion_transacciones_cheques_stage WITH (NOLOCK)
        WHERE batch_id = @batch AND tenant = @tenant;
        DELETE FROM pq_pedidosweb_integracion_transacciones_cheques_stage WHERE batch_id = @batch AND tenant = @tenant;
        COMMIT;
        SELECT 'omitted' AS status, NULL AS clave, NULL AS error_code;
    END TRY
    BEGIN CATCH
        IF @@TRANCOUNT > 0 ROLLBACK;
        SELECT 'rejected' AS status, NULL AS clave, 'integracion.transaccion.snapshotAbortado' AS error_code;
    END CATCH
END
GO

CREATE OR ALTER PROCEDURE pq_sp_integracion_transacciones_historial_ventas_apply
    @batch_id nvarchar(36), @tenant nvarchar(100), @modo nvarchar(20), @fecha_proceso datetime,
    @origen nvarchar(50) = NULL, @item nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @id int = TRY_CONVERT(int, JSON_VALUE(@item, '$.idGva53'));
    DECLARE @cliente varchar(10) = JSON_VALUE(@item, '$.codCliente');
    DECLARE @razon varchar(60) = COALESCE(JSON_VALUE(@item, '$.razonSocial'), JSON_VALUE(@item, '$.razonSoci'));
    DECLARE @remito varchar(14) = JSON_VALUE(@item, '$.nRemito');
    DECLARE @tipo varchar(3) = COALESCE(JSON_VALUE(@item, '$.tipo'), JSON_VALUE(@item, '$.tComp'));
    DECLARE @numero varchar(14) = COALESCE(JSON_VALUE(@item, '$.numero'), JSON_VALUE(@item, '$.nComp'));
    DECLARE @fecha datetime = TRY_CONVERT(datetime, COALESCE(JSON_VALUE(@item, '$.fechaEmision'), JSON_VALUE(@item, '$.fechaEmi')));
    DECLARE @condicion int = TRY_CONVERT(int, JSON_VALUE(@item, '$.condVta'));
    DECLARE @porcentaje decimal(6,2) = TRY_CONVERT(decimal(6,2), JSON_VALUE(@item, '$.porcDesc'));
    DECLARE @cotizacion decimal(15,2) = TRY_CONVERT(decimal(15,2), JSON_VALUE(@item, '$.cotiz'));
    DECLARE @moneda varchar(3) = JSON_VALUE(@item, '$.moneda');
    DECLARE @total decimal(15,2) = TRY_CONVERT(decimal(15,2), JSON_VALUE(@item, '$.totalComp'));
    DECLARE @cod_transp varchar(10) = JSON_VALUE(@item, '$.codTransp');
    DECLARE @nom_transp varchar(60) = JSON_VALUE(@item, '$.nomTransp');
    DECLARE @cod_articu varchar(15) = COALESCE(JSON_VALUE(@item, '$.codArticulo'), JSON_VALUE(@item, '$.codArticu'));
    DECLARE @descripcio varchar(60) = COALESCE(JSON_VALUE(@item, '$.descripcion'), JSON_VALUE(@item, '$.descripcio'));
    DECLARE @cod_dep varchar(10) = JSON_VALUE(@item, '$.codDep');
    DECLARE @um varchar(10) = JSON_VALUE(@item, '$.um');
    DECLARE @cantidad decimal(15,2) = TRY_CONVERT(decimal(15,2), JSON_VALUE(@item, '$.cantidad'));
    DECLARE @precio decimal(15,2) = TRY_CONVERT(decimal(15,2), JSON_VALUE(@item, '$.precio'));
    DECLARE @tot_s_imp decimal(15,2) = TRY_CONVERT(decimal(15,2), COALESCE(JSON_VALUE(@item, '$.totSinImp'), JSON_VALUE(@item, '$.totSImp')));
    DECLARE @n_comp_rem varchar(15) = JSON_VALUE(@item, '$.nCompRem');
    DECLARE @cant_rem decimal(15,2) = TRY_CONVERT(decimal(15,2), JSON_VALUE(@item, '$.cantRem'));
    DECLARE @fecha_rem datetime = TRY_CONVERT(datetime, JSON_VALUE(@item, '$.fechaRem'));
    IF @id IS NULL OR @cliente IS NULL OR @tipo IS NULL OR @numero IS NULL OR @fecha IS NULL
    BEGIN SELECT 'rejected' AS status, CONVERT(nvarchar(50), @id) AS clave, 'integracion.transaccion.itemInvalido' AS error_code; RETURN; END;
    IF NOT EXISTS (SELECT 1 FROM pq_pedidosweb_clientes WITH (NOLOCK) WHERE cod_client = @cliente)
    BEGIN SELECT 'rejected' AS status, CONVERT(nvarchar(50), @id) AS clave, 'integracion.transaccion.fkFaltante' AS error_code; RETURN; END;
    UPDATE pq_pedidosweb_ventadetallada
       SET cod_cli = @cliente, razon_soci = @razon, n_remito = @remito,
           t_comp = @tipo, n_comp = @numero, fecha_emi = @fecha,
           cond_vta = @condicion, porc_desc = @porcentaje, cotiz = @cotizacion,
           moneda = @moneda, total_comp = @total, cod_transp = @cod_transp,
           nom_transp = @nom_transp, cod_articu = @cod_articu,
           descripcio = @descripcio, cod_dep = @cod_dep, um = @um,
           cantidad = @cantidad, precio = @precio, tot_s_imp = @tot_s_imp,
           n_comp_rem = @n_comp_rem, cant_rem = @cant_rem, fecha_rem = @fecha_rem,
           fecha_proceso = @fecha_proceso
     WHERE id_gva53 = @id;
    IF @@ROWCOUNT = 0
    BEGIN
        BEGIN TRY
            SET IDENTITY_INSERT pq_pedidosweb_ventadetallada ON;
            INSERT INTO pq_pedidosweb_ventadetallada (
                cod_cli, razon_soci, n_remito, t_comp, n_comp, fecha_emi, cond_vta,
                porc_desc, cotiz, moneda, total_comp, cod_transp, nom_transp,
                cod_articu, descripcio, cod_dep, um, cantidad, precio, tot_s_imp,
                n_comp_rem, cant_rem, fecha_rem, fecha_proceso, id_gva53
            )
            VALUES (
                @cliente, @razon, @remito, @tipo, @numero, @fecha, @condicion,
                @porcentaje, @cotizacion, @moneda, @total, @cod_transp, @nom_transp,
                @cod_articu, @descripcio, @cod_dep, @um, @cantidad, @precio, @tot_s_imp,
                @n_comp_rem, @cant_rem, @fecha_rem, @fecha_proceso, @id
            );
            SET IDENTITY_INSERT pq_pedidosweb_ventadetallada OFF;
        END TRY
        BEGIN CATCH
            SET IDENTITY_INSERT pq_pedidosweb_ventadetallada OFF;
            THROW;
        END CATCH;
    END;
    SELECT 'applied' AS status, CONVERT(nvarchar(50), @id) AS clave, NULL AS error_code;
END
GO
