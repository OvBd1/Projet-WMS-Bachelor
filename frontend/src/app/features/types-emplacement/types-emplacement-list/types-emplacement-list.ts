import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { TypeEmplacementService } from '../../../core/services/type-emplacement.service';
import { TypeEmplacement } from '../../../core/models/type-emplacement.model';
import { ConfirmDialogComponent } from '../../../shared/confirm-dialog/confirm-dialog';

@Component({
  selector: 'app-types-emplacement-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, ConfirmDialogComponent],
  templateUrl: './types-emplacement-list.html'
})
export class TypesEmplacementListComponent implements OnInit {
  types     = signal<TypeEmplacement[]>([]);
  loading   = signal(false);
  error     = signal('');
  formError = signal('');
  showForm  = signal(false);
  saving    = signal(false);
  editing   = signal<TypeEmplacement | null>(null);
  form: FormGroup;

  constructor(private service: TypeEmplacementService, private fb: FormBuilder) {
    this.form = this.fb.group({ libelle: ['', [Validators.required, Validators.maxLength(100)]] });
  }

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.error.set('');
    this.service.getAll().subscribe({
      next:  data => { this.types.set(data); this.loading.set(false); },
      error: ()   => { this.error.set('Impossible de charger les types.'); this.loading.set(false); }
    });
  }

  openCreate() {
    this.editing.set(null);
    this.formError.set('');
    this.form.reset();
    this.showForm.set(true);
  }

  openEdit(t: TypeEmplacement) {
    this.editing.set(t);
    this.formError.set('');
    this.form.patchValue({ libelle: t.libelle });
    this.showForm.set(true);
  }

  closeForm() {
    this.showForm.set(false);
    this.saving.set(false);
    this.form.reset();
    this.editing.set(null);
  }

  // Garde d'abandon de saisie : demande confirmation si le formulaire a été modifié.
  pendingClose = signal<(() => void) | null>(null);
  tryCloseForm() { if (this.form.dirty) { this.pendingClose.set(() => this.closeForm()); } else { this.closeForm(); } }
  confirmDiscard() { const close = this.pendingClose(); this.pendingClose.set(null); close?.(); }
  cancelDiscard()  { this.pendingClose.set(null); }

  submit() {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving.set(true);
    this.formError.set('');
    const payload = { libelle: this.form.value.libelle };
    const obs = this.editing()
      ? this.service.update(this.editing()!.id, payload)
      : this.service.create(payload);

    obs.subscribe({
      next:  () => { this.load(); this.closeForm(); },
      error: err => { this.formError.set(err.error?.message ?? 'Erreur lors de la sauvegarde.'); this.saving.set(false); }
    });
  }

  delete(t: TypeEmplacement) {
    if (!confirm(`Supprimer le type "${t.libelle}" ?`)) return;
    this.service.delete(t.id).subscribe({
      next:  () => this.load(),
      error: () => this.error.set('Suppression impossible (type utilisé par des emplacements).')
    });
  }
}
