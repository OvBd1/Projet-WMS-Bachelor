import { TestBed } from '@angular/core/testing';
import { DossierContextService } from './dossier-context.service';

describe('DossierContextService', () => {
  beforeEach(() => localStorage.clear());
  afterEach(() => localStorage.clear());

  it('lit le dossier actif depuis le localStorage à l\'initialisation', () => {
    localStorage.setItem('active_dossier_id', '42');
    const service = TestBed.inject(DossierContextService);
    expect(service.currentDossierId()).toBe(42);
  });

  it('démarre sans dossier si rien n\'est stocké', () => {
    const service = TestBed.inject(DossierContextService);
    expect(service.currentDossierId()).toBeNull();
  });

  it('mémorise le dossier sélectionné', () => {
    const service = TestBed.inject(DossierContextService);
    service.setCurrentDossier(7);
    expect(service.currentDossierId()).toBe(7);
    expect(localStorage.getItem('active_dossier_id')).toBe('7');
  });

  it('efface le dossier stocké quand on passe null', () => {
    localStorage.setItem('active_dossier_id', '7');
    const service = TestBed.inject(DossierContextService);
    service.setCurrentDossier(null);
    expect(service.currentDossierId()).toBeNull();
    expect(localStorage.getItem('active_dossier_id')).toBeNull();
  });
});
