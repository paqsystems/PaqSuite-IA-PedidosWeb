import { useCallback, useEffect, useRef, useState } from 'react';

export function useAvatarMenu() {
  const [isOpen, setIsOpen] = useState(false);
  const containerRef = useRef<HTMLDivElement | null>(null);

  const closeMenu = useCallback(() => {
    setIsOpen(false);
  }, []);

  const toggleMenu = useCallback(() => {
    setIsOpen((currentValue) => !currentValue);
  }, []);

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    function handlePointerDown(event: MouseEvent) {
      const container = containerRef.current;

      if (container !== null && !container.contains(event.target as Node)) {
        closeMenu();
      }
    }

    function handleEscapeKey(event: KeyboardEvent) {
      if (event.key === 'Escape') {
        closeMenu();
      }
    }

    document.addEventListener('mousedown', handlePointerDown);
    document.addEventListener('keydown', handleEscapeKey);

    return () => {
      document.removeEventListener('mousedown', handlePointerDown);
      document.removeEventListener('keydown', handleEscapeKey);
    };
  }, [closeMenu, isOpen]);

  return {
    containerRef,
    isOpen,
    toggleMenu,
    closeMenu,
  };
}
