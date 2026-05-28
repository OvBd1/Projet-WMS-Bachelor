import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';

export interface DashboardStats {
  articles: number;
  emplacements: number;
  stocks: number;
  receptions: number;
  commandes: number;
  transferts: number;
}

@Injectable({ providedIn: 'root' })
export class DashboardService {
  private url = `${environment.apiUrl}/dashboard`;

  constructor(private http: HttpClient) {}

  getStats() {
    return this.http.get<DashboardStats>(this.url);
  }
}
