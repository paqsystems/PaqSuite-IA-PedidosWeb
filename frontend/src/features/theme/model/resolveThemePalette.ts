export type ThemePalette = {
  accentColor: string;
  accentContrastColor: string;
  accentSoftColor: string;
  accentSoftHoverColor: string;
  borderColor: string;
  surfaceColor: string;
  panelColor: string;
  panelElevatedColor: string;
  textColor: string;
  mutedTextColor: string;
  footerBackgroundColor: string;
  footerTextColor: string;
  footerStrongTextColor: string;
  overlayColor: string;
  dangerColor: string;
};

type ThemeFamilyPalette = {
  accentColor: string;
  dark: {
    surfaceColor: string;
    panelColor: string;
    panelElevatedColor: string;
    footerBackgroundColor: string;
  };
};

const defaultFamilyPalette: ThemeFamilyPalette = {
  accentColor: '#4f46e5',
  dark: {
    surfaceColor: '#0f172a',
    panelColor: '#1e293b',
    panelElevatedColor: '#273449',
    footerBackgroundColor: '#0b1120',
  },
};

const familyPalettes: Array<{ matches: (themeKey: string) => boolean; palette: ThemeFamilyPalette }> = [
  {
    matches: (themeKey) => themeKey.includes('orange'),
    palette: {
      accentColor: '#f97316',
      dark: {
        surfaceColor: '#1b120c',
        panelColor: '#2f1d13',
        panelElevatedColor: '#462819',
        footerBackgroundColor: '#140d08',
      },
    },
  },
  {
    matches: (themeKey) => themeKey.includes('lime'),
    palette: {
      accentColor: '#84cc16',
      dark: {
        surfaceColor: '#11180d',
        panelColor: '#1d2a14',
        panelElevatedColor: '#2c3c1f',
        footerBackgroundColor: '#0d120a',
      },
    },
  },
  {
    matches: (themeKey) => themeKey.includes('teal'),
    palette: {
      accentColor: '#14b8a6',
      dark: {
        surfaceColor: '#0d1918',
        panelColor: '#15302d',
        panelElevatedColor: '#1d4742',
        footerBackgroundColor: '#091211',
      },
    },
  },
  {
    matches: (themeKey) => themeKey.includes('greenmist'),
    palette: {
      accentColor: '#22c55e',
      dark: {
        surfaceColor: '#0d1710',
        panelColor: '#153120',
        panelElevatedColor: '#1d472b',
        footerBackgroundColor: '#09110c',
      },
    },
  },
  {
    matches: (themeKey) => themeKey.includes('purple') || themeKey.includes('darkviolet'),
    palette: {
      accentColor: '#8b5cf6',
      dark: {
        surfaceColor: '#141122',
        panelColor: '#231a3d',
        panelElevatedColor: '#34235a',
        footerBackgroundColor: '#0f0c1a',
      },
    },
  },
  {
    matches: (themeKey) => themeKey.includes('carmine'),
    palette: {
      accentColor: '#e11d48',
      dark: {
        surfaceColor: '#1b1015',
        panelColor: '#341723',
        panelElevatedColor: '#4f1d31',
        footerBackgroundColor: '#130b0f',
      },
    },
  },
  {
    matches: (themeKey) => themeKey.includes('contrast'),
    palette: {
      accentColor: '#facc15',
      dark: {
        surfaceColor: '#101010',
        panelColor: '#1a1a1a',
        panelElevatedColor: '#262626',
        footerBackgroundColor: '#090909',
      },
    },
  },
  {
    matches: (themeKey) => themeKey.includes('darkmoon'),
    palette: {
      accentColor: '#60a5fa',
      dark: {
        surfaceColor: '#0c1324',
        panelColor: '#15213d',
        panelElevatedColor: '#1e3158',
        footerBackgroundColor: '#090f1b',
      },
    },
  },
  {
    matches: (themeKey) => themeKey.includes('softblue') || themeKey.includes('blue') || themeKey.includes('fluent.saas'),
    palette: {
      accentColor: '#3b82f6',
      dark: {
        surfaceColor: '#0d1726',
        panelColor: '#15253d',
        panelElevatedColor: '#1f3558',
        footerBackgroundColor: '#09101a',
      },
    },
  },
];

function withAlpha(hexColor: string, alpha: number): string {
  const normalizedHex = hexColor.replace('#', '');

  if (normalizedHex.length !== 6) {
    return hexColor;
  }

  const red = Number.parseInt(normalizedHex.slice(0, 2), 16);
  const green = Number.parseInt(normalizedHex.slice(2, 4), 16);
  const blue = Number.parseInt(normalizedHex.slice(4, 6), 16);

  return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
}

function isDarkTheme(themeKey: string): boolean {
  return (
    themeKey.includes('.dark')
    || themeKey.includes('dark.')
    || themeKey === 'contrast'
    || themeKey === 'contrast.compact'
    || themeKey.startsWith('darkmoon')
    || themeKey.startsWith('darkviolet')
  );
}

function resolveFamilyPalette(themeKey: string): ThemeFamilyPalette {
  return familyPalettes.find(({ matches }) => matches(themeKey))?.palette ?? defaultFamilyPalette;
}

export function resolveThemePalette(themeKey: string): ThemePalette {
  const familyPalette = resolveFamilyPalette(themeKey);
  const isDark = isDarkTheme(themeKey);

  if (isDark) {
    return {
      accentColor: familyPalette.accentColor,
      accentContrastColor: '#ffffff',
      accentSoftColor: withAlpha(familyPalette.accentColor, 0.24),
      accentSoftHoverColor: withAlpha(familyPalette.accentColor, 0.34),
      borderColor: withAlpha('#ffffff', 0.12),
      surfaceColor: familyPalette.dark.surfaceColor,
      panelColor: familyPalette.dark.panelColor,
      panelElevatedColor: familyPalette.dark.panelElevatedColor,
      textColor: '#f8fafc',
      mutedTextColor: '#cbd5e1',
      footerBackgroundColor: familyPalette.dark.footerBackgroundColor,
      footerTextColor: '#cbd5e1',
      footerStrongTextColor: '#ffffff',
      overlayColor: 'rgba(2, 6, 23, 0.55)',
      dangerColor: '#fda4af',
    };
  }

  return {
    accentColor: familyPalette.accentColor,
    accentContrastColor: '#1f2937',
    accentSoftColor: withAlpha(familyPalette.accentColor, 0.14),
    accentSoftHoverColor: withAlpha(familyPalette.accentColor, 0.2),
    borderColor: '#d9dee7',
    surfaceColor: '#f5f7fb',
    panelColor: '#ffffff',
    panelElevatedColor: '#ffffff',
    textColor: '#1f2937',
    mutedTextColor: '#64748b',
    footerBackgroundColor: withAlpha(familyPalette.accentColor, 0.94),
    footerTextColor: '#eef2ff',
    footerStrongTextColor: '#ffffff',
    overlayColor: 'rgba(15, 23, 42, 0.35)',
    dangerColor: '#b91c1c',
  };
}
