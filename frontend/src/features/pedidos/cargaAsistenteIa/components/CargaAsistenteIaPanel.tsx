import { useCallback, useId, useLayoutEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import TextBox from 'devextreme-react/text-box';
import { isDevExtremeUserChange } from '../../../../shared/ui/devextremeUserChange';
import { ApiClientError } from '../../../../shared/http/client';
import { postCargaAsistenteTurn } from '../api/postCargaAsistenteTurn';
import { useCargaAsistenteSpeech } from '../hooks/useCargaAsistenteSpeech';
import type {
  CargaAsistenteDraftContext,
  CargaAsistenteImagePayload,
  CargaAsistenteModality,
  CargaAsistentePendingChoice,
} from '../model/cargaAsistenteTypes';
import { extractShowConsultaPayload, type ShowConsultaPayload } from '../utils/formatShowConsulta';
import {
  applyCargaAsistenteActions,
  type CargaAsistenteAddRenglonPayload,
} from '../utils/applyCargaAsistenteActions';
import {
  cargaAsistenteMaxImages,
  fileToBase64Image,
  isAllowedCargaAsistenteImageFile,
} from '../utils/fileToBase64Image';
import { CargaAsistenteConsultaTable } from './CargaAsistenteConsultaTable';
import './CargaAsistenteIaPanel.css';

type CargaAsistenteIaMessage = {
  id: string;
  role: 'user' | 'assistant';
  content: string;
  consulta?: ShowConsultaPayload | null;
};

export type CargaAsistenteIaPanelProps = {
  buildDraftContext: () => CargaAsistenteDraftContext;
  readOnly: boolean;
  onSelectCliente: (codCliente: string) => Promise<void>;
  onClearDraft: () => void;
  onAddRenglon: (payload: CargaAsistenteAddRenglonPayload) => void;
  onUpdateRenglon?: (payload: CargaAsistenteUpdateRenglonPayload) => void;
  onRemoveRenglon?: (renglon: number) => void;
  onUpdateCabeceraField: (field: string, value: unknown) => void;
  onPatchCabeceraFields?: (fields: Record<string, unknown>) => void;
  onGrabarPedido: () => void;
  onGrabarPresupuesto: () => void;
  onApplyImageExtract?: (payload: Record<string, unknown>) => void;
};

function isConfigurationRequired(
  error: unknown,
  resultado?: { configurationRequired?: boolean },
): boolean {
  if (resultado?.configurationRequired === true) {
    return true;
  }

  if (!(error instanceof ApiClientError)) {
    return false;
  }

  if (error.respuestaKey === 'pedidos.carga.asistente.configurationRequired') {
    return true;
  }

  const errorResultado = error.resultado as { configurationRequired?: boolean } | undefined;
  return errorResultado?.configurationRequired === true;
}

function createMessageId(): string {
  return `carga-asistente-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
}

function resolveAsistenteReplyText(
  replyText: string | undefined,
  translate: (key: string, options?: Record<string, unknown>) => string,
  searchedQ?: string,
): string {
  const text = (replyText ?? '').trim();
  if (text === '') {
    return translate('pedidos.carga.asistente.emptyHint');
  }

  if (
    text === 'pedidos.carga.asistente.renglonNoEncontradoConQ'
    || (text === 'pedidos.carga.asistente.renglonNoEncontrado' && searchedQ)
  ) {
    return translate('pedidos.carga.asistente.renglonNoEncontradoConQ', {
      q: searchedQ ?? '',
    });
  }

  if (text.startsWith('pedidos.carga.asistente.')) {
    return translate(text);
  }

  return text;
}

function extractSearchedQFromActions(
  actions: Array<{ action?: string; payload?: Record<string, unknown> }> | undefined,
): string | undefined {
  if (!Array.isArray(actions)) {
    return undefined;
  }

  for (const item of actions) {
    if (item.action !== 'needsRefine') {
      continue;
    }
    const q = item.payload?.q;
    if (typeof q === 'string' && q.trim() !== '') {
      return q.trim();
    }
  }

  return undefined;
}

function formatAssistantReply(
  replyText: string | undefined,
  pending: CargaAsistentePendingChoice | null | undefined,
  translate: (key: string, options?: Record<string, unknown>) => string,
  actions?: Array<{ action?: string; payload?: Record<string, unknown> }>,
): string {
  let text = resolveAsistenteReplyText(
    replyText,
    translate,
    extractSearchedQFromActions(actions),
  );
  const options = pending?.options;

  if (!Array.isArray(options) || options.length === 0) {
    return text;
  }

  const lines = options.map((option) => {
    const n = option.n ?? '';
    const label = String(option.label ?? option.code ?? '');
    return `${n}. ${label}`;
  });

  return `${text}\n${lines.join('\n')}`;
}

export function CargaAsistenteIaPanel({
  buildDraftContext,
  readOnly: _readOnly,
  onSelectCliente,
  onClearDraft,
  onAddRenglon,
  onUpdateRenglon,
  onRemoveRenglon,
  onUpdateCabeceraField,
  onPatchCabeceraFields,
  onGrabarPedido,
  onGrabarPresupuesto,
  onApplyImageExtract,
}: CargaAsistenteIaPanelProps) {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const fileInputId = useId();
  const fileInputRef = useRef<HTMLInputElement | null>(null);
  const threadRef = useRef<HTMLDivElement | null>(null);

  const [expanded, setExpanded] = useState(false);
  const [inputValue, setInputValue] = useState('');
  const [messages, setMessages] = useState<CargaAsistenteIaMessage[]>([]);
  const [pendingChoice, setPendingChoice] = useState<CargaAsistentePendingChoice>(null);
  const [pendingImages, setPendingImages] = useState<CargaAsistenteImagePayload[]>([]);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [configurationRequired, setConfigurationRequired] = useState(false);
  const [statusMessage, setStatusMessage] = useState<string | null>(null);

  const appendAssistantMessage = useCallback(
    (content: string, consulta?: ShowConsultaPayload | null) => {
      setMessages((current) => [
        ...current,
        {
          id: createMessageId(),
          role: 'assistant',
          content,
          consulta: consulta ?? null,
        },
      ]);
    },
    [],
  );

  useLayoutEffect(() => {
    if (!expanded) {
      return;
    }

    const thread = threadRef.current;
    if (!thread) {
      return;
    }

    // Solo scroll interno del hilo (no scrollIntoView: mueve la página y corta el último mensaje).
    thread.scrollTop = thread.scrollHeight;
  }, [expanded, messages, statusMessage, isSubmitting]);

  const handleSpeechError = useCallback(
    (reason: 'unsupported' | 'denied' | 'error') => {
      if (reason === 'unsupported') {
        setStatusMessage(t('pedidos.carga.asistente.micUnsupported'));
        return;
      }

      if (reason === 'denied') {
        setStatusMessage(t('pedidos.carga.asistente.micDenied'));
        return;
      }

      setStatusMessage(t('pedidos.carga.asistente.errorGeneric'));
    },
    [t],
  );

  const submitTurn = useCallback(
    async (rawMessage: string, modality: CargaAsistenteModality, images: CargaAsistenteImagePayload[]) => {
      const message = rawMessage.trim();
      if (message === '' && images.length === 0) {
        return;
      }

      const userContent =
        message !== ''
          ? message
          : t('pedidos.carga.asistente.attach') + ` (${images.length})`;

      setMessages((current) => [
        ...current,
        { id: createMessageId(), role: 'user', content: userContent },
      ]);
      setInputValue('');
      setPendingImages([]);
      setStatusMessage(null);
      setConfigurationRequired(false);
      setIsSubmitting(true);

      try {
        const resultado = await postCargaAsistenteTurn({
          message,
          modality,
          draftContext: buildDraftContext(),
          pendingChoice,
          images: images.length > 0 ? images : undefined,
        });

        if (resultado.configurationRequired) {
          setConfigurationRequired(true);
          appendAssistantMessage(t('pedidos.carga.asistente.configurationRequired'));
          setPendingChoice(null);
          return;
        }

        appendAssistantMessage(
          formatAssistantReply(
            resultado.replyText,
            resultado.pendingChoice,
            t,
            resultado.actions,
          ),
          extractShowConsultaPayload(resultado.actions),
        );
        setPendingChoice(resultado.pendingChoice ?? null);

        await applyCargaAsistenteActions(resultado.actions ?? [], {
          selectCliente: onSelectCliente,
          clearDraft: onClearDraft,
          addRenglon: onAddRenglon,
          updateRenglon: onUpdateRenglon,
          removeRenglon: onRemoveRenglon,
          updateCabeceraField: onUpdateCabeceraField,
          patchCabeceraFields: onPatchCabeceraFields,
          grabarPedido: onGrabarPedido,
          grabarPresupuesto: onGrabarPresupuesto,
          applyImageExtract: onApplyImageExtract,
        });
      } catch (error) {
        if (isConfigurationRequired(error)) {
          setConfigurationRequired(true);
          appendAssistantMessage(t('pedidos.carga.asistente.configurationRequired'));
          setPendingChoice(null);
          return;
        }

        if (error instanceof ApiClientError) {
          const key = error.respuestaKey;
          if (key.startsWith('pedidos.carga.asistente.')) {
            appendAssistantMessage(t(key));
            return;
          }
        }

        appendAssistantMessage(t('pedidos.carga.asistente.errorGeneric'));
      } finally {
        setIsSubmitting(false);
      }
    },
    [
      appendAssistantMessage,
      buildDraftContext,
      onAddRenglon,
      onUpdateRenglon,
      onRemoveRenglon,
      onApplyImageExtract,
      onClearDraft,
      onGrabarPedido,
      onGrabarPresupuesto,
      onSelectCliente,
      onUpdateCabeceraField,
      onPatchCabeceraFields,
      pendingChoice,
      t,
    ],
  );

  const speech = useCargaAsistenteSpeech({
    onTranscript: (text) => {
      void submitTurn(text, 'audio', []);
    },
    onError: handleSpeechError,
  });

  const handleSend = () => {
    const modality: CargaAsistenteModality =
      pendingImages.length > 0 ? 'imagen' : 'texto';
    void submitTurn(inputValue, modality, pendingImages);
  };

  const handleAttachClick = () => {
    fileInputRef.current?.click();
  };

  const handleFilesSelected = async (fileList: FileList | null) => {
    if (!fileList || fileList.length === 0) {
      return;
    }

    const files = Array.from(fileList);
    if (pendingImages.length + files.length > cargaAsistenteMaxImages) {
      setStatusMessage(t('pedidos.carga.asistente.errorGeneric'));
      return;
    }

    if (files.some((file) => !isAllowedCargaAsistenteImageFile(file))) {
      setStatusMessage(t('pedidos.carga.asistente.errorGeneric'));
      return;
    }

    try {
      const encoded = await Promise.all(files.map((file) => fileToBase64Image(file)));
      setPendingImages((current) => [...current, ...encoded]);
      setStatusMessage(null);
    } catch {
      setStatusMessage(t('pedidos.carga.asistente.errorGeneric'));
    }
  };

  return (
    <section
      className={[
        'cargaAsistenteIaPanel',
        expanded ? 'cargaAsistenteIaPanel--expanded' : 'cargaAsistenteIaPanel--collapsed',
      ].join(' ')}
      data-testid="cargaAsistenteIaPanel"
    >
      <header className="cargaAsistenteIaPanel__header">
        <h3 className="cargaAsistenteIaPanel__title">{t('pedidos.carga.asistente.title')}</h3>
        <Button
          text={
            expanded
              ? t('pedidos.carga.asistente.collapse')
              : t('pedidos.carga.asistente.expand')
          }
          stylingMode="text"
          elementAttr={{ 'data-testid': 'cargaAsistenteIaToggle' }}
          onClick={() => {
            setExpanded((current) => !current);
          }}
        />
      </header>

      {expanded ? (
        <div className="cargaAsistenteIaPanel__content">
          <div
            ref={threadRef}
            className="cargaAsistenteIaPanel__body"
            data-testid="cargaAsistenteIaThread"
          >
            {messages.length === 0 ? (
              <p className="cargaAsistenteIaPanel__hint">{t('pedidos.carga.asistente.emptyHint')}</p>
            ) : (
              messages.map((message) => (
                <div
                  key={message.id}
                  className={[
                    'cargaAsistenteIaPanel__message',
                    `cargaAsistenteIaPanel__message--${message.role}`,
                  ].join(' ')}
                >
                  <div className="cargaAsistenteIaPanel__messageText">{message.content}</div>
                  {message.role === 'assistant' && message.consulta ? (
                    <CargaAsistenteConsultaTable payload={message.consulta} />
                  ) : null}
                </div>
              ))
            )}
          </div>

          {configurationRequired ? (
            <div className="cargaAsistenteIaPanel__configGate" role="status">
              <p>{t('pedidos.carga.asistente.configurationRequired')}</p>
              <Button
                text={t('pedidos.carga.asistente.goToPreferences')}
                type="default"
                elementAttr={{ 'data-testid': 'cargaAsistenteIaGoToPreferences' }}
                onClick={() => {
                  navigate('/preferences');
                }}
              />
            </div>
          ) : null}

          {statusMessage ? (
            <p className="cargaAsistenteIaPanel__status" role="status">
              {statusMessage}
            </p>
          ) : null}

          {speech.isListening ? (
            <p className="cargaAsistenteIaPanel__listening" data-testid="cargaAsistenteIaListening">
              {t('pedidos.carga.asistente.listening')}
            </p>
          ) : null}

          {pendingImages.length > 0 ? (
            <p className="cargaAsistenteIaPanel__attachments" data-testid="cargaAsistenteIaAttachments">
              {t('pedidos.carga.asistente.attach')}: {pendingImages.length}
            </p>
          ) : null}

          <div className="cargaAsistenteIaPanel__composer">
            <TextBox
              value={inputValue}
              valueChangeEvent="input"
              disabled={isSubmitting}
              placeholder={t('pedidos.carga.asistente.placeholder')}
              onValueChanged={(event) => {
                if (!isDevExtremeUserChange(event)) {
                  return;
                }

                setInputValue(String(event.value ?? ''));
              }}
              onEnterKey={() => {
                if (!isSubmitting) {
                  handleSend();
                }
              }}
              inputAttr={{ 'data-testid': 'cargaAsistenteIaInput' }}
            />

            <div className="cargaAsistenteIaPanel__actions">
              <Button
                text={t('pedidos.carga.asistente.send')}
                type="default"
                disabled={isSubmitting || (inputValue.trim() === '' && pendingImages.length === 0)}
                elementAttr={{ 'data-testid': 'cargaAsistenteIaSend' }}
                onClick={handleSend}
              />
              <Button
                text={t('pedidos.carga.asistente.mic')}
                stylingMode="outlined"
                disabled={isSubmitting}
                elementAttr={{ 'data-testid': 'cargaAsistenteIaMic' }}
                onClick={() => {
                  if (speech.isListening) {
                    speech.stop();
                    return;
                  }

                  if (!speech.supported) {
                    handleSpeechError('unsupported');
                    return;
                  }

                  speech.start();
                }}
              />
              <Button
                text={t('pedidos.carga.asistente.attach')}
                stylingMode="outlined"
                disabled={isSubmitting || pendingImages.length >= cargaAsistenteMaxImages}
                elementAttr={{ 'data-testid': 'cargaAsistenteIaAttach' }}
                onClick={handleAttachClick}
              />
              <Button
                text={t('pedidos.carga.asistente.config')}
                stylingMode="text"
                elementAttr={{ 'data-testid': 'cargaAsistenteIaConfig' }}
                onClick={() => {
                  navigate('/preferences');
                }}
              />
            </div>
          </div>

          <input
            id={fileInputId}
            ref={fileInputRef}
            type="file"
            accept="image/*"
            multiple
            hidden
            data-testid="cargaAsistenteIaFileInput"
            onChange={(event) => {
              void handleFilesSelected(event.target.files);
              event.target.value = '';
            }}
          />
        </div>
      ) : null}
    </section>
  );
}
