import { lazy, Suspense, type ReactElement } from 'react';

const RolesAdminPage = lazy(() =>
  import('../features/admin/security/roles/RolesAdminPage').then((module) => ({
    default: module.RolesAdminPage,
  })),
);

const RoleAttributesPage = lazy(() =>
  import('../features/admin/security/roles/RoleAttributesPage').then((module) => ({
    default: module.RoleAttributesPage,
  })),
);

const PermisosAdminPage = lazy(() =>
  import('../features/admin/security/permisos/PermisosAdminPage').then((module) => ({
    default: module.PermisosAdminPage,
  })),
);

export type AdminSecurityRoutePath = '/admin/roles' | '/admin/roles/:rolId/atributos' | '/admin/permisos';

export type AdminSecurityRoute = {
  path: AdminSecurityRoutePath;
  element: ReactElement;
};

function withSuspense(element: ReactElement, testId: string): ReactElement {
  return (
    <Suspense fallback={<section data-testid={testId} />}>
      {element}
    </Suspense>
  );
}

export const adminSecurityRoutes: AdminSecurityRoute[] = [
  {
    path: '/admin/roles',
    element: withSuspense(<RolesAdminPage />, 'page-loading-admin-roles'),
  },
  {
    path: '/admin/roles/:rolId/atributos',
    element: withSuspense(<RoleAttributesPage />, 'page-loading-admin-role-atributos'),
  },
  {
    path: '/admin/permisos',
    element: withSuspense(<PermisosAdminPage />, 'page-loading-admin-permisos'),
  },
];
