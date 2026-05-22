import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { TypeConditionnement } from '../models/type-conditionnement.model';

@Injectable({ providedIn: 'root' })
export class TypeConditionnementService {
  private url = `${environment.apiUrl}/types-conditionnement`;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<TypeConditionnement[]>(this.url);
  }

  create(data: { libelle: string }) {
    return this.http.post<TypeConditionnement>(this.url, data);
  }

  update(id: number, data: { libelle: string }) {
    return this.http.put<TypeConditionnement>(`${this.url}/${id}`, data);
  }

  delete(id: number) {
    return this.http.delete<void>(`${this.url}/${id}`);
  }
}
