import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormArray, FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommandeService } from '../../../core/services/commande.service';
import { ArticleService } from '../../../core/services/article.service';
import { Commande, StatutCommande } from '../../../core/models/commande.model';
import { Article } from '../../../core/models/article.model';

const STATUTS: StatutCommande[] = ['EN_ATTENTE', 'PREPAREE', 'EXPEDIEE', 'ANNULEE'];

const BADGE: Record<StatutCommande, string> = {
  EN_ATTENTE: 'badge-orange',
  PREPAREE:   'badge-blue',
  EXPEDIEE:   'badge-green',
  ANNULEE:    'badge-red'
};

@Component({
  selector: 'app-commandes-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  template: `
    <div class="page-header">
      <h1>Commandes</h1>
      <button class="btn btn-primary" (click)="openCreate()">+ Nouvelle commande</button>
    </div>

    @if (error()) {
      <div class="alert-error">{{ error() }}</div>
    }

    <div class="table-wrap">
      @if (loading()) {
        <div class="state-loading">
          <div class="state-loading-spinner"></div>
          <span>Chargement des commandes…</span>
        </div>
      } @else if (commandes().length === 0) {
        <div class="state-empty">
          <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z"/>
          </svg>
          <p class="state-empty-title">Aucune commande</p>
          <p class="state-empty-sub">Aucune commande n'a encore été passée.</p>
        </div>
      } @else {
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Statut</th>
              <th>Utilisateur</th>
              <th>Articles</th>
              <th style="width:200px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @for (c of commandes(); track c.id) {
              <tr>
                <td>#{{ c.id }}</td>
                <td>{{ c.dateCommande | date:'dd/MM/yyyy HH:mm' }}</td>
                <td><span class="badge" [class]="badgeClass(c.statut)">{{ c.statut }}</span></td>
                <td>{{ c.utilisateur.email }}</td>
                <td>
                  @for (l of c.lignes; track l.id) {
                    <div style="font-size:.8rem;color:#475569">
                      {{ l.article.reference }} · {{ l.quantite }} u.
                    </div>
                  }
                </td>
                <td>
                  <div class="btn-group">
                    <button class="btn btn-warning btn-sm" (click)="openStatut(c)">Statut</button>
                    <button class="btn btn-danger btn-sm" (click)="delete(c)">Suppr.</button>
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
        <div class="modal modal-wide" (click)="$event.stopPropagation()">
          <h2>Nouvelle commande</h2>
          <form [formGroup]="form" (ngSubmit)="submit()">
            <div class="lignes-section" formArrayName="lignes">
              <div class="ligne-header">
                <span>Lignes de commande</span>
                <button type="button" class="btn btn-secondary btn-sm" (click)="addLigne()">+ Ligne</button>
              </div>
              @for (lg of lignes.controls; track $index) {
                <div class="ligne-row" [formGroupName]="$index"
                     style="grid-template-columns: 1fr 100px 32px">
                  <div class="form-group" style="margin:0">
                    <label>Article</label>
                    <select formControlName="articleId"
                            [class.invalid]="lg.get('articleId')!.invalid && lg.get('articleId')!.touched">
                      <option value="">— Article —</option>
                      @for (a of articles(); track a.id) {
                        <option [value]="a.id">{{ a.reference }} – {{ a.libelle }}</option>
                      }
                    </select>
                  </div>
                  <div class="form-group" style="margin:0">
                    <label>Quantité</label>
                    <input type="number" formControlName="quantite" min="1"
                           [class.invalid]="lg.get('quantite')!.invalid && lg.get('quantite')!.touched">
                  </div>
                  <button type="button" class="btn btn-danger btn-sm"
                          style="align-self:flex-end;margin-bottom:0"
                          [disabled]="lignes.length <= 1"
                          (click)="removeLigne($index)">✕</button>
                </div>
              }
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

    @if (showStatutForm() && editingCommande()) {
      <div class="modal-overlay" (click)="closeStatutForm()">
        <div class="modal" (click)="$event.stopPropagation()">
          <h2>Changer le statut — Commande #{{ editingCommande()!.id }}</h2>
          <p style="margin-bottom:1rem;color:#475569;font-size:.875rem">
            Statut actuel : <span class="badge" [class]="badgeClass(editingCommande()!.statut)">{{ editingCommande()!.statut }}</span>
          </p>
          <div class="btn-group" style="flex-wrap:wrap;gap:.5rem">
            @for (s of statuts; track s) {
              <button class="btn btn-secondary"
                      [class.btn-primary]="s === editingCommande()!.statut"
                      [disabled]="s === editingCommande()!.statut || saving()"
                      (click)="changeStatut(s)">{{ s }}</button>
            }
          </div>
          @if (formError()) {
            <div class="alert-error" style="margin-top:1rem">{{ formError() }}</div>
          }
          <div class="form-actions">
            <button type="button" class="btn btn-secondary" (click)="closeStatutForm()">Fermer</button>
          </div>
        </div>
      </div>
    }
  `
})
export class CommandesListComponent implements OnInit {
  commandes       = signal<Commande[]>([]);
  articles        = signal<Article[]>([]);
  loading         = signal(false);
  error           = signal('');
  formError       = signal('');
  showForm        = signal(false);
  showStatutForm  = signal(false);
  saving          = signal(false);
  editingCommande = signal<Commande | null>(null);
  form: FormGroup;
  readonly statuts = STATUTS;

  constructor(
    private commandeService: CommandeService,
    private articleService: ArticleService,
    private fb: FormBuilder
  ) {
    this.form = this.fb.group({ lignes: this.fb.array([]) });
  }

  get lignes(): FormArray { return this.form.get('lignes') as FormArray; }

  newLigne() {
    return this.fb.group({
      articleId: ['', Validators.required],
      quantite:  [1, [Validators.required, Validators.min(1)]]
    });
  }

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.error.set('');
    this.commandeService.getAll().subscribe({
      next:  data => { this.commandes.set(data); this.loading.set(false); },
      error: ()   => { this.error.set('Impossible de charger les commandes.'); this.loading.set(false); }
    });
  }

  openCreate() {
    this.formError.set('');
    this.lignes.clear();
    this.addLigne();
    this.articleService.getAll().subscribe({
      next:  data => { this.articles.set(data); this.showForm.set(true); },
      error: () => this.error.set('Impossible de charger les articles.')
    });
  }

  closeForm() { this.showForm.set(false); this.saving.set(false); }

  addLigne() { this.lignes.push(this.newLigne()); }

  removeLigne(i: number) { if (this.lignes.length > 1) this.lignes.removeAt(i); }

  submit() {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving.set(true);
    this.formError.set('');
    const lignes = this.lignes.value.map((l: any) => ({ articleId: +l.articleId, quantite: +l.quantite }));
    this.commandeService.create(lignes).subscribe({
      next:  () => { this.load(); this.closeForm(); },
      error: err => { this.formError.set(err.error?.message ?? 'Erreur lors de l\'enregistrement.'); this.saving.set(false); }
    });
  }

  openStatut(c: Commande) {
    this.editingCommande.set(c);
    this.formError.set('');
    this.showStatutForm.set(true);
  }

  closeStatutForm() { this.showStatutForm.set(false); this.editingCommande.set(null); }

  changeStatut(statut: StatutCommande) {
    const c = this.editingCommande();
    if (!c) return;
    this.saving.set(true);
    this.commandeService.updateStatut(c.id, statut).subscribe({
      next: updated => {
        this.commandes.update(list =>
          list.map(cmd => cmd.id === updated.id ? { ...cmd, statut: updated.statut } : cmd)
        );
        this.editingCommande.update(cmd => cmd ? { ...cmd, statut: updated.statut } : null);
        this.saving.set(false);
        this.closeStatutForm();
      },
      error: err => { this.formError.set(err.error?.message ?? 'Erreur lors du changement de statut.'); this.saving.set(false); }
    });
  }

  delete(c: Commande) {
    if (!confirm(`Supprimer la commande #${c.id} ?`)) return;
    this.commandeService.delete(c.id).subscribe({
      next:  () => this.load(),
      error: () => this.error.set('Suppression impossible.')
    });
  }

  badgeClass(statut: StatutCommande): string { return BADGE[statut]; }
}
