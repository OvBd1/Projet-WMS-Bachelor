import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Emplacement } from '../models/emplacement.model';

@Injectable({ providedIn: 'root' })
export class EmplacementService {
  private url = `${environment.apiUrl}/emplacements`;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<Emplacement[]>(this.url);
  }

  create(data: { code: string; typeEmplacementId: number; description?: string }) {
    return this.http.post<Emplacement>(this.url, data);
  }

  update(id: number, data: { code: string; typeEmplacementId: number; description?: string }) {
    return this.http.put<Emplacement>(`${this.url}/${id}`, data);
  }

  delete(id: number) {
    return this.http.delete<void>(`${this.url}/${id}`);
  }
}
