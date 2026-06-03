import { Routes } from '@angular/router';

export const receptionsRoutes: Routes = [
  { path: '', loadComponent: () => import('./receptions-list/receptions-list').then(m => m.ReceptionsListComponent) },
  { path: ':id/edit', loadComponent: () => import('./reception-edit/reception-edit').then(m => m.ReceptionEditComponent) },
  { path: ':id', loadComponent: () => import('./reception-detail/reception-detail').then(m => m.ReceptionDetailComponent) }
];
