import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const localesDir = path.resolve(__dirname, '../src/locales');
const parametrosDir = path.join(localesDir, 'parametros');
const localeFiles = ['en', 'it', 'fr', 'pt'];

for (const locale of localeFiles) {
  const localePath = path.join(localesDir, `${locale}.json`);
  const overridePath = path.join(parametrosDir, `pedidosWeb.${locale}.json`);

  if (!fs.existsSync(overridePath)) {
    console.warn(`Skip ${locale}: missing ${overridePath}`);
    continue;
  }

  const localeJson = JSON.parse(fs.readFileSync(localePath, 'utf8'));
  const overrideJson = JSON.parse(fs.readFileSync(overridePath, 'utf8'));

  for (const [key, value] of Object.entries(overrideJson)) {
    localeJson[key] = value;
  }

  fs.writeFileSync(localePath, `${JSON.stringify(localeJson, null, 2)}\n`);
  console.log(`Merged ${Object.keys(overrideJson).length} keys into ${locale}.json`);
}
