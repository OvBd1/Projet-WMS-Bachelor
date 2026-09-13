import { TestBed } from '@angular/core/testing';
import { signal } from '@angular/core';
import { HttpClient, provideHttpClient, withInterceptors } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { dossierInterceptor } from './dossier.interceptor';
import { AuthService } from '../services/auth.service';
import { DossierContextService } from '../services/dossier-context.service';

describe('dossierInterceptor', () => {
  let http: HttpClient;
  let httpMock: HttpTestingController;
  const auth = { isAdmin: vi.fn<() => boolean>() };
  const dossierContext = { currentDossierId: signal<number | null>(null) };

  beforeEach(() => {
    auth.isAdmin.mockReset();
    dossierContext.currentDossierId.set(null);
    TestBed.configureTestingModule({
      providers: [
        provideHttpClient(withInterceptors([dossierInterceptor])),
        provideHttpClientTesting(),
        { provide: AuthService, useValue: auth },
        { provide: DossierContextService, useValue: dossierContext }
      ]
    });
    http = TestBed.inject(HttpClient);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpMock.verify());

  // Envoie une requête et renvoie la valeur de l'en-tête X-Dossier-Id.
  function headerSent(): string | null {
    http.get('/api/stocks').subscribe();
    const req = httpMock.expectOne('/api/stocks');
    const value = req.request.headers.get('X-Dossier-Id');
    req.flush([]);
    return value;
  }

  it('ajoute X-Dossier-Id pour un admin avec un dossier sélectionné', () => {
    auth.isAdmin.mockReturnValue(true);
    dossierContext.currentDossierId.set(3);
    expect(headerSent()).toBe('3');
  });

  it('n\'ajoute rien pour un admin sans dossier sélectionné', () => {
    auth.isAdmin.mockReturnValue(true);
    expect(headerSent()).toBeNull();
  });

  it('n\'ajoute rien pour un non-admin même avec un dossier', () => {
    auth.isAdmin.mockReturnValue(false);
    dossierContext.currentDossierId.set(3);
    expect(headerSent()).toBeNull();
  });
});
