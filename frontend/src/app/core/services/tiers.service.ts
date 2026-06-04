import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Tiers } from '../models/tiers.model';

export interface TiersPayload {
  code: string;
  nom: string;
  type: string;
  rue?: string | null;
  codePostal?: string | null;
  ville?: string | null;
  pays?: string | null;
}

@Injectable({ providedIn: 'root' })
export class TiersService {
  private url = `${environment.apiUrl}/tiers`;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<Tiers[]>(this.url);
  }

  getById(id: number) {
    return this.http.get<Tiers>(`${this.url}/${id}`);
  }

  create(payload: TiersPayload) {
    return this.http.post<Tiers>(this.url, payload);
  }

  update(id: number, payload: TiersPayload) {
    return this.http.put<Tiers>(`${this.url}/${id}`, payload);
  }

  delete(id: number) {
    return this.http.delete<void>(`${this.url}/${id}`);
  }
}
