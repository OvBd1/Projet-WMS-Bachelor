import { HttpInterceptorFn } from '@angular/common/http';
import { inject } from '@angular/core';
import { AuthService } from '../services/auth.service';
import { DossierContextService } from '../services/dossier-context.service';

export const dossierInterceptor: HttpInterceptorFn = (req, next) => {
  const auth = inject(AuthService);
  const dossierContext = inject(DossierContextService);

  const dossierId = dossierContext.currentDossierId();
  if (auth.isAdmin() && dossierId) {
    req = req.clone({ setHeaders: { 'X-Dossier-Id': String(dossierId) } });
  }
  return next(req);
};
