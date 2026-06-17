import { Routes } from '@angular/router';

export const utilisateursRoutes: Routes = [
  {
    path: '',
    loadComponent: () => import('./utilisateurs-list/utilisateurs-list').then(m => m.UtilisateursListComponent)
  }
];
