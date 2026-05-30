import { useEffect, useMemo, useRef } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import TreeView from 'devextreme-react/tree-view';
import type dxTreeView from 'devextreme/ui/tree_view';
import type { MenuNode } from '../menuApi';
import type { MenuDisplayMode } from '../utils/menuPresentationStorage';
import {
  collectAllGroupMenuKeys,
  collectAncestorMenuKeys,
  findActiveMenuKey,
  flattenOperationalMenu,
} from '../utils/flattenOperationalMenu';

type TreeMenuItem = {
  menuKey: string;
  text: string;
  routePath: string | null;
  nodeType: MenuNode['nodeType'];
  items?: TreeMenuItem[];
};

type MenuSidebarTreeProps = {
  menuItems: MenuNode[];
  menuTreeExpanded: boolean;
  menuDisplayMode: MenuDisplayMode;
};

function resolveMenuLabel(
  node: Pick<MenuNode, 'labelKey' | 'text'>,
  translate: (labelKey: string) => string,
): string {
  if (node.labelKey.trim() !== '') {
    const translated = translate(node.labelKey);

    if (translated !== node.labelKey) {
      return translated;
    }
  }

  return node.text;
}

function mapMenuNodeToTreeItem(
  node: MenuNode,
  translate: (labelKey: string) => string,
): TreeMenuItem {
  return {
    menuKey: node.menuKey,
    text: resolveMenuLabel(node, translate),
    routePath: node.routePath,
    nodeType: node.nodeType,
    items:
      node.children.length > 0
        ? node.children.map((childNode) => mapMenuNodeToTreeItem(childNode, translate))
        : undefined,
  };
}

export function MenuSidebarTree({
  menuItems,
  menuTreeExpanded,
  menuDisplayMode,
}: MenuSidebarTreeProps) {
  const navigate = useNavigate();
  const location = useLocation();
  const { t, i18n } = useTranslation();
  const treeViewRef = useRef<dxTreeView | null>(null);

  const treeItems = useMemo(() => {
    const translate = (labelKey: string) => t(labelKey);

    if (menuDisplayMode === 'operationalOnly') {
      return flattenOperationalMenu(menuItems).map((node: MenuNode) => ({
        menuKey: node.menuKey,
        text: resolveMenuLabel(node, translate),
        routePath: node.routePath,
        nodeType: node.nodeType,
      }));
    }

    return menuItems.map((node) => mapMenuNodeToTreeItem(node, translate));
  }, [i18n.language, menuDisplayMode, menuItems, t]);

  const activeMenuKey = useMemo(
    () => findActiveMenuKey(menuItems, location.pathname),
    [location.pathname, menuItems],
  );

  const expandedKeys = useMemo((): string[] => {
    if (menuDisplayMode === 'operationalOnly') {
      return [];
    }

    if (!menuTreeExpanded) {
      return collectAncestorMenuKeys(menuItems, location.pathname);
    }

    return collectAllGroupMenuKeys(menuItems);
  }, [location.pathname, menuDisplayMode, menuItems, menuTreeExpanded]);

  useEffect(() => {
    const instance = treeViewRef.current;

    if (!instance) {
      return;
    }

    if (menuDisplayMode === 'operationalOnly') {
      return;
    }

    if (menuTreeExpanded) {
      instance.expandAll();
      return;
    }

    instance.collapseAll();

    if (expandedKeys.length > 0) {
      expandedKeys.forEach((menuKey: string) => {
        instance.expandItem(menuKey);
      });
    }
  }, [expandedKeys, menuDisplayMode, menuTreeExpanded, treeItems]);

  function handleItemClick(event: { itemData?: TreeMenuItem }) {
    const itemData = event.itemData;

    if (!itemData || itemData.nodeType !== 'process' || !itemData.routePath) {
      return;
    }

    navigate(itemData.routePath);
  }

  return (
    <div data-testid="menuSidebarList">
      <TreeView
        ref={(component) => {
          treeViewRef.current = component?.instance() ?? null;
        }}
        dataSource={treeItems}
        dataStructure="tree"
        keyExpr="menuKey"
        displayExpr="text"
        itemsExpr="items"
        focusStateEnabled={false}
        selectionMode="single"
        selectByClick={false}
        selectedItemKeys={activeMenuKey ? [activeMenuKey] : []}
        expandEvent="click"
        onItemClick={handleItemClick}
        itemRender={(item) => (
          <span data-testid={`menuSidebarItem-${item.menuKey}`}>{item.text}</span>
        )}
        className="menuSidebarTree"
      />
    </div>
  );
}
