import { Component, input, output } from '@angular/core';

@Component({
  selector: 'app-confirm-dialog',
  standalone: true,
  templateUrl: './confirm-dialog.html'
})
export class ConfirmDialogComponent {
  title        = input('Confirmation');
  message      = input('');
  confirmLabel = input('Confirmer');
  cancelLabel  = input('Annuler');
  variant      = input<'primary' | 'danger' | 'success' | 'warning'>('primary');
  loading      = input(false);

  confirmed = output<void>();
  cancelled = output<void>();
}
