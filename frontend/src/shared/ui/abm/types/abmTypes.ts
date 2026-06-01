export type AbmPermissions = {
  alta: boolean;
  modi: boolean;
  baja: boolean;
  repo: boolean;
};

export type AbmModalMode = 'create' | 'edit' | 'view';

export const abmFullPermissions: AbmPermissions = {
  alta: true,
  modi: true,
  baja: true,
  repo: true,
};
