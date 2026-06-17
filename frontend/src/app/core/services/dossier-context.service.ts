import { Injectable, signal } from '@angular/core';

const STORAGE_KEY = 'active_dossier_id';

@Injectable({ providedIn: 'root' })
export class DossierContextService {
  currentDossierId = signal<number | null>(this.readStored());

  setCurrentDossier(id: number | null): void {
    this.currentDossierId.set(id);
    if (id) {
      localStorage.setItem(STORAGE_KEY, String(id));
    } else {
      localStorage.removeItem(STORAGE_KEY);
    }
  }

  private readStored(): number | null {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? Number(raw) : null;
  }
}
