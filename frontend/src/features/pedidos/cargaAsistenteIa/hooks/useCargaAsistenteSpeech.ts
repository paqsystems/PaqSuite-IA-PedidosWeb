import { useCallback, useEffect, useRef, useState } from 'react';

type SpeechRecognitionLike = {
  lang: string;
  continuous: boolean;
  interimResults: boolean;
  onresult: ((event: { results: ArrayLike<{ 0: { transcript: string } }> }) => void) | null;
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
  onTranscript: (text: string) => void;
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

export function useCargaAsistenteSpeech({
  onTranscript,
  onError,
  lang = 'es-AR',
}: UseCargaAsistenteSpeechOptions) {
  const [isListening, setIsListening] = useState(false);
  const [blockReason, setBlockReason] = useState<CargaAsistenteSpeechBlockReason>(() =>
    resolveSpeechBlockReason(),
  );
  const recognitionRef = useRef<SpeechRecognitionLike | null>(null);
  const onTranscriptRef = useRef(onTranscript);
  const onErrorRef = useRef(onError);

  useEffect(() => {
    onTranscriptRef.current = onTranscript;
  }, [onTranscript]);

  useEffect(() => {
    onErrorRef.current = onError;
  }, [onError]);

  useEffect(() => {
    setBlockReason(resolveSpeechBlockReason());
  }, []);

  useEffect(() => {
    return () => {
      recognitionRef.current?.stop();
      recognitionRef.current = null;
    };
  }, []);

  const stop = useCallback(() => {
    recognitionRef.current?.stop();
    recognitionRef.current = null;
    setIsListening(false);
  }, []);

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
      recognitionRef.current?.stop();
      const recognition = new Recognition();
      recognition.lang = lang;
      recognition.continuous = false;
      recognition.interimResults = false;

      recognition.onresult = (event) => {
        const first = event.results[0]?.[0]?.transcript;
        const text = String(first ?? '').trim();
        if (text !== '') {
          onTranscriptRef.current(text);
        }
      };

      recognition.onerror = (event) => {
        const errorCode = String(event.error ?? '');
        if (errorCode === 'not-allowed' || errorCode === 'service-not-allowed') {
          setBlockReason('denied');
          onErrorRef.current?.('denied');
        } else {
          onErrorRef.current?.('error');
        }
        setIsListening(false);
        recognitionRef.current = null;
      };

      recognition.onend = () => {
        setIsListening(false);
        recognitionRef.current = null;
      };

      recognitionRef.current = recognition;
      recognition.start();
      setIsListening(true);
      setBlockReason(null);
    } catch {
      onErrorRef.current?.('error');
      setIsListening(false);
    }
  }, [lang]);

  return {
    isListening,
    start,
    stop,
    supported: blockReason === null || blockReason === 'denied',
    blockReason,
  };
}
