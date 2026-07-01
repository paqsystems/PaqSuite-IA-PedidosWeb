import type { ReactNode } from 'react';
import Button from 'devextreme-react/button';
import List from 'devextreme-react/list';
import './consultaKardexList.css';

export type ConsultaKardexListProps<TItem> = {
  items: TItem[];
  keyExpr: keyof TItem | ((item: TItem) => string);
  renderCard: (item: TItem) => ReactNode;
  onItemClick: (item: TItem) => void;
  isLoading: boolean;
  hasMore: boolean;
  onLoadMore: () => void;
  onRefresh: () => void;
  refreshLabel: string;
  loadMoreLabel: string;
  listTestId?: string;
  emptyMessage?: ReactNode;
  hideToolbar?: boolean;
  pageScroll?: boolean;
};

function resolveItemKey<TItem>(item: TItem, keyExpr: ConsultaKardexListProps<TItem>['keyExpr']): string {
  if (typeof keyExpr === 'function') {
    return keyExpr(item);
  }

  return String(item[keyExpr]);
}

export function ConsultaKardexList<TItem>({
  items,
  keyExpr,
  renderCard,
  onItemClick,
  isLoading,
  hasMore,
  onLoadMore,
  onRefresh,
  refreshLabel,
  loadMoreLabel,
  listTestId = 'consultaKardexList',
  emptyMessage,
  hideToolbar = false,
  pageScroll = true,
}: ConsultaKardexListProps<TItem>) {
  return (
    <section
      className={`consultaKardexList${pageScroll ? ' consultaKardexList--pageScroll' : ''}`}
    >
      {!hideToolbar && (
        <div className="consultaKardexList__toolbar">
          <Button
            icon="refresh"
            stylingMode="text"
            text={refreshLabel}
            disabled={isLoading}
            elementAttr={{ 'data-testid': 'gridRefresh' }}
            onClick={onRefresh}
          />
        </div>
      )}

      {pageScroll ? (
        items.length === 0 && !isLoading ? (
          emptyMessage ? (
            <p className="consultaKardexList__empty" data-testid={listTestId}>
              {emptyMessage}
            </p>
          ) : null
        ) : (
          <ul className="consultaKardexList__items" data-testid={listTestId}>
            {items.map((item) => {
              const itemKey = resolveItemKey(item, keyExpr);

              return (
                <li key={itemKey} className="consultaKardexList__item">
                  <button
                    type="button"
                    className="consultaKardexList__cardButton"
                    onClick={() => {
                      onItemClick(item);
                    }}
                  >
                    {renderCard(item)}
                  </button>
                </li>
              );
            })}
          </ul>
        )
      ) : (
        <List
          dataSource={items}
          keyExpr={(item: TItem) => resolveItemKey(item, keyExpr)}
          hoverStateEnabled
          focusStateEnabled={false}
          activeStateEnabled={false}
          scrollingEnabled
          useNativeScrolling
          className="consultaKardexList__dxList"
          noDataText={emptyMessage ? String(emptyMessage) : undefined}
          elementAttr={{ 'data-testid': listTestId }}
          itemRender={(item: TItem) => (
            <button
              type="button"
              className="consultaKardexList__cardButton"
              onClick={() => {
                onItemClick(item);
              }}
            >
              {renderCard(item)}
            </button>
          )}
        />
      )}

      {hasMore && (
        <div className="consultaKardexList__loadMore">
          <Button
            text={loadMoreLabel}
            stylingMode="outlined"
            disabled={isLoading}
            elementAttr={{ 'data-testid': 'consultaKardexLoadMore' }}
            onClick={onLoadMore}
          />
        </div>
      )}
    </section>
  );
}
