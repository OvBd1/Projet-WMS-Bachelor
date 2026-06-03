import { Routes } from '@angular/router';

export const commandesRoutes: Routes = [
  { path: '', loadComponent: () => import('./commandes-list/commandes-list').then(m => m.CommandesListComponent) },
  { path: ':id/edit', loadComponent: () => import('./commande-edit/commande-edit').then(m => m.CommandeEditComponent) }
];
