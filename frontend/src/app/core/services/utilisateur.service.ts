import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { User } from '../models/user.model';

export interface UtilisateurPayload {
  email: string;
  nom: string;
  prenom: string;
  password: string;
  role: string;
  dossierId?: number | null;
}

@Injectable({ providedIn: 'root' })
export class UtilisateurService {
  private url = `${environment.apiUrl}/utilisateurs`;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<User[]>(this.url);
  }

  create(payload: UtilisateurPayload) {
    return this.http.post<User>(this.url, payload);
  }
}
