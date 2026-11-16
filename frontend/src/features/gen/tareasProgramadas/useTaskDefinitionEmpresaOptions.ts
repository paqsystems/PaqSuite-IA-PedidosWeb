import { useMemo } from 'react';
import { useTranslation } from 'react-i18next';

type TaskDefinitionEmpresaOption = {
  id: number;
  nombreEmpresa: string;
  isDictionary?: boolean;
};

/**
 * MONO unified: empresa operativa única (id 1), alineado a `PedidosWebMonoBackupCompanyTargetResolver`.
 */
export function useTaskDefinitionEmpresaOptions(): TaskDefinitionEmpresaOption[] {
  const { t } = useTranslation();

  return useMemo(
    () => [
      {
        id: 1,
        nombreEmpresa: t('tareasProgramadas.definiciones.defaultEmpresaName', {
          defaultValue: 'Empresa',
        }),
      },
    ],
    [t],
  );
}
