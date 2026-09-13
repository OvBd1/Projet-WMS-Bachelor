import { TestBed } from '@angular/core/testing';
import { HttpClient, provideHttpClient, withInterceptors } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { jwtInterceptor } from './jwt.interceptor';
import { AuthService } from '../services/auth.service';

describe('jwtInterceptor', () => {
  let http: HttpClient;
  let httpMock: HttpTestingController;
  const auth = { getToken: vi.fn<() => string | null>() };

  beforeEach(() => {
    auth.getToken.mockReset();
    TestBed.configureTestingModule({
      providers: [
        provideHttpClient(withInterceptors([jwtInterceptor])),
        provideHttpClientTesting(),
        { provide: AuthService, useValue: auth }
      ]
    });
    http = TestBed.inject(HttpClient);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpMock.verify());

  it('ajoute l\'en-tête Authorization quand un token existe', () => {
    auth.getToken.mockReturnValue('mon-token');

    http.get('/api/articles').subscribe();

    const req = httpMock.expectOne('/api/articles');
    expect(req.request.headers.get('Authorization')).toBe('Bearer mon-token');
    req.flush([]);
  });

  it('n\'ajoute rien sans token', () => {
    auth.getToken.mockReturnValue(null);

    http.get('/api/articles').subscribe();

    const req = httpMock.expectOne('/api/articles');
    expect(req.request.headers.has('Authorization')).toBe(false);
    req.flush([]);
  });
});
