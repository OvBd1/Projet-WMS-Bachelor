import { Injectable, signal } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Router } from '@angular/router';
import { tap } from 'rxjs/operators';
import { environment } from '../../../environments/environment';
import { LoginResponse } from '../models/user.model';

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
    const token = this.getToken();
    if (!token) return null;
    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      return payload.email || payload.username || null;
    } catch {
      return null;
    }
  }

  getUserFullName(): string | null {
    const token = this.getToken();
    if (!token) return null;
    try {
      const payload = JSON.parse(atob(token.split('.')[1]));
      const fullName = [payload.prenom, payload.nom].filter(Boolean).join(' ').trim();
      return fullName || null;
    } catch {
      return null;
    }
  }

  getUserDisplayName(): string {
    return this.getUserFullName() ?? this.getUserEmail() ?? '?';
  }

  getUserInitials(): string {
    const token = this.getToken();
    if (token) {
      try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        const initials = [payload.prenom, payload.nom]
          .filter(Boolean)
          .map((s: string) => s.charAt(0))
          .join('');
        if (initials) return initials.toUpperCase();
      } catch {
        // fall through to email-based initials
      }
    }

    const email = this.getUserEmail();
    if (!email) return '?';
    return email.split('@')[0].slice(0, 2).toUpperCase();
  }

  private hasToken(): boolean {
    return !!localStorage.getItem(this.TOKEN_KEY);
  }
}
