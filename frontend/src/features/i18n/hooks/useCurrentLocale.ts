import { createContext, useContext } from 'react';

export type CurrentLocaleContextValue = {
  currentLocale: string;
  changeLocale: (locale: string) => Promise<void>;
  isSaving: boolean;
  saveErrorKey: string | null;
};

export const CurrentLocaleContext = createContext<CurrentLocaleContextValue | null>(null);

export function useCurrentLocale(): CurrentLocaleContextValue {
  const context = useContext(CurrentLocaleContext);

  if (context === null) {
    throw new Error('useCurrentLocale debe usarse dentro de LocaleProvider');
  }

  return context;
}
