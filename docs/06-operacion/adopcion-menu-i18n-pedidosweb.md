# PedidosWeb — menú i18n (GEN-07 H-W0)

- Config: `backend/config/paqsuite.php` → `menuI18n.driver=jsonLocales` y paths a `frontend/src/locales/*.json`.
- Validate (desde `backend/`): `php artisan paqsuite:menu-i18n:validate` (requiere `MenuQueryRepository` + menú MVP seedeado).
- Claves menú: `menu.{menuKey}` alineadas a `config/paqsuite_mvp.php` y traducciones en JSON (5 locales).
- Gate install: `PAQSUITE_MENU_I18N_INSTALL_GATE=false` hasta validate local OK.

Guía transversal: PaqSuite-IA-FRAMEWORK `docs/06-operacion/adopcion-menu-i18n.md`.
