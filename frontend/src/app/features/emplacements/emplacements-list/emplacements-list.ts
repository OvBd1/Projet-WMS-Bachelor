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
  templateUrl: './emplacements-list.html'
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
      next: created => {
        // Recharge la liste des types puis sélectionne automatiquement celui qui vient d'être créé.
        this.typeService.getAll().subscribe({
          next: types => {
            this.types.set(types);
            this.form.patchValue({ typeEmplacementId: String(created.id) });
          }
        });
        this.closeTypeForm();
      },
      error: err => { this.typeFormError.set(err.error?.message ?? 'Erreur.'); this.saving.set(false); }
    });
  }
}
