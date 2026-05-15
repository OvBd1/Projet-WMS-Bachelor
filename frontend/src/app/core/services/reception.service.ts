import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Reception } from '../models/reception.model';

export interface LigneReceptionPayload {
  articleId: number;
  emplacementId: number;
  quantite: number;
  dlc?: string | null;
  numeroSerie?: string | null;
}

export interface ReceptionPayload {
  lignes: LigneReceptionPayload[];
  tiersId?: number | null;
  dateReception?: string | null;
}

@Injectable({ providedIn: 'root' })
export class ReceptionService {
  private url = `${environment.apiUrl}/receptions`;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<Reception[]>(this.url);
  }

  getById(id: number) {
    return this.http.get<Reception>(`${this.url}/${id}`);
  }

  create(payload: ReceptionPayload) {
    return this.http.post<Reception>(this.url, payload);
  }

  valider(id: number) {
    return this.http.patch<Reception>(`${this.url}/${id}/valider`, {});
  }

  annuler(id: number) {
    return this.http.patch<Reception>(`${this.url}/${id}/annuler`, {});
  }

  delete(id: number) {
    return this.http.delete<void>(`${this.url}/${id}`);
  }
}
