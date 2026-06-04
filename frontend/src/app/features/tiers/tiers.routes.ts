import { Routes } from '@angular/router';

export const tiersRoutes: Routes = [
  {
    path: '',
    loadComponent: () => import('./tiers-list/tiers-list').then(m => m.TiersListComponent)
  }
];
