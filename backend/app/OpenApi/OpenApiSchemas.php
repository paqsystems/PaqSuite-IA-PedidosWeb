<?php

namespace App\OpenApi;

/**
 * Payloads tipados del campo `resultado` (envelope MONO).
 *
 * @OA\Schema(
 *     schema="HealthResultado",
 *     type="object",
 *     @OA\Property(property="serviceName", type="string", example="PaqSuite-IA-PedidosWeb"),
 *     @OA\Property(property="status", type="string", example="up")
 * )
 *
 * @OA\Schema(
 *     schema="SessionContextUser",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="displayName", type="string", example="Cliente MVP"),
 *     @OA\Property(property="login", type="string", example="cliente.mvp")
 * )
 *
 * @OA\Schema(
 *     schema="SessionContextSecurity",
 *     type="object",
 *     @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"Cliente"}),
 *     @OA\Property(property="accesoTotal", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="SessionContextResultado",
 *     type="object",
 *     @OA\Property(property="user", ref="#/components/schemas/SessionContextUser"),
 *     @OA\Property(property="functionalProfile", type="string", example="cliente"),
 *     @OA\Property(property="codCliente", type="string", nullable=true, example="CLIMVP001"),
 *     @OA\Property(property="codVendedor", type="string", nullable=true, example=null),
 *     @OA\Property(property="locale", type="string", example="es"),
 *     @OA\Property(property="theme", type="string", example="generic.light"),
 *     @OA\Property(property="firstLogin", type="boolean", example=false),
 *     @OA\Property(property="security", ref="#/components/schemas/SessionContextSecurity")
 * )
 *
 * @OA\Schema(
 *     schema="LoginResultado",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/SessionContextResultado"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="token", type="string", example="1|sanctum-token-ejemplo")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="MenuNode",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=10),
 *     @OA\Property(property="menuKey", type="string", example="pedidosIngresados"),
 *     @OA\Property(property="labelKey", type="string", example="menu.pedidosIngresados"),
 *     @OA\Property(property="text", type="string", example="Pedidos Ingresados"),
 *     @OA\Property(property="routePath", type="string", nullable=true, example="/pedidos/ingresados"),
 *     @OA\Property(property="procedimiento", type="string", example="pw_pedidos_ingresados"),
 *     @OA\Property(property="tipoProceso", type="string", nullable=true, example="P"),
 *     @OA\Property(property="order", type="integer", example=2),
 *     @OA\Property(
 *         property="nodeType",
 *         type="string",
 *         enum={"group", "process"},
 *         example="process"
 *     ),
 *     @OA\Property(
 *         property="children",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/MenuNode")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="UserPreferencesResultado",
 *     type="object",
 *     @OA\Property(property="locale", type="string", example="es"),
 *     @OA\Property(property="theme", type="string", example="generic.light", enum={"generic.light", "generic.dark"}),
 *     @OA\Property(property="openInNewTab", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="OpenInNewTabUpdatedResultado",
 *     type="object",
 *     @OA\Property(property="openInNewTab", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="ThemeUpdatedResultado",
 *     type="object",
 *     @OA\Property(property="theme", type="string", example="generic.dark", enum={"generic.light", "generic.dark"})
 * )
 *
 * @OA\Schema(
 *     schema="LocaleUpdatedResultado",
 *     type="object",
 *     @OA\Property(property="locale", type="string", example="it")
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeHealth",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/HealthResultado")
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "ok",
 *         "resultado": {
 *             "serviceName": "PaqSuite-IA-PedidosWeb",
 *             "status": "up"
 *         }
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeLogin",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/LoginResultado")
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "ok",
 *         "resultado": {
 *             "token": "1|sanctum-token-ejemplo",
 *             "user": {"id": 1, "displayName": "Cliente MVP", "login": "cliente.mvp"},
 *             "functionalProfile": "cliente",
 *             "codCliente": "CLIMVP001",
 *             "codVendedor": null,
 *             "locale": "es",
 *             "theme": "generic.light",
 *             "firstLogin": false,
 *             "security": {"roles": {"Cliente"}, "accesoTotal": false}
 *         }
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeSessionContext",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/SessionContextResultado")
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "ok",
 *         "resultado": {
 *             "user": {"id": 1, "displayName": "Cliente MVP", "login": "cliente.mvp"},
 *             "functionalProfile": "cliente",
 *             "codCliente": "CLIMVP001",
 *             "codVendedor": null,
 *             "locale": "es",
 *             "theme": "generic.light",
 *             "firstLogin": false,
 *             "security": {"roles": {"Cliente"}, "accesoTotal": false}
 *         }
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeMenuList",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="resultado",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/MenuNode")
 *             )
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "ok",
 *         "resultado": {
 *             {
 *                 "id": 10,
 *                 "menuKey": "cargaPedidosPresupuestos",
 *                 "labelKey": "menu.cargaPedidosPresupuestos",
 *                 "text": "Carga Pedidos",
 *                 "routePath": null,
 *                 "procedimiento": "pw_carga_pedidos",
 *                 "tipoProceso": null,
 *                 "order": 1,
 *                 "nodeType": "group",
 *                 "children": {
 *                     {
 *                         "id": 11,
 *                         "menuKey": "pedidosIngresados",
 *                         "labelKey": "menu.pedidosIngresados",
 *                         "text": "Pedidos Ingresados",
 *                         "routePath": "/pedidos/ingresados",
 *                         "procedimiento": "pw_pedidos_ingresados",
 *                         "tipoProceso": "P",
 *                         "order": 2,
 *                         "nodeType": "process",
 *                         "children": {}
 *                     }
 *                 }
 *             }
 *         }
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeUserPreferences",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/UserPreferencesResultado")
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "ok",
 *         "resultado": {"locale": "es", "theme": "generic.light", "openInNewTab": false}
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeOpenInNewTabUpdated",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/OpenInNewTabUpdatedResultado")
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "preferences.updated",
 *         "resultado": {"openInNewTab": true}
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeLocaleUpdated",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/LocaleUpdatedResultado")
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "preferences.localeUpdated",
 *         "resultado": {"locale": "it"}
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeThemeUpdated",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/ThemeUpdatedResultado")
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "preferences.themeUpdated",
 *         "resultado": {"theme": "generic.dark"}
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeEmpty",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope")
 *     },
 *     example={"error": 0, "respuesta": "auth.logoutOk", "resultado": {}}
 * )
 */
final class OpenApiSchemas
{
}
