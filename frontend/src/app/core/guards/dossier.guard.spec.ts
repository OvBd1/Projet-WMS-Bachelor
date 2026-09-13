import { TestBed } from '@angular/core/testing';
import { signal } from '@angular/core';
import { Observable, firstValueFrom, of } from 'rxjs';
import { dossierGuard } from './dossier.guard';
import { AuthService } from '../services/auth.service';
import { DossierService } from '../services/dossier.service';
import { DossierContextService } from '../services/dossier-context.service';

describe('dossierGuard', () => {
  const auth = { isAdmin: vi.fn<() => boolean>() };
  const dossierService = { getAll: vi.fn() };
  const dossierContext = {
    currentDossierId: signal<number | null>(null),
    setCurrentDossier: vi.fn()
  };

  beforeEach(() => {
    auth.isAdmin.mockReset();
    dossierService.getAll.mockReset();
    dossierContext.setCurrentDossier.mockReset();
    dossierContext.currentDossierId.set(null);
    TestBed.configureTestingModule({
      providers: [
        { provide: AuthService, useValue: auth },
        { provide: DossierService, useValue: dossierService },
        { provide: DossierContextService, useValue: dossierContext }
      ]
    });
  });

  const run = () => firstValueFrom(
    TestBed.runInInjectionContext(() => dossierGuard({} as any, {} as any)) as Observable<boolean>
  );

  it('laisse passer un non-admin sans charger les dossiers', async () => {
    auth.isAdmin.mockReturnValue(false);
    await expect(run()).resolves.toBe(true);
    expect(dossierService.getAll).not.toHaveBeenCalled();
  });

  it('laisse passer un admin ayant déjà un dossier sélectionné', async () => {
    auth.isAdmin.mockReturnValue(true);
    dossierContext.currentDossierId.set(5);
    await expect(run()).resolves.toBe(true);
    expect(dossierService.getAll).not.toHaveBeenCalled();
  });

  it('sélectionne le premier dossier pour un admin sans dossier', async () => {
    auth.isAdmin.mockReturnValue(true);
    dossierService.getAll.mockReturnValue(of([{ id: 11 }, { id: 12 }]));
    await expect(run()).resolves.toBe(true);
    expect(dossierContext.setCurrentDossier).toHaveBeenCalledWith(11);
  });

  it('laisse passer sans sélection si aucun dossier n\'existe', async () => {
    auth.isAdmin.mockReturnValue(true);
    dossierService.getAll.mockReturnValue(of([]));
    await expect(run()).resolves.toBe(true);
    expect(dossierContext.setCurrentDossier).not.toHaveBeenCalled();
  });
});
