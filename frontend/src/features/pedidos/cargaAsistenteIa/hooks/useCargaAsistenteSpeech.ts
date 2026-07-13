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

type UseCargaAsistenteSpeechOptions = {
  onTranscript: (text: string) => void;
  onError?: (reason: 'unsupported' | 'denied' | 'error') => void;
  lang?: string;
};

function resolveSpeechRecognitionConstructor(): SpeechRecognitionConstructor | null {
  const speechWindow = window as Window & {
    SpeechRecognition?: SpeechRecognitionConstructor;
    webkitSpeechRecognition?: SpeechRecognitionConstructor;
  };

  return speechWindow.SpeechRecognition ?? speechWindow.webkitSpeechRecognition ?? null;
}

export function useCargaAsistenteSpeech({
  onTranscript,
  onError,
  lang = 'es-AR',
}: UseCargaAsistenteSpeechOptions) {
  const [isListening, setIsListening] = useState(false);
  const recognitionRef = useRef<SpeechRecognitionLike | null>(null);
  const onTranscriptRef = useRef(onTranscript);
  const onErrorRef = useRef(onError);
  const supported = typeof window !== 'undefined' && resolveSpeechRecognitionConstructor() !== null;

  useEffect(() => {
    onTranscriptRef.current = onTranscript;
  }, [onTranscript]);

  useEffect(() => {
    onErrorRef.current = onError;
  }, [onError]);

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
    const Recognition = resolveSpeechRecognitionConstructor();

    if (!Recognition) {
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
    } catch {
      onErrorRef.current?.('error');
      setIsListening(false);
    }
  }, [lang]);

  return {
    isListening,
    start,
    stop,
    supported,
  };
}
