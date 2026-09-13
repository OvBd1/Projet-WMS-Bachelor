import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, catchError, of } from 'rxjs';
import { environment } from '../../../environments/environment';
import { AdresseSuggestion } from '../models/adresse.model';

export const ADRESSE_MIN_LENGTH = 3;

@Injectable({ providedIn: 'root' })
export class AdresseService {
  private url = `${environment.apiUrl}/adresses/search`;

  constructor(private http: HttpClient) {}

  /**
   * Suggestions d'adresses via la passerelle API (Base Adresse Nationale).
   * Toute erreur donne une liste vide : la saisie manuelle reste possible.
   */
  search(query: string, limit = 5): Observable<AdresseSuggestion[]> {
    const q = query.trim();
    if (q.length < ADRESSE_MIN_LENGTH) {
      return of([]);
    }

    const params = new HttpParams().set('q', q).set('limit', limit);
    return this.http.get<AdresseSuggestion[]>(this.url, { params }).pipe(
      catchError(() => of([]))
    );
  }
}
