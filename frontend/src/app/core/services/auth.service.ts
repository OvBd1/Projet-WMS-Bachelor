import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import { LoginResponse } from '../models/user.model';

interface JwtPayload {
  username?: string;
  email?: string;
  nom?: string | null;
  prenom?: string | null;
  roles?: string[];
  id?: number;
  [key: string]: unknown;
}

@Injectable({ providedIn: 'root' })
export class AuthService {
  private readonly TOKEN_KEY = 'jwt_token';

  isAuthenticated = signal(this.hasToken());

  constructor(private http: HttpClient, private router: Router) {}

  login(email: string, password: string) {
    return this.http
      .post<LoginResponse>(`${environment.apiUrl}/auth/login`, { email, password })
      .pipe(
        tap(res => {
          localStorage.setItem(this.TOKEN_KEY, res.token);
          this.isAuthenticated.set(true);
        })
      );
  }

  logout(): void {
    localStorage.removeItem(this.TOKEN_KEY);
    this.isAuthenticated.set(false);
    this.router.navigate(['/auth/login']);
  }

  getToken(): string | null {
    return localStorage.getItem(this.TOKEN_KEY);
  }

  getUserEmail(): string | null {
    const payload = this.getPayload();
    return payload?.email || payload?.username || null;
  }

  getUserFullName(): string | null {
    const payload = this.getPayload();
    if (!payload) return null;
    const fullName = [payload.prenom, payload.nom].filter(Boolean).join(' ').trim();
    return fullName || null;
  }

  getUserDisplayName(): string {
    return this.getUserFullName() ?? this.getUserEmail() ?? '?';
  }

  getUserInitials(): string {
    const payload = this.getPayload();
    if (payload) {
      const initials = [payload.prenom, payload.nom]
        .filter(Boolean)
        .map(s => (s as string).charAt(0))
        .join('');
      if (initials) return initials.toUpperCase();
    }

    const email = this.getUserEmail();
    if (!email) return '?';
    return email.split('@')[0].slice(0, 2).toUpperCase();
  }

  isAdmin(): boolean {
    return !!this.getPayload()?.roles?.includes('ROLE_ADMIN');
  }

  private getPayload(): JwtPayload | null {
    const token = this.getToken();
    if (!token) return null;
    try {
      return JSON.parse(atob(token.split('.')[1]));
    } catch {
      return null;
    }
  }

  private hasToken(): boolean {
    return !!localStorage.getItem(this.TOKEN_KEY);
  }
}
