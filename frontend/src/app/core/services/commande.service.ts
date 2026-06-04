import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Commande, StatutCommande } from '../models/commande.model';

export interface LigneCommandePayload {
  articleId: number;
  quantite: number;
}

export interface CommandePayload {
  lignes: LigneCommandePayload[];
  dateCommande?: string | null;
  tiersId?: number | null;
}

@Injectable({ providedIn: 'root' })
export class CommandeService {
  private url = `${environment.apiUrl}/commandes`;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<Commande[]>(this.url);
  }

  getById(id: number) {
    return this.http.get<Commande>(`${this.url}/${id}`);
  }

  create(payload: CommandePayload) {
    return this.http.post<Commande>(this.url, payload);
  }

  update(id: number, payload: CommandePayload) {
    return this.http.put<Commande>(`${this.url}/${id}`, payload);
  }

  updateStatut(id: number, statut: StatutCommande) {
    return this.http.patch<Commande>(`${this.url}/${id}/statut`, { statut });
  }

  delete(id: number) {
    return this.http.delete<void>(`${this.url}/${id}`);
  }
}
