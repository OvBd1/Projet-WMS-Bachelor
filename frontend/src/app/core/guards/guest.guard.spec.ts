import { TestBed } from '@angular/core/testing';
import { signal } from '@angular/core';
import { Router, UrlTree, provideRouter } from '@angular/router';
import { guestGuard } from './guest.guard';
import { AuthService } from '../services/auth.service';

describe('guestGuard', () => {
  const auth = { isAuthenticated: signal(false) };

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideRouter([]), { provide: AuthService, useValue: auth }]
    });
  });

  const run = () => TestBed.runInInjectionContext(() => guestGuard({} as any, {} as any));

  it('laisse passer un visiteur non connecté', () => {
    auth.isAuthenticated.set(false);
    expect(run()).toBe(true);
  });

  it('redirige vers / un utilisateur déjà connecté', () => {
    auth.isAuthenticated.set(true);
    const result = run();
    expect(result).toBeInstanceOf(UrlTree);
    expect(TestBed.inject(Router).serializeUrl(result as UrlTree)).toBe('/');
  });
});
