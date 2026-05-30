import { useEffect, useMemo, useRef } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
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

function mapMenuNodeToTreeItem(node: MenuNode): TreeMenuItem {
  return {
    menuKey: node.menuKey,
    text: node.text,
    routePath: node.routePath,
    nodeType: node.nodeType,
    items:
      node.children.length > 0
        ? node.children.map(mapMenuNodeToTreeItem)
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
  const treeViewRef = useRef<dxTreeView | null>(null);

  const treeItems = useMemo(() => {
    if (menuDisplayMode === 'operationalOnly') {
      return flattenOperationalMenu(menuItems).map((node: MenuNode) => ({
        menuKey: node.menuKey,
        text: node.text,
        routePath: node.routePath,
        nodeType: node.nodeType,
      }));
    }

    return menuItems.map(mapMenuNodeToTreeItem);
  }, [menuDisplayMode, menuItems]);

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
