import { useRef } from 'react';
import './dxReportingRuntimeSetup';
import ReportDesigner, { Callbacks, RequestOptions } from 'devexpress-reporting-react/dx-report-designer';
import 'devextreme/dist/css/dx.light.css';
import '@devexpress/analytics-core/dist/css/dx-analytics.common.css';
import '@devexpress/analytics-core/dist/css/dx-analytics.light.css';
import '@devexpress/analytics-core/dist/css/dx-querybuilder.css';
import 'devexpress-reporting/dist/css/dx-webdocumentviewer.css';
import 'devexpress-reporting/dist/css/dx-reportdesigner.css';
import 'ace-builds/css/ace.css';
import 'ace-builds/css/theme/dreamweaver.css';
import 'ace-builds/css/theme/ambiance.css';
import type { PedidosWebDxReportSavedEvent } from './DxReportDesignerPanel';
import { parseDxReportSavedArgs } from './parseDxReportSavedArgs';

export type DxReportDesignerAppProps = {
  reportUrl: string;
  host: string;
  getDesignerModelAction: string;
  knownReportCodes?: string[];
  onDxReportSaved?: (event: PedidosWebDxReportSavedEvent) => void | Promise<void>;
};

function isUnknownEmissionReportCode(url: string, knownReportCodes: string[]): boolean {
  if (url === '') {
    return false;
  }
  return !knownReportCodes.includes(url);
}

/** Chunk aislado: CSS + widget DX Reporting (carga lazy). */
export default function DxReportDesignerApp({
  reportUrl,
  host,
  getDesignerModelAction,
  knownReportCodes = [],
  onDxReportSaved,
}: DxReportDesignerAppProps) {
  const pendingLayoutRef = useRef<string | undefined>(undefined);
  const useDevMode = import.meta.env.DEV;

  return (
    <ReportDesigner
      reportUrl={reportUrl}
      height="100%"
      width="100%"
      {...(useDevMode ? { developmentMode: true } : {})}
    >
      <RequestOptions host={host} getDesignerModelAction={getDesignerModelAction} />
      <Callbacks
        ReportSaving={(...callbackArgs: unknown[]) => {
          const parsed = parseDxReportSavedArgs(callbackArgs);
          if (parsed.layoutDefinition) {
            pendingLayoutRef.current = parsed.layoutDefinition;
          }
        }}
        ReportSaved={(...callbackArgs: unknown[]) => {
          if (!onDxReportSaved) {
            return;
          }
          const parsed = parseDxReportSavedArgs(callbackArgs);
          const layoutDefinition = parsed.layoutDefinition ?? pendingLayoutRef.current;
          pendingLayoutRef.current = undefined;
          void onDxReportSaved({
            url: parsed.url,
            layoutDefinition,
            isNew: parsed.url !== '' && isUnknownEmissionReportCode(parsed.url, knownReportCodes),
          });
        }}
      />
    </ReportDesigner>
  );
}
