/**
 * DevExtreme omite `event` en cambios programáticos (p. ej. hidratación de comprobante).
 * @see https://js.devexpress.com/Documentation/ApiReference/UI_Components/dxSelectBox/Configuration/#onValueChanged
 */
export function isDevExtremeUserChange(event: { event?: unknown }): boolean {
  return event.event !== undefined && event.event !== null;
}
