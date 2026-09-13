import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { TypeConditionnementService } from '../../../core/services/type-conditionnement.service';
import { TypeConditionnement } from '../../../core/models/type-conditionnement.model';
import { ConfirmService } from '../../../core/services/confirm.service';
import { IconComponent } from '../../../shared/icon/icon';

@Component({
  selector: 'app-types-conditionnement-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, IconComponent],
  templateUrl: './types-conditionnement-list.html'
})
export class TypesConditionnementListComponent implements OnInit {
  private confirm = inject(ConfirmService);

  types     = signal<TypeConditionnement[]>([]);
  loading   = signal(false);
  error     = signal('');
  formError = signal('');
  showForm  = signal(false);
  saving    = signal(false);
  editing   = signal<TypeConditionnement | null>(null);
  form: FormGroup;

  constructor(private service: TypeConditionnementService, private fb: FormBuilder) {
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

  openEdit(t: TypeConditionnement) {
    this.editing.set(t);
    this.formError.set('');
    this.form.patchValue({ libelle: t.libelle });
    this.showForm.set(true);
  }

  async cancelForm() {
    if (await this.confirm.confirmDiscard(this.form)) this.closeForm();
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

  async delete(t: TypeConditionnement) {
    const ok = await this.confirm.ask({
      title: 'Supprimer le type de conditionnement',
      message: `Supprimer le type "${t.libelle}" ? Cette action est irréversible.`,
      confirmLabel: 'Supprimer',
      variant: 'danger'
    });
    if (!ok) return;
    this.service.delete(t.id).subscribe({
      next:  () => this.load(),
      error: () => this.error.set('Suppression impossible (type utilisé par des articles).')
    });
  }
}
