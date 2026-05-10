import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { TypeEmplacementService } from '../../../core/services/type-emplacement.service';
import { TypeEmplacement } from '../../../core/models/type-emplacement.model';

@Component({
  selector: 'app-types-emplacement-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  template: `
    <div class="page-header">
      <h1>Types d'emplacement</h1>
      <button class="btn btn-primary" (click)="openCreate()">+ Nouveau type</button>
    </div>

    @if (error()) {
      <div class="alert-error">{{ error() }}</div>
    }

    <div class="table-wrap">
      @if (loading()) {
        <div class="state-loading">
          <div class="state-loading-spinner"></div>
          <span>Chargement…</span>
        </div>
      } @else if (types().length === 0) {
        <div class="state-empty">
          <p class="state-empty-title">Aucun type d'emplacement</p>
          <p class="state-empty-sub">Créez votre premier type d'emplacement.</p>
        </div>
      } @else {
        <table>
          <thead>
            <tr>
              <th>Libellé</th>
              <th style="width:120px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @for (t of types(); track t.id) {
              <tr>
                <td>{{ t.libelle }}</td>
                <td>
                  <div class="btn-group">
                    <button class="btn btn-secondary btn-sm" (click)="openEdit(t)">Éditer</button>
                    <button class="btn btn-danger btn-sm" (click)="delete(t)">Suppr.</button>
                  </div>
                </td>
              </tr>
            }
          </tbody>
        </table>
      }
    </div>

    @if (showForm()) {
      <div class="modal-overlay" (click)="closeForm()">
        <div class="modal" (click)="$event.stopPropagation()">
          <h2>{{ editing() ? 'Modifier le type' : 'Nouveau type' }}</h2>
          <form [formGroup]="form" (ngSubmit)="submit()">
            <div class="form-group">
              <label>Libellé *</label>
              <input formControlName="libelle" placeholder="Ex : Allée, Rayon, Zone froide…"
                     [class.invalid]="form.get('libelle')!.invalid && form.get('libelle')!.touched">
            </div>
            @if (formError()) {
              <div class="alert-error">{{ formError() }}</div>
            }
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" (click)="closeForm()">Annuler</button>
              <button type="submit" class="btn btn-primary" [disabled]="form.invalid || saving()">
                {{ saving() ? 'Enregistrement…' : 'Enregistrer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    }
  `
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
