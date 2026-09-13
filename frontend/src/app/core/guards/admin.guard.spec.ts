import { TestBed } from '@angular/core/testing';
import { Router, UrlTree, provideRouter } from '@angular/router';
import { adminGuard } from './admin.guard';
import { AuthService } from '../services/auth.service';

describe('adminGuard', () => {
  const auth = { isAdmin: vi.fn<() => boolean>() };

  beforeEach(() => {
    auth.isAdmin.mockReset();
    TestBed.configureTestingModule({
      providers: [provideRouter([]), { provide: AuthService, useValue: auth }]
    });
  });

  const run = () => TestBed.runInInjectionContext(() => adminGuard({} as any, {} as any));

  it('laisse passer un administrateur', () => {
    auth.isAdmin.mockReturnValue(true);
    expect(run()).toBe(true);
  });

  it('redirige vers /dashboard un non-administrateur', () => {
    auth.isAdmin.mockReturnValue(false);
    const result = run();
    expect(result).toBeInstanceOf(UrlTree);
    expect(TestBed.inject(Router).serializeUrl(result as UrlTree)).toBe('/dashboard');
  });
});
