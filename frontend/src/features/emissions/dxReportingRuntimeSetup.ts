/**
 * DevExpress Reporting usa `import * as ko from 'knockout'` y llama `ko.observable`.
 * El plugin de Vite reescribe ese import a default y envuelve el UMD; este setup
 * fija `globalThis.ko` / `ace` antes de montar el widget.
 */
import ace from 'ace-builds/src-noconflict/ace';
import config from 'devextreme/core/config';
import $ from 'jquery';
import * as ko from 'knockout';

if (typeof ko.observable !== 'function') {
  throw new Error('Knockout observable is not a function');
}

if (typeof $.Deferred !== 'function') {
  throw new Error('jQuery Deferred is not a function');
}

config({ useJQuery: true } as Parameters<typeof config>[0]);

type ReportingGlobals = typeof globalThis & {
  ko?: typeof ko;
  ace?: typeof ace;
  jQuery?: typeof $;
  $?: typeof $;
  __paqDxDesignerProcessCode?: string;
};

const g = globalThis as ReportingGlobals;
g.ko = ko;
g.ace = ace;
g.jQuery = $;
g.$ = $;

/** El sidecar lee processCode en form o query; el cliente DX a veces solo manda reportUrl. */
export function setDxDesignerProcessCode(processCode: string): void {
  g.__paqDxDesignerProcessCode = processCode.trim();
}

const ajaxPrefilterInstalled = (g as ReportingGlobals & { __paqDxProcessCodeAjax?: boolean })
  .__paqDxProcessCodeAjax;
if (!ajaxPrefilterInstalled) {
  $.ajaxPrefilter((options) => {
    const processCode = g.__paqDxDesignerProcessCode?.trim() ?? '';
    if (processCode === '') {
      return;
    }
    const url = String(options.url ?? '');
    if (!/DXXRD(?:\/|$|\?)/i.test(url) || !/GetDesignerModel/i.test(url)) {
      return;
    }
    if (typeof options.data === 'string') {
      if (!/(^|&)processCode=/.test(options.data)) {
        options.data = `${options.data}${options.data ? '&' : ''}processCode=${encodeURIComponent(processCode)}`;
      }
      return;
    }
    if (options.data && typeof options.data === 'object' && !('processCode' in (options.data as object))) {
      (options.data as Record<string, string>).processCode = processCode;
    }
  });
  (g as ReportingGlobals & { __paqDxProcessCodeAjax?: boolean }).__paqDxProcessCodeAjax = true;
}

export { ace, ko, $ };
