export function resolveMenuNodeType(
  routePath: string | null | undefined,
  tipoProceso: string | null | undefined,
): 'process' | 'group' {
  if ((routePath ?? '').trim() !== '') {
    return 'process';
  }

  if ((tipoProceso ?? '').trim().toUpperCase() === 'P') {
    return 'process';
  }

  return 'group';
}
