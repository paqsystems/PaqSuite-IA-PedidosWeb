import { useEffect, useState } from 'react';
import { fetchUserMenu, type MenuNode } from '../menu/menuApi';

export function useUserMenu(isAuthenticated: boolean) {
  const [menuItems, setMenuItems] = useState<MenuNode[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [errorKey, setErrorKey] = useState<string | null>(null);

  useEffect(() => {
    if (!isAuthenticated) {
      setMenuItems([]);
      return;
    }

    let isCancelled = false;

    async function loadMenu() {
      setIsLoading(true);
      setErrorKey(null);

      try {
        const items = await fetchUserMenu();
        if (!isCancelled) {
          setMenuItems(items);
        }
      } catch (error) {
        if (!isCancelled) {
          setMenuItems([]);
          setErrorKey(error instanceof Error ? error.message : 'menu.loadFailed');
        }
      } finally {
        if (!isCancelled) {
          setIsLoading(false);
        }
      }
    }

    loadMenu();

    return () => {
      isCancelled = true;
    };
  }, [isAuthenticated]);

  return { menuItems, isLoading, errorKey };
}
