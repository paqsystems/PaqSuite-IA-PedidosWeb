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
 *     @OA\Property(property="inactivityTimeoutMinutes", type="integer", example=10),
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
 *     @OA\Property(property="theme", type="string", example="material.blue.light"),
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
 *     @OA\Property(property="theme", type="string", example="material.blue.dark")
 * )
 *
 * @OA\Schema(
 *     schema="LocaleUpdatedResultado",
 *     type="object",
 *     @OA\Property(property="locale", type="string", example="it")
 * )
 *
 * @OA\Schema(
 *     schema="VisibleClientContactItem",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="codContacto", type="string", example="C01"),
 *     @OA\Property(property="nombre", type="string", example="Ana Perez"),
 *     @OA\Property(property="telefono", type="string", nullable=true, example="1140000000"),
 *     @OA\Property(property="mail", type="string", nullable=true, example="ana@paqsuite.local")
 * )
 *
 * @OA\Schema(
 *     schema="VisibleClientItem",
 *     type="object",
 *     @OA\Property(property="codCliente", type="string", example="CLIMVP001"),
 *     @OA\Property(property="nombre", type="string", example="Cliente MVP"),
 *     @OA\Property(property="fantasia", type="string", nullable=true, example="Cliente MVP"),
 *     @OA\Property(property="codVendedor", type="string", nullable=true, example="VENACOT01"),
 *     @OA\Property(property="email", type="string", nullable=true, example="cliente.mvp@paqsuite.local"),
 *     @OA\Property(
 *         property="contactos",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/VisibleClientContactItem")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="VisibleComprobanteResultado",
 *     type="object",
 *     @OA\Property(property="id", type="string", example="PED-001"),
 *     @OA\Property(property="codCliente", type="string", example="CLIMVP001"),
 *     @OA\Property(property="codVendedor", type="string", nullable=true, example="VENACOT01"),
 *     @OA\Property(property="estado", type="integer", example=0),
 *     @OA\Property(property="fecha", type="string", format="date-time", nullable=true, example="2026-05-31T01:00:00Z"),
 *     @OA\Property(property="total", type="number", format="float", example=1500.25),
 *     @OA\Property(property="totalIva", type="number", format="float", example=315.05),
 *     @OA\Property(property="observaciones", type="string", nullable=true, example="Observacion MVP")
 * )
 *
 * @OA\Schema(
 *     schema="DashboardResumenResultado",
 *     type="object",
 *     @OA\Property(property="visibleClientsCount", type="integer", example=3),
 *     @OA\Property(property="activeQuotesCount", type="integer", example=1),
 *     @OA\Property(property="enteredOrdersCount", type="integer", example=2),
 *     @OA\Property(property="pendingOrdersCount", type="integer", example=1),
 *     @OA\Property(property="activeQuotesTotal", type="number", format="float", example=1000),
 *     @OA\Property(property="enteredOrdersTotal", type="number", format="float", example=2400),
 *     @OA\Property(property="pendingOrdersTotal", type="number", format="float", example=650)
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
 *             "inactivityTimeoutMinutes": 10,
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
 *             "inactivityTimeoutMinutes": 10,
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
 *     schema="ApiEnvelopeVisibleClients",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(
 *                 property="resultado",
 *                 type="array",
 *                 @OA\Items(ref="#/components/schemas/VisibleClientItem")
 *             )
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "ok",
 *         "resultado": {
 *             {
 *                 "codCliente": "CLIMVP001",
 *                 "nombre": "Cliente MVP",
 *                 "fantasia": "Cliente MVP",
 *                 "codVendedor": "VENACOT01",
 *                 "email": "cliente.mvp@paqsuite.local",
 *                 "contactos": {
 *                     {
 *                         "id": 1,
 *                         "codContacto": "C01",
 *                         "nombre": "Ana Perez",
 *                         "telefono": "1140000000",
 *                         "mail": "ana@paqsuite.local"
 *                     }
 *                 }
 *             }
 *         }
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeVisibleClient",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/VisibleClientItem")
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "ok",
 *         "resultado": {
 *             "codCliente": "CLIMVP001",
 *             "nombre": "Cliente MVP",
 *             "fantasia": "Cliente MVP",
 *             "codVendedor": "VENACOT01",
 *             "email": "cliente.mvp@paqsuite.local",
 *             "contactos": {
 *                 {
 *                     "id": 1,
 *                     "codContacto": "C01",
 *                     "nombre": "Ana Perez",
 *                     "telefono": "1140000000",
 *                     "mail": "ana@paqsuite.local"
 *                 }
 *             }
 *         }
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeVisibleComprobante",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/VisibleComprobanteResultado")
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "ok",
 *         "resultado": {
 *             "id": "PED-001",
 *             "codCliente": "CLIMVP001",
 *             "codVendedor": "VENACOT01",
 *             "estado": 0,
 *             "fecha": "2026-05-31T01:00:00Z",
 *             "total": 1500.25,
 *             "totalIva": 315.05,
 *             "observaciones": "Observacion MVP"
 *         }
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeDashboardResumen",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/DashboardResumenResultado")
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "ok",
 *         "resultado": {
 *             "visibleClientsCount": 3,
 *             "activeQuotesCount": 1,
 *             "enteredOrdersCount": 2,
 *             "pendingOrdersCount": 1,
 *             "activeQuotesTotal": 1000,
 *             "enteredOrdersTotal": 2400,
 *             "pendingOrdersTotal": 650
 *         }
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
 *
 * @OA\Schema(
 *     schema="ChatAssistantProviderCatalogItem",
 *     type="object",
 *     @OA\Property(property="providerId", type="string", example="ollama"),
 *     @OA\Property(property="displayName", type="string", example="Ollama"),
 *     @OA\Property(property="supportsVision", type="boolean", example=true),
 *     @OA\Property(property="requiresBaseUrl", type="boolean", example=true),
 *     @OA\Property(property="supportUrl", type="string", example="https://ollama.com/download"),
 *     @OA\Property(property="suggestedModels", type="array", @OA\Items(type="string"), example={"llama3.1","llava"})
 * )
 *
 * @OA\Schema(
 *     schema="ChatAssistantProviderCatalogResultado",
 *     type="object",
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ChatAssistantProviderCatalogItem")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeChatAssistantProviderCatalog",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/ChatAssistantProviderCatalogResultado")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ChatAssistantConfigurationResultado",
 *     type="object",
 *     @OA\Property(property="credentialId", type="integer", nullable=true, example=1),
 *     @OA\Property(property="displayName", type="string", nullable=true, example="Mi Ollama"),
 *     @OA\Property(property="hasConfiguration", type="boolean", example=true),
 *     @OA\Property(property="hasApiKey", type="boolean", example=true),
 *     @OA\Property(property="apiKeyHint", type="string", example="••••••••"),
 *     @OA\Property(property="providerId", type="string", example="ollama"),
 *     @OA\Property(property="modelId", type="string", example="llama3.1"),
 *     @OA\Property(property="baseUrl", type="string", example="http://localhost:11434"),
 *     @OA\Property(property="supportsVision", type="boolean", example=true),
 *     @OA\Property(property="isEnabled", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="UpsertChatAssistantConfigurationRequest",
 *     type="object",
 *     required={"providerId", "modelId"},
 *     @OA\Property(property="providerId", type="string", example="ollama"),
 *     @OA\Property(property="apiKey", type="string", example="secret-value"),
 *     @OA\Property(property="modelId", type="string", example="llama3.1"),
 *     @OA\Property(property="baseUrl", type="string", example="http://localhost:11434")
 * )
 *
 * @OA\Schema(
 *     schema="UpdateChatAssistantConfigurationStatusRequest",
 *     type="object",
 *     required={"isEnabled"},
 *     @OA\Property(property="isEnabled", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeChatAssistantConfiguration",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/ChatAssistantConfigurationResultado")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeChatAssistantConfigurationSaved",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="respuesta", type="string", example="chatAssistant.configurationSaved"),
 *             @OA\Property(property="resultado", ref="#/components/schemas/ChatAssistantConfigurationResultado")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="SendChatAssistantMessageRequest",
 *     type="object",
 *     @OA\Property(property="message", type="string", example="Necesito ayuda con esta pantalla"),
 *     @OA\Property(
 *         property="images",
 *         type="array",
 *         maxItems=4,
 *         @OA\Items(ref="#/components/schemas/ChatAssistantImageAttachmentRequest")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ChatAssistantImageAttachmentRequest",
 *     type="object",
 *     required={"fileName", "mimeType", "contentBase64"},
 *     @OA\Property(property="fileName", type="string", example="captura.png"),
 *     @OA\Property(property="mimeType", type="string", example="image/png"),
 *     @OA\Property(property="contentBase64", type="string", example="iVBORw0KGgo=")
 * )
 *
 * @OA\Schema(
 *     schema="ChatAssistantDocumentReference",
 *     type="object",
 *     @OA\Property(property="title", type="string", example="Manual de usuario"),
 *     @OA\Property(property="path", type="string", example="99-manual-usuario/PedidosWeb.md")
 * )
 *
 * @OA\Schema(
 *     schema="ChatAssistantMessageReplyResultado",
 *     type="object",
 *     @OA\Property(property="reply", type="string", example="Texto orientativo de la respuesta"),
 *     @OA\Property(
 *         property="references",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/ChatAssistantDocumentReference")
 *     ),
 *     @OA\Property(property="requiresSupportFollowup", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeChatAssistantMessageReply",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/ChatAssistantMessageReplyResultado")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CargaAsistenteDraftRenglon",
 *     type="object",
 *     @OA\Property(property="renglon", type="integer", example=1),
 *     @OA\Property(property="codArticulo", type="string", example="ATS 0500"),
 *     @OA\Property(property="descripcion", type="string", example="ALMENDRA TOSTADA"),
 *     @OA\Property(property="cantidad", type="number", format="float", example=10),
 *     @OA\Property(property="precio", type="number", format="float", nullable=true, example=123.5),
 *     @OA\Property(property="porcBonif", type="number", format="float", nullable=true, example=3)
 * )
 *
 * @OA\Schema(
 *     schema="CargaAsistenteDraftContext",
 *     type="object",
 *     @OA\Property(property="modo", type="string", example="nuevo", description="nuevo|edicion|soloLectura"),
 *     @OA\Property(property="perfilUsuario", type="string", enum={"V","S","C"}, example="V"),
 *     @OA\Property(property="codCliente", type="string", nullable=true, example="C001"),
 *     @OA\Property(property="cabecera", type="object"),
 *     @OA\Property(property="renglones", type="array", @OA\Items(ref="#/components/schemas/CargaAsistenteDraftRenglon")),
 *     @OA\Property(property="readOnly", type="boolean", example=false),
 *     @OA\Property(property="codLista", type="integer", example=1)
 * )
 *
 * @OA\Schema(
 *     schema="CargaAsistenteTurnRequest",
 *     type="object",
 *     required={"modality","draftContext"},
 *     @OA\Property(property="message", type="string", example="Elimina el articulo arroz", description="Obligatorio si no hay images"),
 *     @OA\Property(property="modality", type="string", enum={"texto","audio","imagen"}, example="texto"),
 *     @OA\Property(property="credentialId", type="integer", nullable=true, example=1),
 *     @OA\Property(property="pendingChoice", type="object", nullable=true, description="Eco del turno anterior (lista numerada / confirm)"),
 *     @OA\Property(property="draftContext", ref="#/components/schemas/CargaAsistenteDraftContext"),
 *     @OA\Property(
 *         property="images",
 *         type="array",
 *         maxItems=4,
 *         @OA\Items(ref="#/components/schemas/ChatAssistantImageAttachmentRequest")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="CargaAsistenteActionItem",
 *     type="object",
 *     required={"action","payload","resultado"},
 *     @OA\Property(
 *         property="action",
 *         type="string",
 *         example="removeRenglon",
 *         description="noop|needsChoice|needsRefine|needsConfirm|selectCliente|clearDraftForClienteChange|setCabeceraField|setCabeceraFields|setCampoLibre|addRenglon|updateRenglon|removeRenglon|grabarPedido|grabarPresupuesto|showConsulta|applyImageExtract|denied|validationError|…"
 *     ),
 *     @OA\Property(property="payload", type="object"),
 *     @OA\Property(
 *         property="resultado",
 *         type="string",
 *         example="ok",
 *         description="ok|needsChoice|needsRefine|needsConfirm|denied|validationError"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="CargaAsistenteTurnResultado",
 *     type="object",
 *     required={"replyText","actions","configurationRequired"},
 *     @OA\Property(property="replyText", type="string", example="pedidos.carga.asistente.elegirRenglon", description="Clave i18n o texto; el panel resuelve claves pedidos.carga.asistente.*"),
 *     @OA\Property(property="actions", type="array", @OA\Items(ref="#/components/schemas/CargaAsistenteActionItem")),
 *     @OA\Property(property="pendingChoice", type="object", nullable=true),
 *     @OA\Property(property="configurationRequired", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeCargaAsistenteTurn",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/CargaAsistenteTurnResultado")
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CargaAsistenteConfigurationRequiredResultado",
 *     type="object",
 *     @OA\Property(property="configurationRequired", type="boolean", example=true),
 *     @OA\Property(property="preferencesPath", type="string", example="/preferences")
 * )
 *
 * @OA\Schema(
 *     schema="ComprobanteCabeceraRequest",
 *     type="object",
 *     required={"cod_cliente"},
 *     description="Cabecera de pedido/presupuesto (snake_case, contrato de grabación)",
 *     @OA\Property(property="cod_cliente", type="string", example="CLIMVP001"),
 *     @OA\Property(property="cod_vended", type="string", nullable=true, example="VENACOT01"),
 *     @OA\Property(property="cod_condvta", type="integer", nullable=true, example=1),
 *     @OA\Property(property="cod_transpor", type="string", nullable=true, example="TR01"),
 *     @OA\Property(property="id_de", type="integer", nullable=true, example=10),
 *     @OA\Property(property="nivel", type="integer", example=0, description="0 o 100"),
 *     @OA\Property(property="lista_precios", type="integer", nullable=true, example=1),
 *     @OA\Property(property="moneda", type="integer", example=1, description="0=extranjera, 1=local"),
 *     @OA\Property(property="incluye_iva", type="boolean", example=false),
 *     @OA\Property(property="bonif_1", type="number", format="float", example=0),
 *     @OA\Property(property="bonif_2", type="number", format="float", example=0),
 *     @OA\Property(property="bonif_3", type="number", format="float", example=0),
 *     @OA\Property(property="descuento", type="number", format="float", example=0, description="Bonificación neta de cabecera (calculada/enviada)"),
 *     @OA\Property(property="observaciones", type="string", nullable=true, example="Entrega urgente"),
 *     @OA\Property(property="cod_perfil", type="string", nullable=true, example="P01"),
 *     @OA\Property(property="expreso", type="string", nullable=true, example="Andreani"),
 *     @OA\Property(property="expreso_dire", type="string", nullable=true, example="Av. Siempre Viva 742"),
 *     @OA\Property(property="fecha_entrega", type="string", nullable=true, example="2026-08-10"),
 *     @OA\Property(property="leyenda_1", type="string", nullable=true),
 *     @OA\Property(property="leyenda_2", type="string", nullable=true),
 *     @OA\Property(property="leyenda_3", type="string", nullable=true),
 *     @OA\Property(property="leyenda_4", type="string", nullable=true),
 *     @OA\Property(property="leyenda_5", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ComprobanteRenglonRequest",
 *     type="object",
 *     required={"cod_articulo","cantidad","precio"},
 *     description="Renglón de pedido/presupuesto (snake_case)",
 *     @OA\Property(property="renglon", type="integer", example=1),
 *     @OA\Property(property="cod_articulo", type="string", example="ATS 0500"),
 *     @OA\Property(property="descripcion_articulo", type="string", example="ALMENDRA TOSTADA"),
 *     @OA\Property(property="cantidad", type="number", format="float", example=10, description="Cantidad en unidades de stock/precio"),
 *     @OA\Property(property="cantidad_venta", type="number", format="float", nullable=true, example=10, description="Cantidad en unidades de venta (CC PQ #10)"),
 *     @OA\Property(property="precio", type="number", format="float", example=123.5),
 *     @OA\Property(property="porc_bonif", type="number", format="float", example=3),
 *     @OA\Property(property="porc_iva", type="number", format="float", example=21, description="Porcentaje IVA (ej. 21, no 0.21)")
 * )
 *
 * @OA\Schema(
 *     schema="ComprobanteGrabarRequest",
 *     type="object",
 *     required={"accionGrabacion","cabecera","renglones"},
 *     @OA\Property(property="accionGrabacion", type="string", enum={"pedido","presupuesto"}, example="pedido"),
 *     @OA\Property(property="cod_pedido", type="string", nullable=true, example=null, description="Si viene, modifica comprobante existente"),
 *     @OA\Property(property="cod_pedido_origen", type="string", nullable=true, example=null, description="Conversión pedido→presupuesto"),
 *     @OA\Property(property="cod_presupuesto_origen", type="string", nullable=true, example=null, description="Conversión presupuesto→pedido"),
 *     @OA\Property(property="cod_comprobante_origen_copia", type="string", nullable=true, example=null, description="Alta por copia"),
 *     @OA\Property(property="cabecera", ref="#/components/schemas/ComprobanteCabeceraRequest"),
 *     @OA\Property(
 *         property="renglones",
 *         type="array",
 *         minItems=1,
 *         @OA\Items(ref="#/components/schemas/ComprobanteRenglonRequest")
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="ComprobanteUpsertRequest",
 *     type="object",
 *     required={"cabecera","renglones"},
 *     description="Body de alta/modificación legacy de pedidos o presupuestos",
 *     @OA\Property(property="cabecera", ref="#/components/schemas/ComprobanteCabeceraRequest"),
 *     @OA\Property(
 *         property="renglones",
 *         type="array",
 *         minItems=1,
 *         @OA\Items(ref="#/components/schemas/ComprobanteRenglonRequest")
 *     ),
 *     @OA\Property(property="cod_presupuesto_origen", type="string", nullable=true),
 *     @OA\Property(property="cod_pedido_origen", type="string", nullable=true)
 * )
 *
 * @OA\Schema(
 *     schema="ComprobanteGrabarResultado",
 *     type="object",
 *     @OA\Property(property="cod_pedido", type="string", example="A1B2C3D4-E5F6-7890-ABCD-EF1234567890"),
 *     @OA\Property(property="estado", type="integer", example=0),
 *     @OA\Property(property="nro_visible", type="integer", example=1234),
 *     @OA\Property(property="total", type="number", format="float", example=1500.25),
 *     @OA\Property(property="total_iva", type="number", format="float", example=315.05),
 *     @OA\Property(property="guidSufijo", type="string", example="567890"),
 *     @OA\Property(property="mailEnviado", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeComprobanteGrabar",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="resultado", ref="#/components/schemas/ComprobanteGrabarResultado")
 *         )
 *     },
 *     example={
 *         "error": 0,
 *         "respuesta": "ok",
 *         "resultado": {
 *             "cod_pedido": "A1B2C3D4-E5F6-7890-ABCD-EF1234567890",
 *             "estado": 0,
 *             "nro_visible": 1234,
 *             "total": 1500.25,
 *             "total_iva": 315.05,
 *             "guidSufijo": "567890",
 *             "mailEnviado": false
 *         }
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="CatalogoPerfilItem",
 *     type="object",
 *     @OA\Property(property="cod_perfil", type="string", example="P01"),
 *     @OA\Property(property="descripcion", type="string", example="Perfil estándar")
 * )
 *
 * @OA\Schema(
 *     schema="CatalogoCondicionVentaItem",
 *     type="object",
 *     @OA\Property(property="codigo", type="integer", example=1),
 *     @OA\Property(property="descripcion", type="string", example="Contado")
 * )
 *
 * @OA\Schema(
 *     schema="CatalogoTransporteItem",
 *     type="object",
 *     @OA\Property(property="codigo", type="string", example="TR01"),
 *     @OA\Property(property="descripcion", type="string", example="Retiro en planta")
 * )
 *
 * @OA\Schema(
 *     schema="CatalogoListaPreciosItem",
 *     type="object",
 *     @OA\Property(property="cod_lista", type="integer", example=1),
 *     @OA\Property(property="descripcion", type="string", example="Lista general"),
 *     @OA\Property(property="moneda", type="integer", example=1),
 *     @OA\Property(property="incluye_iva", type="boolean", example=false)
 * )
 *
 * @OA\Schema(
 *     schema="CatalogoDireccionEntregaItem",
 *     type="object",
 *     @OA\Property(property="id_de", type="integer", example=10),
 *     @OA\Property(property="direccion", type="string", example="Av. Siempre Viva 742"),
 *     @OA\Property(property="localidad", type="string", example="CABA"),
 *     @OA\Property(property="habitual", type="boolean", example=true)
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeCatalogoPerfiles",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", type="array", @OA\Items(ref="#/components/schemas/CatalogoPerfilItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeCatalogoCondicionesVenta",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", type="array", @OA\Items(ref="#/components/schemas/CatalogoCondicionVentaItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeCatalogoTransportes",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", type="array", @OA\Items(ref="#/components/schemas/CatalogoTransporteItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeCatalogoListasPrecios",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", type="array", @OA\Items(ref="#/components/schemas/CatalogoListaPreciosItem")))
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="ApiEnvelopeCatalogoDireccionesEntrega",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/ApiEnvelope"),
 *         @OA\Schema(type="object", @OA\Property(property="resultado", type="array", @OA\Items(ref="#/components/schemas/CatalogoDireccionEntregaItem")))
 *     }
 * )
 */
final class OpenApiSchemas
{
}
