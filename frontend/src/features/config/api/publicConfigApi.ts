import { apiRequest } from '../../../shared/http/client';

export type PublicConfig = {
  gridLayoutsEnabled: boolean;
  pivotsEnabled: boolean;
  pivotLayoutsEnabled: boolean;
  excelImportEnabled: boolean;
  securityAdminEnabled: boolean;
};

export async function fetchPublicConfig(): Promise<PublicConfig> {
  const response = await apiRequest<Partial<PublicConfig>>('/config/public');

  return {
    gridLayoutsEnabled: Boolean(response.resultado.gridLayoutsEnabled),
    pivotsEnabled: Boolean(response.resultado.pivotsEnabled),
    pivotLayoutsEnabled: Boolean(response.resultado.pivotLayoutsEnabled),
    excelImportEnabled: Boolean(response.resultado.excelImportEnabled),
    securityAdminEnabled: Boolean(response.resultado.securityAdminEnabled),
  };
}
