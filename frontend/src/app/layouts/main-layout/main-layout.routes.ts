import { Routes } from '@angular/router';
import { MainLayoutComponent } from './main-layout';

export const mainRoutes: Routes = [
  {
    path: '',
    component: MainLayoutComponent,
    children: [
      { path: '', redirectTo: 'dashboard', pathMatch: 'full' },
      {
        path: 'dashboard',
        loadComponent: () => import('../../features/dashboard/dashboard').then(m => m.DashboardComponent)
      },
      {
        path: 'articles',
        loadChildren: () => import('../../features/articles/articles.routes').then(m => m.articlesRoutes)
      },
      {
        path: 'types-conditionnement',
        loadChildren: () => import('../../features/types-conditionnement/types-conditionnement.routes').then(m => m.typesConditionnementRoutes)
      },
      {
        path: 'stocks',
        loadChildren: () => import('../../features/stocks/stocks.routes').then(m => m.stocksRoutes)
      },
      {
        path: 'emplacements',
        loadChildren: () => import('../../features/emplacements/emplacements.routes').then(m => m.emplacementsRoutes)
      },
      {
        path: 'types-emplacement',
        loadChildren: () => import('../../features/types-emplacement/types-emplacement.routes').then(m => m.typesEmplacementRoutes)
      },
      {
        path: 'receptions',
        loadChildren: () => import('../../features/receptions/receptions.routes').then(m => m.receptionsRoutes)
      },
      {
        path: 'commandes',
        loadChildren: () => import('../../features/commandes/commandes.routes').then(m => m.commandesRoutes)
      },
      {
        path: 'transferts',
        loadChildren: () => import('../../features/transferts/transferts.routes').then(m => m.transfertsRoutes)
      },
      {
        path: 'tiers',
        loadChildren: () => import('../../features/tiers/tiers.routes').then(m => m.tiersRoutes)
      }
    ]
  }
];
