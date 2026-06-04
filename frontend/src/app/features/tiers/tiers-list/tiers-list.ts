import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { TiersService } from '../../../core/services/tiers.service';
import { Tiers } from '../../../core/models/tiers.model';

const TYPES = ['FOURNISSEUR', 'CLIENT', 'AUTRE'] as const;

@Component({
  selector: 'app-tiers-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './tiers-list.html',
  styleUrl: './tiers-list.css'
})
export class TiersListComponent implements OnInit {
  tiers       = signal<Tiers[]>([]);
  loading     = signal(false);
  error       = signal('');
  formError   = signal('');
  showForm    = signal(false);
  saving      = signal(false);
  editingTiers = signal<Tiers | null>(null);
  form: FormGroup;
  readonly types = TYPES;

  constructor(private tiersService: TiersService, private fb: FormBuilder) {
    this.form = this.fb.group({
      code:       ['', [Validators.required, Validators.maxLength(50)]],
      nom:        ['', [Validators.required, Validators.maxLength(255)]],
      type:       ['FOURNISSEUR', Validators.required],
      rue:        [''],
      codePostal: [''],
      ville:      [''],
      pays:       ['']
    });
  }

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.error.set('');
    this.tiersService.getAll().subscribe({
      next:  data => { this.tiers.set(data); this.loading.set(false); },
      error: ()   => { this.error.set('Impossible de charger les tiers.'); this.loading.set(false); }
    });
  }

  openCreate() {
    this.editingTiers.set(null);
    this.formError.set('');
    this.form.reset({ type: 'FOURNISSEUR' });
    this.showForm.set(true);
  }

  openEdit(t: Tiers) {
    this.editingTiers.set(t);
    this.formError.set('');
    this.form.patchValue({
      code:       t.code,
      nom:        t.nom,
      type:       t.type,
      rue:        t.rue ?? '',
      codePostal: t.codePostal ?? '',
      ville:      t.ville ?? '',
      pays:       t.pays ?? ''
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
      code:       v.code.trim(),
      nom:        v.nom.trim(),
      type:       v.type,
      rue:        v.rue || null,
      codePostal: v.codePostal || null,
      ville:      v.ville || null,
      pays:       v.pays || null
    };

    const op = this.editingTiers()
      ? this.tiersService.update(this.editingTiers()!.id, payload)
      : this.tiersService.create(payload);

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

  delete(t: Tiers) {
    if (!confirm(`Supprimer le tiers "${t.nom}" ?`)) return;
    this.tiersService.delete(t.id).subscribe({
      next:  () => this.load(),
      error: () => this.error.set('Suppression impossible (tiers utilisé dans des commandes ou réceptions).')
    });
  }

  badgeClass(type: string): string {
    return type === 'FOURNISSEUR' ? 'badge badge-fournisseur'
         : type === 'CLIENT'      ? 'badge badge-client'
         :                          'badge badge-autre';
  }
}
