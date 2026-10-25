/*
 * SPEC-101-22 / TR-22b
 * Deploy idempotente de staging y SP de maestros catálogo.
 * No ejecutar contra Ankas_del_sur sin autorización explícita.
 */
SET NOCOUNT ON;
GO

IF OBJECT_ID(N'dbo.pq_pedidosweb_integracion_maestros_stage', N'U') IS NULL
BEGIN
    CREATE TABLE dbo.pq_pedidosweb_integracion_maestros_stage (
        batch_id uniqueidentifier NOT NULL,
        tenant nvarchar(100) NOT NULL,
        recurso nvarchar(60) NOT NULL,
        clave_natural nvarchar(200) NOT NULL,
        fecha_proceso datetime2(3) NOT NULL,
        origen nvarchar(50) NULL,
        estado varchar(20) NOT NULL CONSTRAINT DF_pw_int_mae_stage_estado DEFAULT ('aplicado'),
        creado_en datetime2(3) NOT NULL CONSTRAINT DF_pw_int_mae_stage_creado DEFAULT (SYSUTCDATETIME())
        CONSTRAINT PK_pw_int_mae_stage PRIMARY KEY (batch_id, recurso, clave_natural)
    );
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_stage_insert
    @batch_id uniqueidentifier,
    @tenant nvarchar(100),
    @recurso nvarchar(60),
    @clave_natural nvarchar(200),
    @fecha_proceso datetime2(3),
    @origen nvarchar(50)
AS
BEGIN
    SET NOCOUNT ON;
    MERGE dbo.pq_pedidosweb_integracion_maestros_stage AS dst
    USING (SELECT @batch_id AS batch_id, @recurso AS recurso, @clave_natural AS clave_natural) AS src
      ON dst.batch_id = src.batch_id
     AND dst.recurso = src.recurso
     AND dst.clave_natural = src.clave_natural
    WHEN NOT MATCHED THEN
        INSERT (batch_id, tenant, recurso, clave_natural, fecha_proceso, origen)
        VALUES (@batch_id, @tenant, @recurso, @clave_natural, @fecha_proceso, @origen);
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_provincias_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @clave nvarchar(10) = COALESCE(JSON_VALUE(@payload_json, '$.codProvin'), JSON_VALUE(@payload_json, '$.cod_provin'));
    IF NULLIF(@clave, '') IS NULL THROW 51000, '[ITEM_REJECTED] codProvin requerido', 1;
    MERGE dbo.pq_pedidosweb_provincias AS dst
    USING (SELECT @clave cod_provin, JSON_VALUE(@payload_json, '$.nombrePro') nombre_pro) src
      ON dst.cod_provin = src.cod_provin
    WHEN MATCHED THEN UPDATE SET nombre_pro = COALESCE(src.nombre_pro, dst.nombre_pro)
    WHEN NOT MATCHED THEN INSERT (cod_provin, nombre_pro) VALUES (src.cod_provin, src.nombre_pro);
    EXEC dbo.pq_sp_integracion_maestros_stage_insert @batch_id, @tenant, N'provincias', @clave, @fecha_proceso, @origen;
    SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_condventa_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @clave int = TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.codigo'));
    IF @clave IS NULL THROW 51000, '[ITEM_REJECTED] codigo requerido', 1;
    DECLARE @operation varchar(20) = CASE
        WHEN EXISTS (
            SELECT 1
            FROM dbo.pq_pedidosweb_condventa dst WITH (NOLOCK)
            WHERE dst.codigo = @clave
        ) THEN 'modified'
        ELSE 'added'
    END;
    MERGE dbo.pq_pedidosweb_condventa AS dst
    USING (SELECT @clave codigo, JSON_VALUE(@payload_json, '$.descripcion') descripcion) src
      ON dst.codigo = src.codigo
    WHEN MATCHED THEN UPDATE SET descripcion = COALESCE(src.descripcion, dst.descripcion)
    WHEN NOT MATCHED THEN INSERT (codigo, descripcion) VALUES (src.codigo, src.descripcion);
    DECLARE @stage_key nvarchar(200) = CONVERT(nvarchar(200), @clave);
    EXEC dbo.pq_sp_integracion_maestros_stage_insert @batch_id, @tenant, N'condventa', @stage_key, @fecha_proceso, @origen;
    SELECT 'applied' status, CONVERT(nvarchar(200), @clave) clave,
           CAST(NULL AS varchar(100)) error_code, @operation operation;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_transportes_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @clave nvarchar(20) = JSON_VALUE(@payload_json, '$.codigo');
    IF NULLIF(@clave, '') IS NULL THROW 51000, '[ITEM_REJECTED] codigo requerido', 1;
    MERGE dbo.pq_pedidosweb_transportes AS dst
    USING (SELECT @clave codigo, JSON_VALUE(@payload_json, '$.descripcion') descripcion) src
      ON dst.codigo = src.codigo
    WHEN MATCHED THEN UPDATE SET descripcion = COALESCE(src.descripcion, dst.descripcion)
    WHEN NOT MATCHED THEN INSERT (codigo, descripcion) VALUES (src.codigo, src.descripcion);
    EXEC dbo.pq_sp_integracion_maestros_stage_insert @batch_id, @tenant, N'transportes', @clave, @fecha_proceso, @origen;
    SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_perfiles_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @clave nvarchar(20) = COALESCE(JSON_VALUE(@payload_json, '$.codPerfil'), JSON_VALUE(@payload_json, '$.cod_perfil'));
    IF NULLIF(@clave, '') IS NULL THROW 51000, '[ITEM_REJECTED] codPerfil requerido', 1;
    MERGE dbo.pq_pedidosweb_perfil AS dst
    USING (SELECT @clave cod_perfil, JSON_VALUE(@payload_json, '$.descripcion') descripcion) src
      ON dst.cod_perfil = src.cod_perfil
    WHEN MATCHED THEN UPDATE SET descripcion = COALESCE(src.descripcion, dst.descripcion)
    WHEN NOT MATCHED THEN INSERT (cod_perfil, descripcion) VALUES (src.cod_perfil, src.descripcion);
    EXEC dbo.pq_sp_integracion_maestros_stage_insert @batch_id, @tenant, N'perfiles', @clave, @fecha_proceso, @origen;
    SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_vendedores_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @clave nvarchar(20) = COALESCE(JSON_VALUE(@payload_json, '$.codVended'), JSON_VALUE(@payload_json, '$.cod_vended'));
    IF NULLIF(@clave, '') IS NULL THROW 51000, '[ITEM_REJECTED] codVended requerido', 1;
    MERGE dbo.pq_pedidosweb_vendedores AS dst
    USING (SELECT @clave cod_vended,
                  JSON_VALUE(@payload_json, '$.nombre') nombre,
                  TRY_CONVERT(bit, JSON_VALUE(@payload_json, '$.supervisor')) supervisor,
                  JSON_VALUE(@payload_json, '$.mailSupervisor') mail_supervisor,
                  JSON_VALUE(@payload_json, '$.eMail') e_mail) src
      ON dst.cod_vended = src.cod_vended
    WHEN MATCHED THEN UPDATE SET nombre = COALESCE(src.nombre, dst.nombre), supervisor = COALESCE(src.supervisor, dst.supervisor),
        mail_supervisor = COALESCE(src.mail_supervisor, dst.mail_supervisor), e_mail = COALESCE(src.e_mail, dst.e_mail)
    WHEN NOT MATCHED THEN INSERT (cod_vended, nombre, supervisor, mail_supervisor, e_mail)
        VALUES (src.cod_vended, src.nombre, COALESCE(src.supervisor, 0), src.mail_supervisor, src.e_mail);
    EXEC dbo.pq_sp_integracion_maestros_stage_insert @batch_id, @tenant, N'vendedores', @clave, @fecha_proceso, @origen;
    SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_listaprecios_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @clave int = TRY_CONVERT(int, COALESCE(JSON_VALUE(@payload_json, '$.codLista'), JSON_VALUE(@payload_json, '$.cod_lista')));
    IF @clave IS NULL THROW 51000, '[ITEM_REJECTED] codLista requerido', 1;
    MERGE dbo.pq_pedidosweb_listaprecios AS dst
    USING (SELECT @clave cod_lista, TRY_CONVERT(bit, JSON_VALUE(@payload_json, '$.incluyeIva')) incluye_iva,
        TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.moneda')) moneda, JSON_VALUE(@payload_json, '$.descripcion') descripcion,
        TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.decimales')) decimales) src
      ON dst.cod_lista = src.cod_lista
    WHEN MATCHED THEN UPDATE SET incluye_iva = COALESCE(src.incluye_iva, dst.incluye_iva), moneda = COALESCE(src.moneda, dst.moneda),
        descripcion = COALESCE(src.descripcion, dst.descripcion), decimales = COALESCE(src.decimales, dst.decimales)
    WHEN NOT MATCHED THEN INSERT (cod_lista, incluye_iva, moneda, descripcion, decimales)
        VALUES (src.cod_lista, COALESCE(src.incluye_iva, 0), COALESCE(src.moneda, 1), src.descripcion, COALESCE(src.decimales, 2));
    DECLARE @stage_key nvarchar(200) = CONVERT(nvarchar(200), @clave);
    EXEC dbo.pq_sp_integracion_maestros_stage_insert @batch_id, @tenant, N'listaprecios', @stage_key, @fecha_proceso, @origen;
    SELECT 'applied' status, CONVERT(nvarchar(200), @clave) clave, CAST(NULL AS varchar(100)) error_code;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_escalas_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @clave varchar(2) = JSON_VALUE(@payload_json, '$.codEscala');
    IF NULLIF(@clave, '') IS NULL THROW 51000, '[ITEM_REJECTED] codEscala requerido', 1;
    MERGE dbo.pq_pedidosweb_escalas_cabecera AS dst
    USING (SELECT @clave cod_escala, JSON_VALUE(@payload_json, '$.descripEs') descrip_es,
        TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.nroEscala')) nro_escala) src
      ON dst.cod_escala = src.cod_escala
    WHEN MATCHED THEN UPDATE SET descrip_es = COALESCE(src.descrip_es, dst.descrip_es), nro_escala = COALESCE(src.nro_escala, dst.nro_escala)
    WHEN NOT MATCHED THEN INSERT (cod_escala, descrip_es, nro_escala) VALUES (src.cod_escala, src.descrip_es, src.nro_escala);
    EXEC dbo.pq_sp_integracion_maestros_stage_insert @batch_id, @tenant, N'escalas', @clave, @fecha_proceso, @origen;
    INSERT INTO dbo.pq_pedidosweb_integracion_maestros_stage(batch_id, tenant, recurso, clave_natural, fecha_proceso, origen)
    SELECT @batch_id, @tenant, N'escalas_detalle', @clave + N'|' + CONVERT(nvarchar(100), JSON_VALUE(v.value, '$.codValor')), @fecha_proceso, @origen
    FROM OPENJSON(@payload_json, '$.valores') v
    WHERE NULLIF(JSON_VALUE(v.value, '$.codValor'), '') IS NOT NULL;
    MERGE dbo.pq_pedidosweb_escalas_detalle AS dst
    USING (SELECT @clave cod_escala, JSON_VALUE(v.value, '$.codValor') cod_valor, JSON_VALUE(v.value, '$.descValor') desc_valor
           FROM OPENJSON(@payload_json, '$.valores') v) src
      ON dst.cod_escala = src.cod_escala AND dst.cod_valor = src.cod_valor
    WHEN MATCHED THEN UPDATE SET desc_valor = COALESCE(src.desc_valor, dst.desc_valor)
    WHEN NOT MATCHED THEN INSERT (cod_escala, cod_valor, desc_valor) VALUES (src.cod_escala, src.cod_valor, src.desc_valor);
    SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_articulos_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @clave varchar(15) = JSON_VALUE(@payload_json, '$.codigo');
    IF NULLIF(@clave, '') IS NULL THROW 51000, '[ITEM_REJECTED] codigo requerido', 1;
    DECLARE @eliminar_si_ausente bit = TRY_CONVERT(bit, JSON_VALUE(@payload_json, '$.eliminarSiAusente'));
    DECLARE @operation varchar(20);
    IF TRY_CONVERT(bit, JSON_VALUE(@payload_json, '$.eliminarSiAusente')) = 1
       AND EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_stock WITH (NOLOCK) WHERE cod_articulo = @clave)
    BEGIN
        SELECT 'rejected' status, @clave clave, 'integracion.maestro.deleteReferenciado' error_code, 'deleted' operation;
        RETURN;
    END;
    IF @eliminar_si_ausente = 1
    BEGIN
        DELETE FROM dbo.pq_pedidosweb_articulos WHERE codigo = @clave;
        EXEC dbo.pq_sp_integracion_maestros_stage_insert @batch_id, @tenant, N'articulos', @clave, @fecha_proceso, @origen;
        SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code, 'deleted' operation;
        RETURN;
    END;
    SET @operation = CASE
        WHEN EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_articulos WITH (NOLOCK) WHERE codigo = @clave)
        THEN 'modified'
        ELSE 'added'
    END;
    MERGE dbo.pq_pedidosweb_articulos AS dst
    USING (SELECT @clave codigo, JSON_VALUE(@payload_json, '$.descripcion') descripcion,
        TRY_CONVERT(decimal(6,2), JSON_VALUE(@payload_json, '$.bonificacion')) bonificacion,
        JSON_VALUE(@payload_json, '$.usaEsc') usa_esc, JSON_VALUE(@payload_json, '$.base') base,
        JSON_VALUE(@payload_json, '$.valor1') valor1, JSON_VALUE(@payload_json, '$.valor2') valor2,
        TRY_CONVERT(numeric(6,2), JSON_VALUE(@payload_json, '$.porcIva')) porc_iva,
        TRY_CONVERT(decimal(18,4), JSON_VALUE(@payload_json, '$.equivalenciaVentas')) equivalencia_ventas,
        TRY_CONVERT(bit, JSON_VALUE(@payload_json, '$.stockeable')) stockeable,
        TRY_CONVERT(bit, JSON_VALUE(@payload_json, '$.especial')) especial) src
      ON dst.codigo = src.codigo
    WHEN MATCHED THEN UPDATE SET descripcion = COALESCE(src.descripcion, dst.descripcion), bonificacion = COALESCE(src.bonificacion, dst.bonificacion),
        usa_esc = COALESCE(src.usa_esc, dst.usa_esc), base = COALESCE(src.base, dst.base), valor1 = COALESCE(src.valor1, dst.valor1),
        valor2 = COALESCE(src.valor2, dst.valor2), porc_iva = COALESCE(src.porc_iva, dst.porc_iva),
        equivalencia_ventas = COALESCE(src.equivalencia_ventas, dst.equivalencia_ventas),
        stockeable = COALESCE(src.stockeable, dst.stockeable), especial = COALESCE(src.especial, dst.especial)
    WHEN NOT MATCHED THEN INSERT (codigo, descripcion, bonificacion, usa_esc, base, valor1, valor2, porc_iva, equivalencia_ventas, stockeable, especial)
        VALUES (src.codigo, src.descripcion, src.bonificacion, src.usa_esc, src.base, src.valor1, src.valor2, src.porc_iva,
            COALESCE(src.equivalencia_ventas, 1), COALESCE(src.stockeable, 1), COALESCE(src.especial, 0));
    EXEC dbo.pq_sp_integracion_maestros_stage_insert @batch_id, @tenant, N'articulos', @clave, @fecha_proceso, @origen;
    SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code, @operation operation;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_listaprecios_articulos_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @lista int = TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.codLista'));
    DECLARE @art nvarchar(50) = JSON_VALUE(@payload_json, '$.codArticulo');
    IF @lista IS NULL OR NULLIF(@art, '') IS NULL THROW 51000, '[ITEM_REJECTED] clave requerida', 1;
    IF NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_listaprecios WITH (NOLOCK) WHERE cod_lista = @lista)
        THROW 51000, '[ITEM_REJECTED] lista inexistente', 1;
    IF NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_articulos WITH (NOLOCK) WHERE codigo = @art)
        THROW 51000, '[ITEM_REJECTED] articulo inexistente', 1;
    MERGE dbo.pq_pedidosweb_listaprecios_articulos AS dst
    USING (SELECT @lista cod_lista, @art cod_articulo, TRY_CONVERT(decimal(18,4), JSON_VALUE(@payload_json, '$.precio')) precio) src
      ON dst.cod_lista = src.cod_lista AND dst.cod_articulo = src.cod_articulo
    WHEN MATCHED THEN UPDATE SET precio = COALESCE(src.precio, dst.precio)
    WHEN NOT MATCHED THEN INSERT (cod_lista, cod_articulo, precio) VALUES (src.cod_lista, src.cod_articulo, COALESCE(src.precio, 0));
    DECLARE @stage_key nvarchar(200) = CONVERT(nvarchar(200), @lista) + N'|' + @art;
    EXEC dbo.pq_sp_integracion_maestros_stage_insert @batch_id, @tenant, N'listaprecios-articulos', @stage_key, @fecha_proceso, @origen;
    SELECT 'applied' status, CONVERT(nvarchar(200), @lista) + N'|' + @art clave, CAST(NULL AS varchar(100)) error_code;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_descuentocantidad_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @art nvarchar(50) = COALESCE(JSON_VALUE(@payload_json, '$.codArticu'), JSON_VALUE(@payload_json, '$.cod_articu'));
    DECLARE @cantidad decimal(18,4) = TRY_CONVERT(decimal(18,4), JSON_VALUE(@payload_json, '$.cantidad'));
    IF NULLIF(@art, '') IS NULL OR @cantidad IS NULL THROW 51000, '[ITEM_REJECTED] clave requerida', 1;
    IF NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_articulos WITH (NOLOCK) WHERE codigo = @art)
        THROW 51000, '[ITEM_REJECTED] articulo inexistente', 1;
    MERGE dbo.pq_pedidosweb_descuentocantidad AS dst
    USING (SELECT @art cod_articu, @cantidad cantidad, TRY_CONVERT(decimal(18,4), JSON_VALUE(@payload_json, '$.descuento')) descuento) src
      ON dst.cod_articu = src.cod_articu AND dst.cantidad = src.cantidad
    WHEN MATCHED THEN UPDATE SET descuento = COALESCE(src.descuento, dst.descuento)
    WHEN NOT MATCHED THEN INSERT (cod_articu, cantidad, descuento) VALUES (src.cod_articu, src.cantidad, COALESCE(src.descuento, 0));
    DECLARE @stage_key nvarchar(200) = @art + N'|' + CONVERT(nvarchar(100), @cantidad);
    EXEC dbo.pq_sp_integracion_maestros_stage_insert @batch_id, @tenant, N'descuentocantidad', @stage_key, @fecha_proceso, @origen;
    SELECT 'applied' status, @art + N'|' + CONVERT(nvarchar(100), @cantidad) clave, CAST(NULL AS varchar(100)) error_code;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_provincias_finalize_snapshot
    @batch_id uniqueidentifier, @tenant nvarchar(100), @recurso nvarchar(60), @origen nvarchar(50)
AS
BEGIN
    SET NOCOUNT ON;
    DELETE dst FROM dbo.pq_pedidosweb_provincias dst
    WHERE NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_integracion_maestros_stage st WITH (NOLOCK)
                      WHERE st.batch_id = @batch_id AND st.recurso = N'provincias' AND st.clave_natural = dst.cod_provin)
      AND NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_clientesde ref WITH (NOLOCK) WHERE ref.cod_provin = dst.cod_provin);
    SELECT 'omitted' status, dst.cod_provin clave, 'integracion.maestro.deleteReferenciado' error_code
    FROM dbo.pq_pedidosweb_provincias dst
    WHERE NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_integracion_maestros_stage st WITH (NOLOCK)
                      WHERE st.batch_id = @batch_id AND st.recurso = N'provincias' AND st.clave_natural = dst.cod_provin)
      AND EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_clientesde ref WITH (NOLOCK) WHERE ref.cod_provin = dst.cod_provin);
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_condventa_finalize_snapshot
    @batch_id uniqueidentifier, @tenant nvarchar(100), @recurso nvarchar(60), @origen nvarchar(50)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @deleted TABLE (codigo int PRIMARY KEY);
    INSERT INTO @deleted (codigo)
    SELECT dst.codigo
    FROM dbo.pq_pedidosweb_condventa dst WITH (NOLOCK)
    WHERE NOT EXISTS (
        SELECT 1
        FROM dbo.pq_pedidosweb_integracion_maestros_stage st WITH (NOLOCK)
        WHERE st.batch_id = @batch_id
          AND st.recurso = N'condventa'
          AND st.clave_natural = CONVERT(nvarchar(200), dst.codigo)
    )
      AND NOT EXISTS (
        SELECT 1
        FROM dbo.pq_pedidosweb_clientes ref WITH (NOLOCK)
        WHERE ref.cod_condvta = dst.codigo
    );
    DELETE dst
    FROM dbo.pq_pedidosweb_condventa dst WITH (NOLOCK)
    INNER JOIN @deleted d ON d.codigo = dst.codigo;
    SELECT 'deleted' status, CONVERT(nvarchar(200), d.codigo) clave,
           CAST(NULL AS varchar(100)) error_code, 'deleted' operation
    FROM @deleted d;
    SELECT 'omitted' status, CONVERT(nvarchar(200), dst.codigo) clave,
           'integracion.maestro.deleteReferenciado' error_code, 'deleted' operation
    FROM dbo.pq_pedidosweb_condventa dst WITH (NOLOCK)
    WHERE NOT EXISTS (
        SELECT 1
        FROM dbo.pq_pedidosweb_integracion_maestros_stage st WITH (NOLOCK)
        WHERE st.batch_id = @batch_id
          AND st.recurso = N'condventa'
          AND st.clave_natural = CONVERT(nvarchar(200), dst.codigo)
    )
      AND EXISTS (
        SELECT 1
        FROM dbo.pq_pedidosweb_clientes ref WITH (NOLOCK)
        WHERE ref.cod_condvta = dst.codigo
    );
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_transportes_finalize_snapshot
    @batch_id uniqueidentifier, @tenant nvarchar(100), @recurso nvarchar(60), @origen nvarchar(50)
AS
BEGIN
    SET NOCOUNT ON;
    DELETE dst FROM dbo.pq_pedidosweb_transportes dst
    WHERE NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_integracion_maestros_stage st WITH (NOLOCK)
                      WHERE st.batch_id = @batch_id AND st.recurso = N'transportes' AND st.clave_natural = dst.codigo)
      AND NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_clientes ref WITH (NOLOCK) WHERE ref.cod_transpor = dst.codigo);
    SELECT 'omitted' status, dst.codigo clave, 'integracion.maestro.deleteReferenciado' error_code
    FROM dbo.pq_pedidosweb_transportes dst
    WHERE NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_integracion_maestros_stage st WITH (NOLOCK)
                      WHERE st.batch_id = @batch_id AND st.recurso = N'transportes' AND st.clave_natural = dst.codigo)
      AND EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_clientes ref WITH (NOLOCK) WHERE ref.cod_transpor = dst.codigo);
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_perfiles_finalize_snapshot
    @batch_id uniqueidentifier, @tenant nvarchar(100), @recurso nvarchar(60), @origen nvarchar(50)
AS
BEGIN
    SET NOCOUNT ON;
    DELETE dst FROM dbo.pq_pedidosweb_perfil dst
    WHERE NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_integracion_maestros_stage st WITH (NOLOCK)
                      WHERE st.batch_id = @batch_id AND st.recurso = N'perfiles' AND st.clave_natural = dst.cod_perfil);
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_listaprecios_finalize_snapshot
    @batch_id uniqueidentifier, @tenant nvarchar(100), @recurso nvarchar(60), @origen nvarchar(50)
AS
BEGIN
    SET NOCOUNT ON;
    DELETE dst FROM dbo.pq_pedidosweb_listaprecios dst
    WHERE NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_integracion_maestros_stage st WITH (NOLOCK)
                      WHERE st.batch_id = @batch_id AND st.recurso = N'listaprecios' AND st.clave_natural = CONVERT(nvarchar(200), dst.cod_lista))
      AND NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_clientes ref WITH (NOLOCK) WHERE ref.lista_precios = dst.cod_lista)
      AND NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_listaprecios_articulos ref WITH (NOLOCK) WHERE ref.cod_lista = dst.cod_lista);
    SELECT 'omitted' status, CONVERT(nvarchar(200), dst.cod_lista) clave, 'integracion.maestro.deleteReferenciado' error_code
    FROM dbo.pq_pedidosweb_listaprecios dst
    WHERE NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_integracion_maestros_stage st WITH (NOLOCK)
                      WHERE st.batch_id = @batch_id AND st.recurso = N'listaprecios' AND st.clave_natural = CONVERT(nvarchar(200), dst.cod_lista))
      AND (EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_clientes ref WITH (NOLOCK) WHERE ref.lista_precios = dst.cod_lista)
        OR EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_listaprecios_articulos ref WITH (NOLOCK) WHERE ref.cod_lista = dst.cod_lista));
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_escalas_finalize_snapshot
    @batch_id uniqueidentifier, @tenant nvarchar(100), @recurso nvarchar(60), @origen nvarchar(50)
AS
BEGIN
    SET NOCOUNT ON;
    DELETE det FROM dbo.pq_pedidosweb_escalas_detalle det
    INNER JOIN dbo.pq_pedidosweb_integracion_maestros_stage cab WITH (NOLOCK)
        ON cab.batch_id = @batch_id AND cab.recurso = N'escalas' AND cab.clave_natural = det.cod_escala
    WHERE NOT EXISTS (SELECT 1 FROM dbo.pq_pedidosweb_integracion_maestros_stage val WITH (NOLOCK)
                      WHERE val.batch_id = @batch_id AND val.recurso = N'escalas_detalle'
                        AND val.clave_natural = det.cod_escala + N'|' + det.cod_valor);
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_medidas_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @id_medida int = TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.idMedida'));
    DECLARE @cod_medida_raw nvarchar(4000) = JSON_VALUE(@payload_json, '$.codMedida');
    DECLARE @desc_medida_raw nvarchar(4000) = JSON_VALUE(@payload_json, '$.descMedida');

    IF @id_medida IS NULL
        OR NULLIF(LTRIM(RTRIM(@cod_medida_raw)), '') IS NULL
        OR LEN(@cod_medida_raw) > 10
        OR LEN(@desc_medida_raw) > 40
        THROW 51000, '[ITEM_REJECTED] idMedida, codMedida o descMedida invalidos', 1;

    DECLARE @cod_medida varchar(10) = CONVERT(varchar(10), @cod_medida_raw);
    DECLARE @desc_medida varchar(40) = CONVERT(varchar(40), @desc_medida_raw);

    IF EXISTS (
        SELECT 1
        FROM dbo.pq_pedidosweb_medidas dst WITH (NOLOCK)
        WHERE dst.id_medida = @id_medida
          AND dst.cod_medida <> @cod_medida
    )
    BEGIN
        SELECT 'rejected' status, CONVERT(nvarchar(200), @cod_medida) clave,
               'integracion.maestro.idMedidaDuplicado' error_code,
               'modified' operation;
        RETURN;
    END;

    DECLARE @operation varchar(20) = CASE
        WHEN EXISTS (
            SELECT 1
            FROM dbo.pq_pedidosweb_medidas dst WITH (NOLOCK)
            WHERE dst.cod_medida = @cod_medida
        ) THEN 'modified'
        ELSE 'added'
    END;

    MERGE dbo.pq_pedidosweb_medidas AS dst
    USING (
        SELECT @id_medida AS id_medida,
               @cod_medida AS cod_medida,
               @desc_medida AS desc_medida
    ) AS src
      ON dst.cod_medida = src.cod_medida
    WHEN MATCHED THEN UPDATE SET
        id_medida = src.id_medida,
        desc_medida = COALESCE(src.desc_medida, dst.desc_medida)
    WHEN NOT MATCHED THEN INSERT (id_medida, cod_medida, desc_medida)
        VALUES (src.id_medida, src.cod_medida, src.desc_medida);

    EXEC dbo.pq_sp_integracion_maestros_stage_insert
        @batch_id, @tenant, N'medidas', @cod_medida, @fecha_proceso, @origen;

    SELECT 'applied' status, CONVERT(nvarchar(200), @cod_medida) clave,
           CAST(NULL AS varchar(100)) error_code, @operation operation;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_medidas_finalize_snapshot
    @batch_id uniqueidentifier, @tenant nvarchar(100), @recurso nvarchar(60), @origen nvarchar(50)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @deleted TABLE (cod_medida varchar(10) PRIMARY KEY);

    INSERT INTO @deleted (cod_medida)
    SELECT dst.cod_medida
    FROM dbo.pq_pedidosweb_medidas dst WITH (NOLOCK)
    WHERE NOT EXISTS (
        SELECT 1
        FROM dbo.pq_pedidosweb_integracion_maestros_stage st WITH (NOLOCK)
        WHERE st.batch_id = @batch_id
          AND st.recurso = N'medidas'
          AND st.clave_natural = dst.cod_medida
    );

    DELETE dst
    FROM dbo.pq_pedidosweb_medidas dst
    INNER JOIN @deleted deleted_row
        ON deleted_row.cod_medida = dst.cod_medida;

    SELECT 'deleted' status, CONVERT(nvarchar(200), cod_medida) clave,
           CAST(NULL AS varchar(100)) error_code, 'deleted' operation
    FROM @deleted;
END;
GO

DELETE FROM dbo.pq_pedidosweb_integracion_maestros_stage
WHERE creado_en < DATEADD(day, -7, SYSUTCDATETIME());
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_clientes_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;
    DECLARE @clave nvarchar(20) = JSON_VALUE(@payload_json, '$.codClient');
    IF NULLIF(@clave, '') IS NULL
        THROW 51000, '[ITEM_REJECTED] codClient requerido', 1;

    IF TRY_CONVERT(bit, JSON_VALUE(@payload_json, '$.eliminarSiAusente')) = 1
    BEGIN
        DECLARE @started_transaction bit = 0;
        IF @@TRANCOUNT = 0
        BEGIN
            BEGIN TRANSACTION;
            SET @started_transaction = 1;
        END
        ELSE
            SAVE TRANSACTION cliente_baja;

        IF NOT EXISTS (
            SELECT 1
            FROM dbo.pq_pedidosweb_clientes c WITH (UPDLOCK, HOLDLOCK)
            WHERE c.cod_client = @clave
        )
        BEGIN
            IF @started_transaction = 1
                COMMIT TRANSACTION;
            EXEC dbo.pq_sp_integracion_maestros_stage_insert
                @batch_id, @tenant, N'clientes', @clave, @fecha_proceso, @origen;
            SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code;
            RETURN;
        END;

        IF EXISTS (
            SELECT 1 FROM dbo.pq_pedidosweb_pedidoscabecera p
            WHERE p.cod_cliente = @clave
        )
        OR EXISTS (
            SELECT 1 FROM dbo.pq_pedidosweb_deuda d
            WHERE d.cod_cliente = @clave
        )
        OR EXISTS (
            SELECT 1 FROM dbo.pq_pedidosweb_cheques ch
            WHERE ch.cod_client = @clave
        )
        OR EXISTS (
            SELECT 1 FROM dbo.pq_pedidosweb_ventadetallada v
            WHERE v.cod_cli = @clave
        )
        BEGIN
            IF @started_transaction = 1
                ROLLBACK TRANSACTION;
            ELSE
                ROLLBACK TRANSACTION cliente_baja;
            SELECT 'rejected' status, @clave clave, 'integracion.maestro.deleteReferenciado' error_code;
            RETURN;
        END;

        DELETE FROM dbo.pq_pedidosweb_clientescontactos WHERE cod_client = @clave;
        DELETE FROM dbo.pq_pedidosweb_clientesde WHERE cod_client = @clave;
        DELETE FROM dbo.pq_pedidosweb_clientes WHERE cod_client = @clave;
        IF @started_transaction = 1
            COMMIT TRANSACTION;

        EXEC dbo.pq_sp_integracion_maestros_stage_insert
            @batch_id, @tenant, N'clientes', @clave, @fecha_proceso, @origen;
        SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code;
        RETURN;
    END;

    IF NULLIF(JSON_VALUE(@payload_json, '$.codVended'), '') IS NOT NULL
       AND NOT EXISTS (
           SELECT 1 FROM dbo.pq_pedidosweb_vendedores v WITH (NOLOCK)
           WHERE v.cod_vended = JSON_VALUE(@payload_json, '$.codVended')
       )
        THROW 51000, '[ITEM_REJECTED] codVended inexistente', 1;

    IF JSON_VALUE(@payload_json, '$.listaPrecios') IS NOT NULL
       AND NOT EXISTS (
           SELECT 1 FROM dbo.pq_pedidosweb_listaprecios l WITH (NOLOCK)
           WHERE l.cod_lista = TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.listaPrecios'))
       )
        THROW 51000, '[ITEM_REJECTED] listaPrecios inexistente', 1;

    IF JSON_VALUE(@payload_json, '$.codCondvta') IS NOT NULL
       AND NOT EXISTS (
           SELECT 1 FROM dbo.pq_pedidosweb_condventa c WITH (NOLOCK)
           WHERE c.codigo = TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.codCondvta'))
       )
        THROW 51000, '[ITEM_REJECTED] codCondvta inexistente', 1;

    IF NULLIF(JSON_VALUE(@payload_json, '$.codTranspor'), '') IS NOT NULL
       AND NOT EXISTS (
           SELECT 1 FROM dbo.pq_pedidosweb_transportes t WITH (NOLOCK)
           WHERE t.codigo = JSON_VALUE(@payload_json, '$.codTranspor')
       )
        THROW 51000, '[ITEM_REJECTED] codTranspor inexistente', 1;

    MERGE dbo.pq_pedidosweb_clientes AS dst
    USING (
        SELECT
            @clave cod_client,
            JSON_VALUE(@payload_json, '$.nombre') nombre,
            JSON_VALUE(@payload_json, '$.fantasia') fantasia,
            JSON_VALUE(@payload_json, '$.codVended') cod_vended,
            TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.listaPrecios')) lista_precios,
            TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.codCondvta')) cod_condvta,
            JSON_VALUE(@payload_json, '$.codTranspor') cod_transpor,
            TRY_CONVERT(decimal(18,4), JSON_VALUE(@payload_json, '$.bonificacion')) bonificacion,
            TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.nivel')) nivel,
            JSON_VALUE(@payload_json, '$.expreso') expreso,
            JSON_VALUE(@payload_json, '$.expresoDire') expreso_dire,
            JSON_VALUE(@payload_json, '$.eMail') e_mail,
            JSON_VALUE(@payload_json, '$.razonSoci') razon_soci,
            JSON_VALUE(@payload_json, '$.leyenda1') leyenda_1,
            JSON_VALUE(@payload_json, '$.leyenda2') leyenda_2,
            JSON_VALUE(@payload_json, '$.leyenda3') leyenda_3,
            JSON_VALUE(@payload_json, '$.leyenda4') leyenda_4,
            JSON_VALUE(@payload_json, '$.leyenda5') leyenda_5
    ) src ON dst.cod_client = src.cod_client
    WHEN MATCHED THEN UPDATE SET
        nombre = COALESCE(src.nombre, dst.nombre),
        fantasia = COALESCE(src.fantasia, dst.fantasia),
        cod_vended = COALESCE(src.cod_vended, dst.cod_vended),
        lista_precios = COALESCE(src.lista_precios, dst.lista_precios),
        cod_condvta = COALESCE(src.cod_condvta, dst.cod_condvta),
        cod_transpor = COALESCE(src.cod_transpor, dst.cod_transpor),
        bonificacion = COALESCE(src.bonificacion, dst.bonificacion),
        nivel = COALESCE(src.nivel, dst.nivel),
        expreso = COALESCE(src.expreso, dst.expreso),
        expreso_dire = COALESCE(src.expreso_dire, dst.expreso_dire),
        e_mail = COALESCE(src.e_mail, dst.e_mail),
        razon_soci = COALESCE(src.razon_soci, dst.razon_soci),
        leyenda_1 = COALESCE(src.leyenda_1, dst.leyenda_1),
        leyenda_2 = COALESCE(src.leyenda_2, dst.leyenda_2),
        leyenda_3 = COALESCE(src.leyenda_3, dst.leyenda_3),
        leyenda_4 = COALESCE(src.leyenda_4, dst.leyenda_4),
        leyenda_5 = COALESCE(src.leyenda_5, dst.leyenda_5)
    WHEN NOT MATCHED THEN INSERT (
        cod_client, nombre, fantasia, cod_vended, lista_precios, cod_condvta,
        cod_transpor, bonificacion, nivel, expreso, expreso_dire, e_mail,
        razon_soci, leyenda_1, leyenda_2, leyenda_3, leyenda_4, leyenda_5
    ) VALUES (
        src.cod_client, src.nombre, src.fantasia, src.cod_vended, src.lista_precios,
        src.cod_condvta, src.cod_transpor, src.bonificacion, src.nivel, src.expreso,
        src.expreso_dire, src.e_mail, src.razon_soci, src.leyenda_1, src.leyenda_2,
        src.leyenda_3, src.leyenda_4, src.leyenda_5
    );

    EXEC dbo.pq_sp_integracion_maestros_stage_insert
        @batch_id, @tenant, N'clientes', @clave, @fecha_proceso, @origen;
    SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_clientes_direcciones_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @cliente nvarchar(20) = JSON_VALUE(@payload_json, '$.codClient');
    DECLARE @id_de int = TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.idDe'));
    IF NULLIF(@cliente, '') IS NULL OR @id_de IS NULL
        THROW 51000, '[ITEM_REJECTED] clave de direccion requerida', 1;
    IF NOT EXISTS (
        SELECT 1 FROM dbo.pq_pedidosweb_clientes c WITH (NOLOCK)
        WHERE c.cod_client = @cliente
    )
        THROW 51000, '[ITEM_REJECTED] cliente inexistente', 1;

    MERGE dbo.pq_pedidosweb_clientesde AS dst
    USING (
        SELECT @cliente cod_client, @id_de id_de,
            JSON_VALUE(@payload_json, '$.codDe') cod_de,
            JSON_VALUE(@payload_json, '$.direccion') direccion,
            JSON_VALUE(@payload_json, '$.localidad') localidad,
            JSON_VALUE(@payload_json, '$.cPostal') c_postal,
            JSON_VALUE(@payload_json, '$.codProvin') cod_provin,
            CASE
                WHEN JSON_VALUE(@payload_json, '$.habitual') IS NULL THEN NULL
                WHEN UPPER(JSON_VALUE(@payload_json, '$.habitual')) IN ('1','S','Y','T','TRUE') THEN 'S'
                ELSE 'N'
            END habitual
    ) src ON dst.cod_client = src.cod_client AND dst.id_de = src.id_de
    WHEN MATCHED THEN UPDATE SET
        cod_DE = COALESCE(src.cod_de, dst.cod_DE),
        direccion = COALESCE(src.direccion, dst.direccion),
        localidad = COALESCE(src.localidad, dst.localidad),
        c_postal = COALESCE(src.c_postal, dst.c_postal),
        cod_provin = COALESCE(src.cod_provin, dst.cod_provin),
        habitual = COALESCE(src.habitual, dst.habitual)
    WHEN NOT MATCHED THEN INSERT (
        cod_client, id_de, cod_DE, direccion, localidad, c_postal, cod_provin, habitual
    ) VALUES (
        src.cod_client, src.id_de, src.cod_de, src.direccion, src.localidad,
        src.c_postal, src.cod_provin, src.habitual
    );

    DECLARE @clave nvarchar(200) = @cliente + N'|' + CONVERT(nvarchar(100), @id_de);
    EXEC dbo.pq_sp_integracion_maestros_stage_insert
        @batch_id, @tenant, N'clientes-direcciones', @clave, @fecha_proceso, @origen;
    SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_clientes_contactos_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @cliente nvarchar(20) = JSON_VALUE(@payload_json, '$.codClient');
    DECLARE @contacto nvarchar(50) = JSON_VALUE(@payload_json, '$.codContacto');
    IF NULLIF(@cliente, '') IS NULL OR NULLIF(@contacto, '') IS NULL
        THROW 51000, '[ITEM_REJECTED] clave de contacto requerida', 1;
    IF NOT EXISTS (
        SELECT 1 FROM dbo.pq_pedidosweb_clientes c WITH (NOLOCK)
        WHERE c.cod_client = @cliente
    )
        THROW 51000, '[ITEM_REJECTED] cliente inexistente', 1;

    MERGE dbo.pq_pedidosweb_clientescontactos AS dst
    USING (
        SELECT @cliente cod_client, @contacto cod_contacto,
            JSON_VALUE(@payload_json, '$.nombre') nombre,
            JSON_VALUE(@payload_json, '$.telefono') telefono,
            JSON_VALUE(@payload_json, '$.mail') mail
    ) src ON dst.cod_client = src.cod_client AND dst.cod_contacto = src.cod_contacto
    WHEN MATCHED THEN UPDATE SET
        nombre = COALESCE(src.nombre, dst.nombre),
        telefono = COALESCE(src.telefono, dst.telefono),
        mail = COALESCE(src.mail, dst.mail)
    WHEN NOT MATCHED THEN INSERT (cod_client, cod_contacto, nombre, telefono, mail)
        VALUES (src.cod_client, src.cod_contacto, COALESCE(src.nombre, ''), src.telefono, src.mail);

    DECLARE @clave nvarchar(200) = @cliente + N'|' + @contacto;
    EXEC dbo.pq_sp_integracion_maestros_stage_insert
        @batch_id, @tenant, N'clientes-contactos', @clave, @fecha_proceso, @origen;
    SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code;
END;
GO

CREATE OR ALTER PROCEDURE dbo.pq_sp_integracion_maestros_parametros_apply
    @batch_id uniqueidentifier, @tenant nvarchar(100), @modo varchar(20),
    @fecha_proceso datetime2(3), @origen nvarchar(50), @payload_json nvarchar(max)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @clave nvarchar(100) = JSON_VALUE(@payload_json, '$.clave');
    DECLARE @tipo char(1) = UPPER(JSON_VALUE(@payload_json, '$.tipoValor'));
    IF NULLIF(@clave, '') IS NULL
        THROW 51000, '[ITEM_REJECTED] clave requerida', 1;
    IF @clave NOT IN (
        'ArticulosPrecioCero','ArticulosSinPrecio','ActualizarPrecioCopia',
        'CargaUnidadesVenta','IncluyeArticulosNoStockeables','CargaRecurrente',
        'ClienteLeyenda1','ClienteLeyenda2','ClienteLeyenda3','ClienteLeyenda4',
        'ClienteLeyenda5','ClientesInhabilitados','CodClasifArticulos',
        'CodMotivoCierreExitoso','CodPerfilPedidos','CodTransporte',
        'DetallePorMail','DiasResumenCuenta','DiasVentasDetalladas','FechaControl',
        'ListaPrecios','Mail_DireccionRemitente','MailDestinatariosAdicionales',
        'mailCCO','MinutosAviso','MinutosBloqueo','MinutosWeb','MonedaCodigo',
        'MonedaSimbolo','ModificaBonArtS','ModificaBonArtV','ModificaBonCliS',
        'ModificaBonCliV','ModificaCondVtaC','ModificaCondVtaS','ModificaCondVtaV',
        'ModificaDirEntrC','ModificaDirEntrS','ModificaDirEntrV','ModificaExpresoC',
        'ModificaExpresoS','ModificaExpresoV','ModificaListaPrecS',
        'ModificaListaPrecV','ModificaNivelC','ModificaNivelDesktop','ModificaNivelS',
        'ModificaNivelV','ModificaPrecioS','ModificaPrecioV','ModificaTranspC',
        'ModificaTranspS','ModificaTranspV','NivelExtremo','NOeliminaPedido',
        'NOmodificaPedido','RedistribucionManual','TalonarioFacturaA',
        'TalonarioFacturaB','TalonarioFacturaE'
    )
    BEGIN
        SELECT 'rejected' status, @clave clave, 'integracion.maestro.parametroNoPermitido' error_code;
        RETURN;
    END;

    UPDATE p
    SET Valor_Bool = CASE WHEN @tipo = 'B' THEN TRY_CONVERT(bit, JSON_VALUE(@payload_json, '$.valorBool')) ELSE NULL END,
        Valor_Int = CASE WHEN @tipo = 'I' THEN TRY_CONVERT(int, JSON_VALUE(@payload_json, '$.valorInt')) ELSE NULL END,
        Valor_Decimal = CASE WHEN @tipo = 'N' THEN TRY_CONVERT(numeric(24,6), JSON_VALUE(@payload_json, '$.valorDecimal')) ELSE NULL END,
        Valor_DateTime = CASE WHEN @tipo = 'D' THEN TRY_CONVERT(datetime, JSON_VALUE(@payload_json, '$.valorDateTime')) ELSE NULL END,
        Valor_String = CASE WHEN @tipo = 'S' THEN JSON_VALUE(@payload_json, '$.valorString') ELSE NULL END,
        Valor_Text = CASE WHEN @tipo = 'T' THEN JSON_VALUE(@payload_json, '$.valorText') ELSE NULL END,
        tipo_valor = @tipo
    FROM dbo.PQ_parametros_gral p
    WHERE p.Programa = 'PedidosWeb' AND p.Clave = @clave;

    IF @@ROWCOUNT = 0
        THROW 51000, '[ITEM_REJECTED] parametro inexistente', 1;

    EXEC dbo.pq_sp_integracion_maestros_stage_insert
        @batch_id, @tenant, N'parametros', @clave, @fecha_proceso, @origen;
    SELECT 'applied' status, @clave clave, CAST(NULL AS varchar(100)) error_code;
END;
GO
