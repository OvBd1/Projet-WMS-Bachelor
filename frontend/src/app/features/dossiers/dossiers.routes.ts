import { Routes } from '@angular/router';

export const dossiersRoutes: Routes = [
  {
    path: '',
    loadComponent: () => import('./dossiers-list/dossiers-list').then(m => m.DossiersListComponent)
  }
];
