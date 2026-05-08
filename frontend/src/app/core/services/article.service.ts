import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../../environments/environment';
import { Article } from '../models/article.model';

export interface ArticlePayload {
  reference: string;
  libelle: string;
  description?: string | null;
  gestionDlc?: boolean;
  gestionNumeroSerie?: boolean;
  typeConditionnementId?: number | null;
}

@Injectable({ providedIn: 'root' })
export class ArticleService {
  private url = `${environment.apiUrl}/articles`;

  constructor(private http: HttpClient) {}

  getAll() {
    return this.http.get<Article[]>(this.url);
  }

  getById(id: number) {
    return this.http.get<Article>(`${this.url}/${id}`);
  }

  create(data: ArticlePayload) {
    return this.http.post<Article>(this.url, data);
  }

  update(id: number, data: ArticlePayload) {
    return this.http.put<Article>(`${this.url}/${id}`, data);
  }

  delete(id: number) {
    return this.http.delete<void>(`${this.url}/${id}`);
  }

  uploadImage(id: number, file: File) {
    const formData = new FormData();
    formData.append('image', file);
    return this.http.post<Article>(`${this.url}/${id}/image`, formData);
  }
}
