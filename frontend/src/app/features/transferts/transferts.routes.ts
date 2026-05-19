import { Routes } from '@angular/router';

export const transfertsRoutes: Routes = [
  { path: '', loadComponent: () => import('./transferts-list/transferts-list').then(m => m.TransfertsListComponent) }
];
