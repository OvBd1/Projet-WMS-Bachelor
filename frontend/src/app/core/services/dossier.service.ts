import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Dossier } from '../models/dossier.model';

export interface DossierPayload {
  code: string;
  raisonSociale: string;
  rue?: string | null;
  codePostal?: string | null;
  ville?: string | null;
  pays?: string | null;
}

@Injectable({ providedIn: 'root' })
export class DossierService {
  private url = `${environment.apiUrl}/dossiers`;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<Dossier[]>(this.url);
  }

  getById(id: number) {
    return this.http.get<Dossier>(`${this.url}/${id}`);
  }

  create(payload: DossierPayload) {
    return this.http.post<Dossier>(this.url, payload);
  }

  update(id: number, payload: DossierPayload) {
    return this.http.put<Dossier>(`${this.url}/${id}`, payload);
  }
}
