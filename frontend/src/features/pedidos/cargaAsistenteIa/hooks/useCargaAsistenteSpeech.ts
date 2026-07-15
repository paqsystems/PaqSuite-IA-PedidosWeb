import { useCallback, useEffect, useRef, useState } from 'react';

type SpeechRecognitionResultLike = {
  isFinal: boolean;
  0: { transcript: string };
};

type SpeechRecognitionEventLike = {
  resultIndex: number;
  results: ArrayLike<SpeechRecognitionResultLike> & { length: number };
};

type SpeechRecognitionLike = {
  lang: string;
  continuous: boolean;
  interimResults: boolean;
  onresult: ((event: SpeechRecognitionEventLike) => void) | null;
  onerror: ((event: { error?: string }) => void) | null;
  onend: (() => void) | null;
  start: () => void;
  stop: () => void;
};

type SpeechRecognitionConstructor = new () => SpeechRecognitionLike;

export type CargaAsistenteSpeechBlockReason =
  | 'unsupported'
  | 'insecureContext'
  | 'denied'
  | 'error'
  | null;

type UseCargaAsistenteSpeechOptions = {
  /** Se invoca al detener el dictado (manual o error) con el texto final acumulado. */
  onTranscript: (text: string) => void;
  /** Texto parcial mientras escucha (finales + interim) para mostrar en el composer. */
  onListeningTextChange?: (text: string) => void;
  onError?: (reason: Exclude<CargaAsistenteSpeechBlockReason, null>) => void;
  lang?: string;
};

function resolveSpeechRecognitionConstructor(): SpeechRecognitionConstructor | null {
  if (typeof window === 'undefined') {
    return null;
  }

  const speechWindow = window as Window & {
    SpeechRecognition?: SpeechRecognitionConstructor;
    webkitSpeechRecognition?: SpeechRecognitionConstructor;
  };

  return speechWindow.SpeechRecognition ?? speechWindow.webkitSpeechRecognition ?? null;
}

function resolveSpeechBlockReason(): CargaAsistenteSpeechBlockReason {
  if (typeof window === 'undefined') {
    return 'unsupported';
  }

  // Chrome/Edge ocultan SpeechRecognition fuera de contexto seguro (salvo localhost).
  if (!window.isSecureContext) {
    return 'insecureContext';
  }

  if (resolveSpeechRecognitionConstructor() === null) {
    return 'unsupported';
  }

  return null;
}

function joinSpeechParts(parts: string[]): string {
  return parts
    .map((part) => part.trim())
    .filter((part) => part !== '')
    .join(' ')
    .replace(/\s+/g, ' ')
    .trim();
}

export function useCargaAsistenteSpeech({
  onTranscript,
  onListeningTextChange,
  onError,
  lang = 'es-AR',
}: UseCargaAsistenteSpeechOptions) {
  const [isListening, setIsListening] = useState(false);
  const [blockReason, setBlockReason] = useState<CargaAsistenteSpeechBlockReason>(() =>
    resolveSpeechBlockReason(),
  );
  const recognitionRef = useRef<SpeechRecognitionLike | null>(null);
  const wantListeningRef = useRef(false);
  const sessionActiveRef = useRef(false);
  const discardNextEndRef = useRef(false);
  const finalPartsRef = useRef<string[]>([]);
  const onTranscriptRef = useRef(onTranscript);
  const onListeningTextChangeRef = useRef(onListeningTextChange);
  const onErrorRef = useRef(onError);

  useEffect(() => {
    onTranscriptRef.current = onTranscript;
  }, [onTranscript]);

  useEffect(() => {
    onListeningTextChangeRef.current = onListeningTextChange;
  }, [onListeningTextChange]);

  useEffect(() => {
    onErrorRef.current = onError;
  }, [onError]);

  useEffect(() => {
    setBlockReason(resolveSpeechBlockReason());
  }, []);

  const emitListeningText = useCallback((interim = '') => {
    const finals = joinSpeechParts(finalPartsRef.current);
    const combined = joinSpeechParts([finals, interim]);
    onListeningTextChangeRef.current?.(combined);
  }, []);

  const finishListening = useCallback((publishTranscript: boolean) => {
    if (!sessionActiveRef.current) {
      return;
    }

    sessionActiveRef.current = false;
    wantListeningRef.current = false;
    const text = joinSpeechParts(finalPartsRef.current);
    finalPartsRef.current = [];
    recognitionRef.current = null;
    setIsListening(false);

    if (publishTranscript && text !== '') {
      onTranscriptRef.current(text);
    }
  }, []);

  useEffect(() => {
    return () => {
      discardNextEndRef.current = true;
      wantListeningRef.current = false;
      sessionActiveRef.current = false;
      recognitionRef.current?.stop();
      recognitionRef.current = null;
    };
  }, []);

  const stop = useCallback(() => {
    if (!sessionActiveRef.current) {
      return;
    }

    wantListeningRef.current = false;
    const recognition = recognitionRef.current;

    if (recognition) {
      try {
        recognition.stop();
      } catch {
        finishListening(true);
      }
      return;
    }

    finishListening(true);
  }, [finishListening]);

  const start = useCallback(() => {
    const reason = resolveSpeechBlockReason();
    setBlockReason(reason);

    if (reason === 'insecureContext' || reason === 'unsupported') {
      onErrorRef.current?.(reason);
      return;
    }

    const Recognition = resolveSpeechRecognitionConstructor();
    if (!Recognition) {
      setBlockReason('unsupported');
      onErrorRef.current?.('unsupported');
      return;
    }

    try {
      if (recognitionRef.current) {
        discardNextEndRef.current = true;
        wantListeningRef.current = false;
        try {
          recognitionRef.current.stop();
        } catch {
          // ignore
        }
        recognitionRef.current = null;
      }

      finalPartsRef.current = [];
      emitListeningText('');
      discardNextEndRef.current = false;
      sessionActiveRef.current = true;
      wantListeningRef.current = true;

      const recognition = new Recognition();
      recognition.lang = lang;
      recognition.continuous = true;
      recognition.interimResults = true;

      recognition.onresult = (event) => {
        if (!sessionActiveRef.current) {
          return;
        }

        let interim = '';

        for (let index = event.resultIndex; index < event.results.length; index += 1) {
          const result = event.results[index];
          const transcript = String(result?.[0]?.transcript ?? '').trim();
          if (transcript === '') {
            continue;
          }

          if (result.isFinal) {
            finalPartsRef.current.push(transcript);
          } else {
            interim = interim === '' ? transcript : `${interim} ${transcript}`;
          }
        }

        emitListeningText(interim);
      };

      recognition.onerror = (event) => {
        const errorCode = String(event.error ?? '');

        // En modo continuo Chrome suele emitir "no-speech" / "aborted" sin ser fallo fatal.
        if (errorCode === 'no-speech' || errorCode === 'aborted') {
          return;
        }

        if (errorCode === 'not-allowed' || errorCode === 'service-not-allowed') {
          setBlockReason('denied');
          onErrorRef.current?.('denied');
          finishListening(false);
          return;
        }

        onErrorRef.current?.('error');
        finishListening(true);
      };

      recognition.onend = () => {
        if (discardNextEndRef.current) {
          discardNextEndRef.current = false;
          return;
        }

        if (wantListeningRef.current && sessionActiveRef.current) {
          try {
            recognition.start();
            return;
          } catch {
            finishListening(true);
            return;
          }
        }

        finishListening(true);
      };

      recognitionRef.current = recognition;
      recognition.start();
      setIsListening(true);
      setBlockReason(null);
    } catch {
      onErrorRef.current?.('error');
      finishListening(false);
    }
  }, [emitListeningText, finishListening, lang]);

  return {
    isListening,
    start,
    stop,
    supported: blockReason === null || blockReason === 'denied',
    blockReason,
  };
}
