import { Injectable, signal } from '@angular/core';

export type ConfirmVariant = 'primary' | 'danger' | 'success' | 'warning';

export interface ConfirmOptions {
  title: string;
  message: string;
  confirmLabel?: string;
  variant?: ConfirmVariant;
}

interface ConfirmRequest extends Required<ConfirmOptions> {
  resolve: (confirmed: boolean) => void;
}

/**
 * Remplace window.confirm() par la modale partagée app-confirm-dialog,
 * hébergée une seule fois dans MainLayoutComponent.
 *
 * Usage : if (!(await this.confirm.ask({ title, message }))) return;
 */
@Injectable({ providedIn: 'root' })
export class ConfirmService {
  private readonly current = signal<ConfirmRequest | null>(null);
  readonly request = this.current.asReadonly();

  ask(options: ConfirmOptions): Promise<boolean> {
    // Une seule modale à la fois : une demande encore ouverte est considérée comme refusée.
    this.current()?.resolve(false);
    return new Promise(resolve =>
      this.current.set({ confirmLabel: 'Confirmer', variant: 'primary', ...options, resolve })
    );
  }

  answer(confirmed: boolean): void {
    const request = this.current();
    this.current.set(null);
    request?.resolve(confirmed);
  }
}
