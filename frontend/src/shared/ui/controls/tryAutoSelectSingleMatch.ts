import type { InputEvent } from 'devextreme/ui/select_box';

type DataSourceLike = {
  load: () => Promise<unknown>;
  items: () => unknown[];
};

type SelectBoxComponentLike = {
  getDataSource: () => DataSourceLike | null | undefined;
  option: (name: string, value?: unknown) => unknown;
};

export async function loadFilteredSelectBoxItems(
  component: SelectBoxComponentLike,
): Promise<unknown[]> {
  const dataSource = component.getDataSource();

  if (!dataSource) {
    return [];
  }

  const existingItems = dataSource.items() ?? [];
  if (existingItems.length > 0) {
    return existingItems;
  }

  await dataSource.load();

  return dataSource.items() ?? [];
}

export async function tryAutoSelectSingleMatch(
  component: SelectBoxComponentLike,
  valueExpr: string,
): Promise<unknown | null> {
  const items = await loadFilteredSelectBoxItems(component);

  if (items.length !== 1) {
    return null;
  }

  const item = items[0] as Record<string, unknown>;
  const value = item[valueExpr];

  if (value === undefined || value === null) {
    return null;
  }

  return value;
}

function resolveSelectBoxSearchText(component: SelectBoxComponentLike): string {
  const searchValue = component.option('searchValue');
  if (typeof searchValue === 'string') {
    return searchValue.trim();
  }

  const text = component.option('text');
  return typeof text === 'string' ? text.trim() : '';
}

const autoMatchDebounceTimers = new WeakMap<
  SelectBoxComponentLike,
  ReturnType<typeof setTimeout>
>();

export function createSelectBoxAutoMatchInputHandler(options: {
  enabled: boolean;
  valueExpr: string;
  minSearchLength?: number;
  searchTimeout?: number;
  userOnInput?: (event: InputEvent) => void;
}): (event: InputEvent) => void {
  return (event) => {
    options.userOnInput?.(event);

    if (!options.enabled) {
      return;
    }

    const minSearchLength = options.minSearchLength ?? 0;
    if (resolveSelectBoxSearchText(event.component).length < minSearchLength) {
      return;
    }

    const pendingTimer = autoMatchDebounceTimers.get(event.component);
    if (pendingTimer) {
      clearTimeout(pendingTimer);
    }

    const debounceMs = (options.searchTimeout ?? 350) + 50;
    autoMatchDebounceTimers.set(
      event.component,
      setTimeout(() => {
        void tryAutoSelectSingleMatch(event.component, options.valueExpr).then((value) => {
          if (value === null) {
            return;
          }

          if (event.component.option('value') !== value) {
            event.component.option('value', value);
          }
        });
      }, debounceMs),
    );
  };
}
