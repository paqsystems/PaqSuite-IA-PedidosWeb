import { readFileSync } from 'node:fs';
import { resolve } from 'node:path';
import { describe, expect, it } from 'vitest';

function readLocaleCatalog(localeCode: string): Record<string, string> {
  const filePath = resolve(process.cwd(), 'src/locales', `${localeCode}.json`);
  return JSON.parse(readFileSync(filePath, 'utf8')) as Record<string, string>;
}

describe('localeCatalogParity', () => {
  const es = readLocaleCatalog('es');
  const en = readLocaleCatalog('en');
  const itCatalog = readLocaleCatalog('it');
  const esKeys = Object.keys(es).sort();

  it('es.json define claves base del slice', () => {
    expect(esKeys.length).toBeGreaterThan(20);
    expect(es).toHaveProperty('login.submit');
    expect(es).toHaveProperty('grid.column.name');
  });

  it('paridad de claves entre es y en', () => {
    expect(Object.keys(en).sort()).toEqual(esKeys);
  });

  it('paridad de claves entre es y it', () => {
    expect(Object.keys(itCatalog).sort()).toEqual(esKeys);
  });
});
