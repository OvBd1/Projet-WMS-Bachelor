import { Routes } from '@angular/router';

export const articlesRoutes: Routes = [
  { path: '', loadComponent: () => import('./articles-list/articles-list').then(m => m.ArticlesListComponent) }
];
