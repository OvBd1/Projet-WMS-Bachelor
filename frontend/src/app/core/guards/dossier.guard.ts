import { CanActivateChildFn } from '@angular/router';
import { inject } from '@angular/core';
import { map, of } from 'rxjs';
import { AuthService } from '../services/auth.service';
import { DossierService } from '../services/dossier.service';
import { DossierContextService } from '../services/dossier-context.service';

export const dossierGuard: CanActivateChildFn = () => {
  const auth = inject(AuthService);
  const dossierContext = inject(DossierContextService);
  const dossierService = inject(DossierService);

  if (!auth.isAdmin() || dossierContext.currentDossierId()) {
    return of(true);
  }

  return dossierService.getAll().pipe(
    map(dossiers => {
      if (dossiers.length > 0) {
        dossierContext.setCurrentDossier(dossiers[0].id);
      }
      return true;
    })
  );
};
