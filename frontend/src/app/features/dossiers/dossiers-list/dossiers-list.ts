import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { DossierService } from '../../../core/services/dossier.service';
import { Dossier } from '../../../core/models/dossier.model';

@Component({
  selector: 'app-dossiers-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './dossiers-list.html',
  styleUrl: './dossiers-list.css'
})
export class DossiersListComponent implements OnInit {
  dossiers        = signal<Dossier[]>([]);
  loading         = signal(false);
  error           = signal('');
  formError       = signal('');
  showForm        = signal(false);
  saving          = signal(false);
  editingDossier  = signal<Dossier | null>(null);
  form: FormGroup;

  constructor(private dossierService: DossierService, private fb: FormBuilder) {
    this.form = this.fb.group({
      code:          ['', [Validators.required, Validators.maxLength(50)]],
      raisonSociale: ['', [Validators.required, Validators.maxLength(255)]],
      rue:           [''],
      codePostal:    [''],
      ville:         [''],
      pays:          ['']
    });
  }

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.error.set('');
    this.dossierService.getAll().subscribe({
      next:  data => { this.dossiers.set(data); this.loading.set(false); },
      error: ()   => { this.error.set('Impossible de charger les dossiers.'); this.loading.set(false); }
    });
  }

  openCreate() {
    this.editingDossier.set(null);
    this.formError.set('');
    this.form.reset();
    this.showForm.set(true);
  }

  openEdit(d: Dossier) {
    this.editingDossier.set(d);
    this.formError.set('');
    this.form.patchValue({
      code:          d.code,
      raisonSociale: d.raisonSociale,
      rue:           d.rue ?? '',
      codePostal:    d.codePostal ?? '',
      ville:         d.ville ?? '',
      pays:          d.pays ?? ''
    });
    this.showForm.set(true);
  }

  closeForm() { this.showForm.set(false); this.saving.set(false); }

  submit() {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving.set(true);
    this.formError.set('');

    const v = this.form.value;
    const payload = {
      code:          v.code.trim(),
      raisonSociale: v.raisonSociale.trim(),
      rue:           v.rue || null,
      codePostal:    v.codePostal || null,
      ville:         v.ville || null,
      pays:          v.pays || null
    };

    const op = this.editingDossier()
      ? this.dossierService.update(this.editingDossier()!.id, payload)
      : this.dossierService.create(payload);

    op.subscribe({
      next:  () => { this.load(); this.closeForm(); },
      error: err => {
        const msg = err.error?.errors
          ? Object.values(err.error.errors).join(', ')
          : (err.error?.message ?? 'Erreur lors de l\'enregistrement.');
        this.formError.set(msg);
        this.saving.set(false);
      }
    });
  }
}
