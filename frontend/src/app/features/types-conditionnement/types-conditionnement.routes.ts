import { Routes } from '@angular/router';

export const typesConditionnementRoutes: Routes = [
  {
    path: '',
    loadComponent: () =>
      import('./types-conditionnement-list/types-conditionnement-list').then(
        m => m.TypesConditionnementListComponent
      )
  }
];
