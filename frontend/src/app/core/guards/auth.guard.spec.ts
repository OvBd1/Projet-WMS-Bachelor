import { TestBed } from '@angular/core/testing';
import { signal } from '@angular/core';
import { Router, UrlTree, provideRouter } from '@angular/router';
import { authGuard } from './auth.guard';
import { AuthService } from '../services/auth.service';

describe('authGuard', () => {
  const auth = { isAuthenticated: signal(false) };

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideRouter([]), { provide: AuthService, useValue: auth }]
    });
  });

  const run = () => TestBed.runInInjectionContext(() => authGuard({} as any, {} as any));

  it('laisse passer un utilisateur connecté', () => {
    auth.isAuthenticated.set(true);
    expect(run()).toBe(true);
  });

  it('redirige vers /auth/login un visiteur non connecté', () => {
    auth.isAuthenticated.set(false);
    const result = run();
    expect(result).toBeInstanceOf(UrlTree);
    expect(TestBed.inject(Router).serializeUrl(result as UrlTree)).toBe('/auth/login');
  });
});
