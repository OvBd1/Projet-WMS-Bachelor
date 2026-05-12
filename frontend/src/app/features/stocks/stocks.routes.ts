import { Routes } from '@angular/router';

export const stocksRoutes: Routes = [
  { path: '', loadComponent: () => import('./stocks-list/stocks-list').then(m => m.StocksListComponent) }
];
