import { createContext, useContext } from 'react';

export type CurrentThemeContextValue = {
  currentTheme: string;
  persistedTheme: string;
  previewTheme: (themeKey: string) => void;
  revertThemePreview: () => void;
  confirmTheme: (themeKey: string) => Promise<boolean>;
  isSaving: boolean;
  saveErrorKey: string | null;
};

export const CurrentThemeContext = createContext<CurrentThemeContextValue | null>(null);

export function useCurrentTheme(): CurrentThemeContextValue {
  const context = useContext(CurrentThemeContext);

  if (context === null) {
    throw new Error('useCurrentTheme debe usarse dentro de ThemeProvider');
  }

  return context;
}
