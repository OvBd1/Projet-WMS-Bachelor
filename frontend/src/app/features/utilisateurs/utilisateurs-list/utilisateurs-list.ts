import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { UtilisateurService } from '../../../core/services/utilisateur.service';
import { DossierService } from '../../../core/services/dossier.service';
import { User } from '../../../core/models/user.model';
import { Dossier } from '../../../core/models/dossier.model';

@Component({
  selector: 'app-utilisateurs-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './utilisateurs-list.html',
  styleUrl: './utilisateurs-list.css'
})
export class UtilisateursListComponent implements OnInit {
  utilisateurs = signal<User[]>([]);
  dossiers     = signal<Dossier[]>([]);
  loading      = signal(false);
  error        = signal('');
  formError    = signal('');
  showForm     = signal(false);
  saving       = signal(false);
  form: FormGroup;

  constructor(
    private utilisateurService: UtilisateurService,
    private dossierService: DossierService,
    private fb: FormBuilder
  ) {
    this.form = this.fb.group({
      email:     ['', [Validators.required, Validators.email]],
      nom:       ['', [Validators.required, Validators.maxLength(100)]],
      prenom:    ['', [Validators.required, Validators.maxLength(100)]],
      password:  ['', [Validators.required, Validators.minLength(6)]],
      role:      ['ROLE_USER', Validators.required],
      dossierId: ['']
    });
  }

  ngOnInit() {
    this.load();
    this.dossierService.getAll().subscribe({ next: data => this.dossiers.set(data) });
  }

  load() {
    this.loading.set(true);
    this.error.set('');
    this.utilisateurService.getAll().subscribe({
      next:  data => { this.utilisateurs.set(data); this.loading.set(false); },
      error: ()   => { this.error.set('Impossible de charger les utilisateurs.'); this.loading.set(false); }
    });
  }

  openCreate() {
    this.formError.set('');
    this.form.reset({ role: 'ROLE_USER' });
    this.showForm.set(true);
  }

  closeForm() { this.showForm.set(false); this.saving.set(false); }

  isRoleUser(): boolean {
    return this.form.get('role')!.value === 'ROLE_USER';
  }

  submit() {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }

    const v = this.form.value;
    if (v.role === 'ROLE_USER' && !v.dossierId) {
      this.formError.set('Un dossier est requis pour un utilisateur non-admin.');
      return;
    }

    this.saving.set(true);
    this.formError.set('');

    const payload = {
      email:     v.email.trim(),
      nom:       v.nom.trim(),
      prenom:    v.prenom.trim(),
      password:  v.password,
      role:      v.role,
      dossierId: v.role === 'ROLE_USER' ? Number(v.dossierId) : null
    };

    this.utilisateurService.create(payload).subscribe({
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
