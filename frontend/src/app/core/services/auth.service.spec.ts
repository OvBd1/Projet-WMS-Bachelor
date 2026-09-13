import { TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { Router } from '@angular/router';
import { AuthService } from './auth.service';
import { environment } from '../../../environments/environment';

// Fabrique un faux JWT (seul le payload est lu côté client).
function fakeJwt(payload: object): string {
  return `header.${btoa(JSON.stringify(payload))}.sig`;
}

describe('AuthService', () => {
  let service: AuthService;
  let httpMock: HttpTestingController;
  let router: { navigate: ReturnType<typeof vi.fn> };

  beforeEach(() => {
    localStorage.clear();
    router = { navigate: vi.fn() };
    TestBed.configureTestingModule({
      providers: [
        provideHttpClient(),
        provideHttpClientTesting(),
        { provide: Router, useValue: router }
      ]
    });
    service = TestBed.inject(AuthService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => {
    httpMock.verify();
    localStorage.clear();
  });

  it('stocke le token et passe authentifié après connexion', () => {
    expect(service.isAuthenticated()).toBe(false);

    service.login('a@b.fr', 'secret').subscribe();

    const req = httpMock.expectOne(`${environment.apiUrl}/auth/login`);
    expect(req.request.method).toBe('POST');
    expect(req.request.body).toEqual({ email: 'a@b.fr', password: 'secret' });
    req.flush({ token: 'abc.def.ghi' });

    expect(localStorage.getItem('jwt_token')).toBe('abc.def.ghi');
    expect(service.isAuthenticated()).toBe(true);
  });

  it('supprime le token et redirige vers la connexion à la déconnexion', () => {
    localStorage.setItem('jwt_token', 'abc.def.ghi');
    service.isAuthenticated.set(true);

    service.logout();

    expect(localStorage.getItem('jwt_token')).toBeNull();
    expect(service.isAuthenticated()).toBe(false);
    expect(router.navigate).toHaveBeenCalledWith(['/auth/login']);
  });

  describe('isAdmin', () => {
    it('renvoie true si le payload contient ROLE_ADMIN', () => {
      localStorage.setItem('jwt_token', fakeJwt({ roles: ['ROLE_USER', 'ROLE_ADMIN'] }));
      expect(service.isAdmin()).toBe(true);
    });

    it('renvoie false pour un simple utilisateur', () => {
      localStorage.setItem('jwt_token', fakeJwt({ roles: ['ROLE_USER'] }));
      expect(service.isAdmin()).toBe(false);
    });

    it('renvoie false sans lever d\'erreur si le token est mal formé', () => {
      localStorage.setItem('jwt_token', 'pas-un-jwt');
      expect(() => service.isAdmin()).not.toThrow();
      expect(service.isAdmin()).toBe(false);
    });
  });

  describe('nom affiché et initiales', () => {
    it('utilise le prénom et le nom', () => {
      localStorage.setItem('jwt_token', fakeJwt({ email: 'jean.dupont@wms.fr', prenom: 'Jean', nom: 'Dupont' }));
      expect(service.getUserDisplayName()).toBe('Jean Dupont');
      expect(service.getUserInitials()).toBe('JD');
    });

    it('se rabat sur l\'email sans prénom ni nom', () => {
      localStorage.setItem('jwt_token', fakeJwt({ email: 'magasin@wms.fr', prenom: null, nom: null }));
      expect(service.getUserDisplayName()).toBe('magasin@wms.fr');
      expect(service.getUserInitials()).toBe('MA');
    });

    it('renvoie « ? » sans token', () => {
      expect(service.getUserDisplayName()).toBe('?');
      expect(service.getUserInitials()).toBe('?');
    });
  });
});
