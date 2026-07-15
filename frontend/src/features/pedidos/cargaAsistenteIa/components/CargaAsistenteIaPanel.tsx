import { useCallback, useId, useLayoutEffect, useRef, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import Button from 'devextreme-react/button';
import TextBox from 'devextreme-react/text-box';
import { isDevExtremeUserChange } from '../../../../shared/ui/devextremeUserChange';
import { SelectBoxDx } from '../../../../shared/ui/controls/SelectBoxDx';
import { ApiClientError } from '../../../../shared/http/client';
import { postCargaAsistenteTurn } from '../api/postCargaAsistenteTurn';
import { useCargaAsistenteLlmCredential } from '../hooks/useCargaAsistenteLlmCredential';
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
  type CargaAsistenteUpdateRenglonPayload,
} from '../utils/applyCargaAsistenteActions';
import {
  cargaAsistenteMaxImageBytes,
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

type PendingAttachment = {
  id: string;
  fileName: string;
  previewUrl: string;
  payload: CargaAsistenteImagePayload;
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

function createAttachmentId(): string {
  return `carga-adjunto-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
}

function aliasAsistenteErrorKey(key: string): string {
  const aliases: Record<string, string> = {
    'chatAssistant.imageTooLarge': 'chatAssistant.images.tooLarge',
    'chatAssistant.imagesTooMany': 'chatAssistant.images.tooMany',
    'chatAssistant.imageInvalidFormat': 'chatAssistant.images.invalidFormat',
    'chatAssistant.imageInvalidPayload': 'chatAssistant.images.invalidFormat',
    'chatAssistant.visionUnsupported': 'pedidos.carga.asistente.visionUnsupported',
  };

  return aliases[key] ?? key;
}

function resolveCargaAsistenteErrorMessage(
  error: unknown,
  translate: (key: string, options?: Record<string, unknown>) => string,
): string {
  if (!(error instanceof ApiClientError)) {
    return translate('pedidos.carga.asistente.errorGeneric');
  }

  const key = error.respuestaKey;

  if (key.startsWith('pedidos.carga.asistente.') || key.startsWith('chatAssistant.')) {
    return translate(aliasAsistenteErrorKey(key));
  }

  if (key === 'validation.failed') {
    const fields = (error.resultado as { fields?: Record<string, string[]> } | undefined)?.fields;
    const imageErrors = fields?.images ?? [];

    for (const message of imageErrors) {
      if (
        typeof message === 'string'
        && (message.startsWith('chatAssistant.') || message.startsWith('pedidos.carga.asistente.'))
      ) {
        return translate(aliasAsistenteErrorKey(message));
      }
    }
  }

  return translate('pedidos.carga.asistente.errorGeneric');
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

  if (text.includes('\n')) {
    return text
      .split('\n')
      .map((line) => resolveAsistenteReplyText(line, translate, searchedQ))
      .join('\n');
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
  const listeningPrefixRef = useRef('');
  const {
    isLoading: isLoadingLlmCredentials,
    operationalConfigurations,
    selectedCredentialId,
    selectCredential,
  } = useCargaAsistenteLlmCredential();

  const [expanded, setExpanded] = useState(false);
  const [inputValue, setInputValue] = useState('');
  const [messages, setMessages] = useState<CargaAsistenteIaMessage[]>([]);
  const [pendingChoice, setPendingChoice] = useState<CargaAsistentePendingChoice>(null);
  const [pendingAttachments, setPendingAttachments] = useState<PendingAttachment[]>([]);
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

  const clearPendingAttachments = useCallback((attachments: PendingAttachment[]) => {
    for (const attachment of attachments) {
      URL.revokeObjectURL(attachment.previewUrl);
    }
  }, []);

  const removePendingAttachment = useCallback((attachmentId: string) => {
    setPendingAttachments((current) => {
      const target = current.find((item) => item.id === attachmentId);
      if (target) {
        URL.revokeObjectURL(target.previewUrl);
      }

      return current.filter((item) => item.id !== attachmentId);
    });
    setStatusMessage(null);
  }, []);

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
  }, [expanded, messages, statusMessage, isSubmitting, pendingAttachments]);

  const handleSpeechError = useCallback(
    (reason: 'unsupported' | 'insecureContext' | 'denied' | 'error') => {
      if (reason === 'unsupported') {
        setStatusMessage(t('pedidos.carga.asistente.micUnsupported'));
        return;
      }

      if (reason === 'insecureContext') {
        setStatusMessage(t('pedidos.carga.asistente.micInsecureContext'));
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
      setPendingAttachments((current) => {
        clearPendingAttachments(current);
        return [];
      });
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
          credentialId: selectedCredentialId,
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

        appendAssistantMessage(resolveCargaAsistenteErrorMessage(error, t));
      } finally {
        setIsSubmitting(false);
      }
    },
    [
      appendAssistantMessage,
      buildDraftContext,
      clearPendingAttachments,
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
      selectedCredentialId,
      t,
    ],
  );

  const handleListeningTextChange = useCallback((text: string) => {
    const prefix = listeningPrefixRef.current;
    setInputValue(prefix === '' ? text : `${prefix}${text}`);
  }, []);

  const speech = useCargaAsistenteSpeech({
    onTranscript: (text) => {
      const prefix = listeningPrefixRef.current;
      listeningPrefixRef.current = '';
      const fullMessage = `${prefix}${text}`.trim();
      void submitTurn(fullMessage, 'audio', []);
    },
    onListeningTextChange: handleListeningTextChange,
    onError: handleSpeechError,
  });

  const handleSend = () => {
    if (speech.isListening) {
      return;
    }

    const images = pendingAttachments.map((item) => item.payload);
    const modality: CargaAsistenteModality = images.length > 0 ? 'imagen' : 'texto';
    void submitTurn(inputValue, modality, images);
  };

  const handleAttachClick = () => {
    fileInputRef.current?.click();
  };

  const handleFilesSelected = async (fileList: FileList | null) => {
    if (!fileList || fileList.length === 0) {
      return;
    }

    const files = Array.from(fileList);
    if (pendingAttachments.length + files.length > cargaAsistenteMaxImages) {
      setStatusMessage(t('chatAssistant.images.tooMany'));
      return;
    }

    if (files.some((file) => !isAllowedCargaAsistenteImageFile(file))) {
      setStatusMessage(t('chatAssistant.images.invalidFormat'));
      return;
    }

    if (files.some((file) => file.size > cargaAsistenteMaxImageBytes)) {
      setStatusMessage(t('chatAssistant.images.tooLarge'));
      return;
    }

    try {
      const nextAttachments: PendingAttachment[] = [];

      for (const file of files) {
        const payload = await fileToBase64Image(file);
        nextAttachments.push({
          id: createAttachmentId(),
          fileName: payload.fileName || file.name,
          previewUrl: URL.createObjectURL(file),
          payload,
        });
      }

      setPendingAttachments((current) => [...current, ...nextAttachments]);
      setStatusMessage(null);
    } catch (error) {
      const message =
        error instanceof Error && error.message.startsWith('chatAssistant.')
          ? t(error.message)
          : t('chatAssistant.images.invalidFormat');
      setStatusMessage(message);
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

          {pendingAttachments.length > 0 ? (
            <ul
              className="cargaAsistenteIaPanel__attachments"
              data-testid="cargaAsistenteIaAttachments"
            >
              {pendingAttachments.map((attachment) => (
                <li key={attachment.id} className="cargaAsistenteIaPanel__attachmentItem">
                  <img
                    src={attachment.previewUrl}
                    alt={attachment.fileName}
                    className="cargaAsistenteIaPanel__attachmentPreview"
                  />
                  <div className="cargaAsistenteIaPanel__attachmentMeta">
                    <span className="cargaAsistenteIaPanel__attachmentName">{attachment.fileName}</span>
                    <Button
                      text={t('pedidos.carga.asistente.removeAttachment')}
                      stylingMode="text"
                      disabled={isSubmitting}
                      elementAttr={{ 'data-testid': 'cargaAsistenteIaRemoveAttachment' }}
                      onClick={() => {
                        removePendingAttachment(attachment.id);
                      }}
                    />
                  </div>
                </li>
              ))}
            </ul>
          ) : null}

          <div className="cargaAsistenteIaPanel__composer">
            <TextBox
              value={inputValue}
              valueChangeEvent="input"
              disabled={isSubmitting || speech.isListening}
              placeholder={t('pedidos.carga.asistente.placeholder')}
              onValueChanged={(event) => {
                if (!isDevExtremeUserChange(event)) {
                  return;
                }

                setInputValue(String(event.value ?? ''));
              }}
              onEnterKey={() => {
                if (!isSubmitting && !speech.isListening) {
                  handleSend();
                }
              }}
              inputAttr={{ 'data-testid': 'cargaAsistenteIaInput' }}
            />

            <div className="cargaAsistenteIaPanel__actions">
              <Button
                text={t('pedidos.carga.asistente.send')}
                type="default"
                disabled={
                  isSubmitting
                  || speech.isListening
                  || (inputValue.trim() === '' && pendingAttachments.length === 0)
                }
                elementAttr={{ 'data-testid': 'cargaAsistenteIaSend' }}
                onClick={handleSend}
              />
              <Button
                text={
                  speech.isListening
                    ? t('pedidos.carga.asistente.micStop')
                    : t('pedidos.carga.asistente.mic')
                }
                stylingMode="outlined"
                type={speech.isListening ? 'danger' : 'normal'}
                disabled={isSubmitting}
                elementAttr={{ 'data-testid': 'cargaAsistenteIaMic' }}
                onClick={() => {
                  if (speech.isListening) {
                    speech.stop();
                    return;
                  }

                  if (speech.blockReason === 'insecureContext') {
                    handleSpeechError('insecureContext');
                    return;
                  }

                  if (speech.blockReason === 'unsupported' || !speech.supported) {
                    handleSpeechError('unsupported');
                    return;
                  }

                  const current = inputValue.trim();
                  listeningPrefixRef.current = current === '' ? '' : `${current} `;
                  speech.start();
                }}
              />
              <Button
                text={t('pedidos.carga.asistente.attach')}
                stylingMode="outlined"
                disabled={
                  isSubmitting
                  || speech.isListening
                  || pendingAttachments.length >= cargaAsistenteMaxImages
                }
                elementAttr={{ 'data-testid': 'cargaAsistenteIaAttach' }}
                onClick={handleAttachClick}
              />
              <Button
                text={t('pedidos.carga.asistente.config')}
                stylingMode="text"
                disabled={speech.isListening}
                elementAttr={{ 'data-testid': 'cargaAsistenteIaConfig' }}
                onClick={() => {
                  navigate('/preferences');
                }}
              />
            </div>

            {operationalConfigurations.length > 0 ? (
              <label className="cargaAsistenteIaPanel__providerSelect">
                <span>{t('pedidos.carga.asistente.configurationLabel')}</span>
                <SelectBoxDx
                  dataSource={operationalConfigurations}
                  displayExpr="displayName"
                  valueExpr="credentialId"
                  value={selectedCredentialId}
                  searchEnabled
                  isLoading={isLoadingLlmCredentials}
                  disabled={isSubmitting || speech.isListening}
                  inputAttr={{ 'data-testid': 'cargaAsistenteIaConfigurationSelect' }}
                  elementAttr={{ 'data-testid': 'cargaAsistenteIaConfigurationSelectBox' }}
                  onValueChanged={(event) => {
                    if (!isDevExtremeUserChange(event)) {
                      return;
                    }

                    selectCredential((event.value as number | null) ?? null);
                  }}
                />
              </label>
            ) : null}
          </div>

          <input
            id={fileInputId}
            ref={fileInputRef}
            type="file"
            accept="image/png,image/jpeg,image/webp,.png,.jpg,.jpeg,.webp"
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
