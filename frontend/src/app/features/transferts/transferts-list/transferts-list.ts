import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { forkJoin } from 'rxjs';
import { TransfertService } from '../../../core/services/transfert.service';
import { ArticleService } from '../../../core/services/article.service';
import { EmplacementService } from '../../../core/services/emplacement.service';
import { Transfert } from '../../../core/models/transfert.model';
import { Article } from '../../../core/models/article.model';
import { Emplacement } from '../../../core/models/emplacement.model';

@Component({
  selector: 'app-transferts-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  template: `
    <div class="page-header">
      <h1>Transferts</h1>
      <button class="btn btn-primary" (click)="openCreate()">+ Nouveau transfert</button>
    </div>

    @if (error()) {
      <div class="alert-error">{{ error() }}</div>
    }

    <div class="table-wrap">
      @if (loading()) {
        <div class="state-loading">
          <div class="state-loading-spinner"></div>
          <span>Chargement des transferts…</span>
        </div>
      } @else if (transferts().length === 0) {
        <div class="state-empty">
          <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
          </svg>
          <p class="state-empty-title">Aucun transfert</p>
          <p class="state-empty-sub">Les mouvements de stock entre emplacements apparaîtront ici.</p>
        </div>
      } @else {
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Date</th>
              <th>Article</th>
              <th>Quantité</th>
              <th>Source → Destination</th>
              <th>Utilisateur</th>
            </tr>
          </thead>
          <tbody>
            @for (t of transferts(); track t.id) {
              <tr>
                <td>#{{ t.id }}</td>
                <td>{{ t.dateTransfert | date:'dd/MM/yyyy HH:mm' }}</td>
                <td>
                  <div>{{ t.article.libelle }}</div>
                  <div style="font-size:.75rem;color:#94a3b8"><code>{{ t.article.reference }}</code></div>
                </td>
                <td><strong>{{ t.quantite }}</strong></td>
                <td>
                  <span class="badge badge-orange">{{ t.emplacementSource.code }}</span>
                  <span style="margin:0 .5rem;color:#94a3b8">→</span>
                  <span class="badge badge-green">{{ t.emplacementDestination.code }}</span>
                </td>
                <td>{{ t.utilisateur.email }}</td>
              </tr>
            }
          </tbody>
        </table>
      }
    </div>

    @if (showForm()) {
      <div class="modal-overlay" (click)="closeForm()">
        <div class="modal" (click)="$event.stopPropagation()">
          <h2>Nouveau transfert</h2>
          <form [formGroup]="form" (ngSubmit)="submit()">
            <div class="form-group">
              <label>Article *</label>
              <select formControlName="articleId"
                      [class.invalid]="form.get('articleId')!.invalid && form.get('articleId')!.touched">
                <option value="">— Choisir un article —</option>
                @for (a of articles(); track a.id) {
                  <option [value]="a.id">{{ a.reference }} – {{ a.libelle }}</option>
                }
              </select>
            </div>
            <div class="form-group">
              <label>Emplacement source *</label>
              <select formControlName="emplacementSourceId"
                      [class.invalid]="form.get('emplacementSourceId')!.invalid && form.get('emplacementSourceId')!.touched">
                <option value="">— Source —</option>
                @for (e of emplacements(); track e.id) {
                  <option [value]="e.id">{{ e.code }}</option>
                }
              </select>
            </div>
            <div class="form-group">
              <label>Emplacement destination *</label>
              <select formControlName="emplacementDestinationId"
                      [class.invalid]="form.get('emplacementDestinationId')!.invalid && form.get('emplacementDestinationId')!.touched">
                <option value="">— Destination —</option>
                @for (e of emplacements(); track e.id) {
                  <option [value]="e.id">{{ e.code }}</option>
                }
              </select>
            </div>
            <div class="form-group">
              <label>Quantité *</label>
              <input type="number" formControlName="quantite" min="1"
                     [class.invalid]="form.get('quantite')!.invalid && form.get('quantite')!.touched">
            </div>
            @if (formError()) {
              <div class="alert-error">{{ formError() }}</div>
            }
            <div class="form-actions">
              <button type="button" class="btn btn-secondary" (click)="closeForm()">Annuler</button>
              <button type="submit" class="btn btn-primary" [disabled]="form.invalid || saving()">
                {{ saving() ? 'Transfert en cours…' : 'Transférer' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    }
  `
})
export class TransfertsListComponent implements OnInit {
  transferts   = signal<Transfert[]>([]);
  articles     = signal<Article[]>([]);
  emplacements = signal<Emplacement[]>([]);
  loading      = signal(false);
  error        = signal('');
  formError    = signal('');
  showForm     = signal(false);
  saving       = signal(false);
  form: FormGroup;

  constructor(
    private transfertService: TransfertService,
    private articleService: ArticleService,
    private emplacementService: EmplacementService,
    private fb: FormBuilder
  ) {
    this.form = this.fb.group({
      articleId:               ['', Validators.required],
      emplacementSourceId:     ['', Validators.required],
      emplacementDestinationId: ['', Validators.required],
      quantite:                [1, [Validators.required, Validators.min(1)]]
    });
  }

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.error.set('');
    this.transfertService.getAll().subscribe({
      next:  data => { this.transferts.set(data); this.loading.set(false); },
      error: ()   => { this.error.set('Impossible de charger les transferts.'); this.loading.set(false); }
    });
  }

  openCreate() {
    this.formError.set('');
    this.form.reset({ quantite: 1 });
    forkJoin({ articles: this.articleService.getAll(), emplacements: this.emplacementService.getAll() }).subscribe({
      next:  ({ articles, emplacements }) => { this.articles.set(articles); this.emplacements.set(emplacements); this.showForm.set(true); },
      error: () => this.error.set('Impossible de charger les données du formulaire.')
    });
  }

  closeForm() { this.showForm.set(false); this.saving.set(false); this.form.reset({ quantite: 1 }); }

  submit() {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    const v = this.form.value;
    if (+v.emplacementSourceId === +v.emplacementDestinationId) {
      this.formError.set('La source et la destination doivent être différentes.');
      return;
    }
    this.saving.set(true);
    this.formError.set('');
    this.transfertService.create({
      articleId:                +v.articleId,
      emplacementSourceId:      +v.emplacementSourceId,
      emplacementDestinationId: +v.emplacementDestinationId,
      quantite:                 +v.quantite
    }).subscribe({
      next:  () => { this.load(); this.closeForm(); },
      error: err => { this.formError.set(err.error?.message ?? 'Erreur lors du transfert (stock insuffisant ?).'); this.saving.set(false); }
    });
  }
}
