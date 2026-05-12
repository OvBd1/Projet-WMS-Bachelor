import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Stock } from '../models/stock.model';

@Injectable({ providedIn: 'root' })
export class StockService {
  private url = `${environment.apiUrl}/stocks`;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<Stock[]>(this.url);
  }

  getByArticle(articleId: number) {
    return this.http.get<Stock[]>(`${this.url}/article/${articleId}`);
  }

  getByEmplacement(emplacementId: number) {
    return this.http.get<Stock[]>(`${this.url}/emplacement/${emplacementId}`);
  }

  patch(id: number, quantite: number) {
    return this.http.patch<Stock>(`${this.url}/${id}`, { quantite });
  }
}
