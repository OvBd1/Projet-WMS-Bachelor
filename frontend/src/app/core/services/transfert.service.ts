import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Transfert } from '../models/transfert.model';

export interface TransfertPayload {
  articleId: number;
  emplacementSourceId: number;
  emplacementDestinationId: number;
  quantite: number;
}

@Injectable({ providedIn: 'root' })
export class TransfertService {
  private url = `${environment.apiUrl}/transferts`;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<Transfert[]>(this.url);
  }

  getById(id: number) {
    return this.http.get<Transfert>(`${this.url}/${id}`);
  }

  create(payload: TransfertPayload) {
    return this.http.post<Transfert>(this.url, payload);
  }
}
