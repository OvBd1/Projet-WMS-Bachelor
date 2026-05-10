import { Routes } from '@angular/router';

export const typesEmplacementRoutes: Routes = [
  {
    path: '',
    loadComponent: () =>
      import('./types-emplacement-list/types-emplacement-list').then(
        m => m.TypesEmplacementListComponent
      )
  }
];
