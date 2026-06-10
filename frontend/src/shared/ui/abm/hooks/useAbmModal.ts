import { useCallback, useState } from 'react';
import type { AbmModalMode } from '../types/abmTypes';

export function useAbmModal<TRecord extends Record<string, unknown>>() {
  const [isOpen, setIsOpen] = useState(false);
  const [mode, setMode] = useState<AbmModalMode>('create');
  const [record, setRecord] = useState<TRecord | null>(null);

  const openCreate = useCallback(() => {
    setMode('create');
    setRecord(null);
    setIsOpen(true);
  }, []);

  const openEdit = useCallback((row: TRecord) => {
    setMode('edit');
    setRecord(row);
    setIsOpen(true);
  }, []);

  const openView = useCallback((row: TRecord) => {
    setMode('view');
    setRecord(row);
    setIsOpen(true);
  }, []);

  const close = useCallback(() => {
    setIsOpen(false);
    setRecord(null);
  }, []);

  return {
    isOpen,
    mode,
    record,
    openCreate,
    openEdit,
    openView,
    close,
  };
}
