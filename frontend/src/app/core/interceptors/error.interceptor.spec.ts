import { TestBed } from '@angular/core/testing';
import { HttpClient, HttpErrorResponse, provideHttpClient, withInterceptors } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { Router } from '@angular/router';
import { errorInterceptor } from './error.interceptor';
import { AuthService } from '../services/auth.service';

describe('errorInterceptor', () => {
  let http: HttpClient;
  let httpMock: HttpTestingController;
  const auth = { logout: vi.fn() };
  const router = { navigate: vi.fn() };

  beforeEach(() => {
    auth.logout.mockReset();
    router.navigate.mockReset();
    TestBed.configureTestingModule({
      providers: [
        provideHttpClient(withInterceptors([errorInterceptor])),
        provideHttpClientTesting(),
        { provide: AuthService, useValue: auth },
        { provide: Router, useValue: router }
      ]
    });
    http = TestBed.inject(HttpClient);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpMock.verify());

  // Déclenche une erreur HTTP et renvoie l'erreur reçue par l'abonné.
  function failWith(status: number): HttpErrorResponse | undefined {
    let received: HttpErrorResponse | undefined;
    http.get('/api/articles').subscribe({ error: err => (received = err) });
    httpMock.expectOne('/api/articles').flush('erreur', { status, statusText: 'Erreur' });
    return received;
  }

  it('déconnecte l\'utilisateur sur une 401 et relaie l\'erreur', () => {
    const err = failWith(401);
    expect(auth.logout).toHaveBeenCalledTimes(1);
    expect(router.navigate).not.toHaveBeenCalled();
    expect(err?.status).toBe(401);
  });

  it('redirige vers l\'accueil sur une 403 et relaie l\'erreur', () => {
    const err = failWith(403);
    expect(router.navigate).toHaveBeenCalledWith(['/']);
    expect(auth.logout).not.toHaveBeenCalled();
    expect(err?.status).toBe(403);
  });

  it('ne fait rien de particulier sur les autres erreurs mais les relaie', () => {
    const err = failWith(500);
    expect(auth.logout).not.toHaveBeenCalled();
    expect(router.navigate).not.toHaveBeenCalled();
    expect(err?.status).toBe(500);
  });
});
