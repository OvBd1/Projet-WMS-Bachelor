import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { forkJoin } from 'rxjs';
import { EmplacementService } from '../../../core/services/emplacement.service';
import { TypeEmplacementService } from '../../../core/services/type-emplacement.service';
import { Emplacement } from '../../../core/models/emplacement.model';
import { TypeEmplacement } from '../../../core/models/type-emplacement.model';

@Component({
  selector: 'app-emplacements-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  template: `
    <div class="page-header">
      <h1>Emplacements</h1>
      <div class="btn-group">
        <button class="btn btn-secondary" (click)="openTypeForm()">+ Type</button>
        <button class="btn btn-primary" (click)="openCreate()">+ Emplacement</button>
      </div>
    </div>

    @if (error()) {
      <div class="alert-error">{{ error() }}</div>
    }

    <div class="table-wrap">
      @if (loading()) {
        <div class="state-loading">
          <div class="state-loading-spinner"></div>
          <span>Chargement des emplacements…</span>
        </div>
      } @else if (emplacements().length === 0) {
        <div class="state-empty">
          <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/>
          </svg>
          <p class="state-empty-title">Aucun emplacement</p>
          <p class="state-empty-sub">Créez des emplacements pour organiser vos stocks.</p>
        </div>
      } @else {
        <table>
          <thead>
            <tr>
              <th>Code</th>
              <th>Type</th>
              <th>Description</th>
              <th style="width:120px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @for (e of emplacements(); track e.id) {
              <tr>
                <td><span class="badge badge-blue">{{ e.code }}</span></td>
                <td><span class="badge badge-gray">{{ e.typeEmplacement.libelle }}</span></td>
                <td>{{ e.description || '—' }}</td>
                <td>
                  <div class="btn-group">
                    <button class="btn btn-secondary btn-sm" (click)="openEdit(e)">Éditer</button>
                    <button class="btn btn-danger btn-sm" (click)="delete(e)">Suppr.</button>
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
          <h2>{{ editing() ? 'Modifier l\'emplacement' : 'Nouvel emplacement' }}</h2>
          <form [formGroup]="form" (ngSubmit)="submit()">
            <div class="form-group">
              <label>Code *</label>
              <input formControlName="code" placeholder="A1-01" maxlength="50"
                     [class.invalid]="form.get('code')!.invalid && form.get('code')!.touched">
            </div>
            <div class="form-group">
              <label>Type d'emplacement *</label>
              <select formControlName="typeEmplacementId"
                      [class.invalid]="form.get('typeEmplacementId')!.invalid && form.get('typeEmplacementId')!.touched">
                <option value="">— Choisir un type —</option>
                @for (t of types(); track t.id) {
                  <option [value]="t.id">{{ t.libelle }}</option>
                }
              </select>
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea formControlName="description" placeholder="Description optionnelle" rows="2"></textarea>
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

    @if (showTypeForm()) {
      <div class="modal-overlay" (click)="closeTypeForm()">
        <div class="modal" (click)="$event.stopPropagation()">
          <h2>Nouveau type d'emplacement</h2>
          <form [formGroup]="typeForm" (ngSubmit)="submitType()">
            <div class="form-group">
              <label>Libellé *</label>
              <input formControlName="libelle" placeholder="Palette, Rayonnage, Frigo…"
                     [class.invalid]="typeForm.get('libelle')!.invalid && typeForm.get('libelle')!.touched">
            </div>
            @if (typeFormError()) {
              <div class="alert-error">{{ typeFormError() }}</div>
            }
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" (click)="closeTypeForm()">Annuler</button>
              <button type="submit" class="btn btn-primary" [disabled]="typeForm.invalid || saving()">
                {{ saving() ? 'Enregistrement…' : 'Enregistrer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    }
  `
})
export class EmplacementsListComponent implements OnInit {
  emplacements  = signal<Emplacement[]>([]);
  types         = signal<TypeEmplacement[]>([]);
  loading       = signal(false);
  error         = signal('');
  formError     = signal('');
  typeFormError = signal('');
  showForm      = signal(false);
  showTypeForm  = signal(false);
  saving        = signal(false);
  editing       = signal<Emplacement | null>(null);
  form: FormGroup;
  typeForm: FormGroup;

  constructor(
    private emplacementService: EmplacementService,
    private typeService: TypeEmplacementService,
    private fb: FormBuilder
  ) {
    this.form = this.fb.group({
      code:               ['', [Validators.required, Validators.maxLength(50)]],
      typeEmplacementId:  ['', Validators.required],
      description:        ['']
    });
    this.typeForm = this.fb.group({
      libelle: ['', [Validators.required, Validators.maxLength(100)]]
    });
  }

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.error.set('');
    forkJoin({
      emplacements: this.emplacementService.getAll(),
      types:        this.typeService.getAll()
    }).subscribe({
      next:  ({ emplacements, types }) => { this.emplacements.set(emplacements); this.types.set(types); this.loading.set(false); },
      error: () => { this.error.set('Impossible de charger les données.'); this.loading.set(false); }
    });
  }

  openCreate() {
    this.editing.set(null);
    this.formError.set('');
    this.form.reset();
    this.showForm.set(true);
  }

  openEdit(e: Emplacement) {
    this.editing.set(e);
    this.formError.set('');
    this.form.patchValue({ code: e.code, typeEmplacementId: e.typeEmplacement.id, description: e.description ?? '' });
    this.showForm.set(true);
  }

  closeForm() { this.showForm.set(false); this.saving.set(false); this.form.reset(); this.editing.set(null); }

  submit() {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving.set(true);
    this.formError.set('');
    const val = this.form.value;
    const payload = { code: val.code, typeEmplacementId: +val.typeEmplacementId, description: val.description || undefined };
    const obs = this.editing()
      ? this.emplacementService.update(this.editing()!.id, payload)
      : this.emplacementService.create(payload);

    obs.subscribe({
      next:  () => { this.load(); this.closeForm(); },
      error: err => { this.formError.set(err.error?.message ?? 'Erreur lors de la sauvegarde.'); this.saving.set(false); }
    });
  }

  delete(e: Emplacement) {
    if (!confirm(`Supprimer l'emplacement "${e.code}" ?`)) return;
    this.emplacementService.delete(e.id).subscribe({
      next:  () => this.load(),
      error: () => this.error.set('Suppression impossible (emplacement utilisé dans des stocks ou réceptions).')
    });
  }

  openTypeForm() { this.typeFormError.set(''); this.typeForm.reset(); this.showTypeForm.set(true); }
  closeTypeForm() { this.showTypeForm.set(false); this.saving.set(false); this.typeForm.reset(); }

  submitType() {
    if (this.typeForm.invalid) { this.typeForm.markAllAsTouched(); return; }
    this.saving.set(true);
    this.typeFormError.set('');
    this.typeService.create(this.typeForm.value).subscribe({
      next:  () => { this.load(); this.closeTypeForm(); },
      error: err => { this.typeFormError.set(err.error?.message ?? 'Erreur.'); this.saving.set(false); }
    });
  }
}
