import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { TypeEmplacement } from '../models/type-emplacement.model';

@Injectable({ providedIn: 'root' })
export class TypeEmplacementService {
  private url = `${environment.apiUrl}/types-emplacement`;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<TypeEmplacement[]>(this.url);
  }

  create(data: { libelle: string }) {
    return this.http.post<TypeEmplacement>(this.url, data);
  }

  update(id: number, data: { libelle: string }) {
    return this.http.put<TypeEmplacement>(`${this.url}/${id}`, data);
  }

  delete(id: number) {
    return this.http.delete<void>(`${this.url}/${id}`);
  }
}
