import { useCallback, useEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { isNativeApp } from '../../../../shared/platform/isNativeApp';
import { fetchPublicConfig } from '../../../config/api/publicConfigApi';
import type { ExcelImportHostResult } from '../../../excelImport/types/excelImportHostTypes';
import { fetchExcelGrupos } from '../api/fetchExcelGrupos';
import { ImportacionMasivaEliminarModal } from '../components/ImportacionMasivaEliminarModal';
import { ImportacionMasivaGrid } from '../components/ImportacionMasivaGrid';
import { ImportacionMasivaProgresoOverlay } from '../components/ImportacionMasivaProgresoOverlay';
import { ImportacionMasivaReimportModal } from '../components/ImportacionMasivaReimportModal';
import { ImportacionMasivaSalidaModal } from '../components/ImportacionMasivaSalidaModal';
import { ImportacionMasivaToolbar } from '../components/ImportacionMasivaToolbar';
import { useImportacionMasivaBorrador } from '../hooks/useImportacionMasivaBorrador';
import { useImportacionMasivaGrabacion } from '../hooks/useImportacionMasivaGrabacion';
import { useImportacionMasivaNavigationGuard } from '../hooks/useImportacionMasivaNavigationGuard';
import type { BorradorFila } from '../types/importacionMasivaTypes';
import { mapGruposToBorradorFilas } from '../utils/mapGrupoToBorradorFila';
import { persistImportacionMasivaBorrador } from '../utils/importacionMasivaBorradorStorage';
import './importacionMasivaPage.css';

export function ImportacionMasivaPage() {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [excelImportEnabled, setExcelImportEnabled] = useState(false);
  const [reimportVisible, setReimportVisible] = useState(false);
  const [eliminarVisible, setEliminarVisible] = useState(false);
  const [filaEliminar, setFilaEliminar] = useState<BorradorFila | null>(null);
  const pendingGruposRef = useRef<Awaited<ReturnType<typeof fetchExcelGrupos>> | null>(null);

  const {
    filas,
    filasGrid,
    tieneFilasPendientes,
    replaceFilas,
    appendFilas,
    removeFila,
    clearFilas,
    setEsPedido,
    setTodasEsPedido,
    setErrorFila,
  } = useImportacionMasivaBorrador();

  const { progreso, isGrabando, grabarLote } = useImportacionMasivaGrabacion({
    onFilaOk: removeFila,
    onFilaError: setErrorFila,
  });

  const uiBloqueada = isGrabando;

  const { salidaVisible, closeSalidaModal, confirmSalida, requestSalida } =
    useImportacionMasivaNavigationGuard({
      enabled: tieneFilasPendientes && !isGrabando,
      onSalidaAccion: async (accion) => {
        if (accion === 'cancelar') {
          clearFilas();
          return;
        }

        if (accion === 'grabarTodo') {
          await grabarLote(filas);
        }
      },
    });

  useEffect(() => {
    if (isNativeApp()) {
      navigate('/consultas/stock', { replace: true });
    }
  }, [navigate]);

  useEffect(() => {
    void fetchPublicConfig()
      .then((config) => setExcelImportEnabled(config.excelImportEnabled))
      .catch(() => setExcelImportEnabled(false));
  }, []);

  const applyGruposImportados = useCallback(
    (grupos: Awaited<ReturnType<typeof fetchExcelGrupos>>) => {
      const nuevasFilas = mapGruposToBorradorFilas(grupos);
      if (filas.length === 0) {
        replaceFilas(nuevasFilas);
        return;
      }

      pendingGruposRef.current = grupos;
      setReimportVisible(true);
    },
    [filas.length, replaceFilas],
  );

  const handleExcelImportComplete = useCallback(
    async (result: ExcelImportHostResult) => {
      if (result.validRows.length === 0) {
        return;
      }

      try {
        const grupos = await fetchExcelGrupos(result.guidImportacion);
        if (grupos.length === 0) {
          return;
        }

        applyGruposImportados(grupos);
      } catch {
        // Sin grupos válidos: no-op silencioso; el host ya informó errores si los hubo.
      }
    },
    [applyGruposImportados],
  );

  const handleReimportReplace = useCallback(() => {
    if (pendingGruposRef.current) {
      replaceFilas(mapGruposToBorradorFilas(pendingGruposRef.current));
      pendingGruposRef.current = null;
    }
    setReimportVisible(false);
  }, [replaceFilas]);

  const handleReimportAppend = useCallback(() => {
    if (pendingGruposRef.current) {
      appendFilas(mapGruposToBorradorFilas(pendingGruposRef.current));
      pendingGruposRef.current = null;
    }
    setReimportVisible(false);
  }, [appendFilas]);

  const handleReimportCancel = useCallback(() => {
    pendingGruposRef.current = null;
    setReimportVisible(false);
  }, []);

  const handleConsultar = useCallback(
    (fila: BorradorFila) => {
      persistImportacionMasivaBorrador(filas);
      navigate('/pedidos/carga', {
        state: {
          mode: 'readonly',
          from: 'importacionMasiva',
          borrador: {
            idInterno: fila.idInterno,
            cabecera: fila.cabecera,
            renglones: fila.renglones,
            esPedido: fila.esPedido,
          },
        },
      });
    },
    [filas, navigate],
  );

  const handleEliminarRequest = useCallback((fila: BorradorFila) => {
    setFilaEliminar(fila);
    setEliminarVisible(true);
  }, []);

  const handleEliminarConfirm = useCallback(() => {
    if (filaEliminar) {
      removeFila(filaEliminar.idInterno);
    }
    setFilaEliminar(null);
    setEliminarVisible(false);
  }, [filaEliminar, removeFila]);

  const handleGrabar = useCallback(async () => {
    await grabarLote(filas);
  }, [filas, grabarLote]);

  if (isNativeApp()) {
    return null;
  }

  return (
    <section className="importacionMasivaPage" data-testid="importacionMasivaPage">
      <div className="importacionMasivaPage__header">
        <h2>{t('pages.importacionMasiva')}</h2>
      </div>

      <ImportacionMasivaToolbar
        excelImportEnabled={excelImportEnabled}
        disabled={uiBloqueada}
        tieneFilas={filas.length > 0}
        onImportComplete={handleExcelImportComplete}
        onMarcarPedidos={() => setTodasEsPedido(true)}
        onMarcarPresupuestos={() => setTodasEsPedido(false)}
        onGrabar={handleGrabar}
      />

      <div data-testid="importacionMasivaGrid">
        <ImportacionMasivaGrid
          filas={filasGrid}
          disabled={uiBloqueada}
          onToggleTipo={setEsPedido}
          onConsultar={handleConsultar}
          onEliminar={handleEliminarRequest}
        />
      </div>

      <ImportacionMasivaProgresoOverlay progreso={progreso} />

      <ImportacionMasivaReimportModal
        visible={reimportVisible}
        onReplace={handleReimportReplace}
        onAppend={handleReimportAppend}
        onCancel={handleReimportCancel}
      />

      <ImportacionMasivaSalidaModal
        visible={salidaVisible}
        onConfirm={confirmSalida}
        onCancel={closeSalidaModal}
      />

      <ImportacionMasivaEliminarModal
        visible={eliminarVisible}
        onConfirm={handleEliminarConfirm}
        onCancel={() => {
          setEliminarVisible(false);
          setFilaEliminar(null);
        }}
      />

      {tieneFilasPendientes ? (
        <button type="button" hidden data-testid="importacionMasivaTriggerSalida" onClick={requestSalida} />
      ) : null}
    </section>
  );
}
