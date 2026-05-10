import { Routes } from '@angular/router';

export const emplacementsRoutes: Routes = [
  { path: '', loadComponent: () => import('./emplacements-list/emplacements-list').then(m => m.EmplacementsListComponent) }
];
