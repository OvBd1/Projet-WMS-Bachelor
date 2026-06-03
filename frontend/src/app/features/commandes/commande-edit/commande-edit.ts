import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { CommandeService, CommandePayload } from '../../../core/services/commande.service';
import { ArticleService } from '../../../core/services/article.service';
import { Commande } from '../../../core/models/commande.model';
import { Article } from '../../../core/models/article.model';

interface LineItem {
  _idx: number;
  articleId: number;
  quantite: number;
  articleReference: string;
  articleLibelle: string;
}

@Component({
  selector: 'app-commande-edit',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  template: `
    <div class="page-header">
      <div style="display:flex;align-items:center;gap:12px">
        <a routerLink="/commandes" class="btn btn-secondary btn-sm">← Retour</a>
        <h1 style="margin:0">Modifier la commande #{{ commande()?.id }}</h1>
      </div>
      @if (!loading()) {
        <button class="btn btn-primary" [disabled]="saving()" (click)="save()">
          {{ saving() ? 'Enregistrement…' : 'Enregistrer' }}
        </button>
      }
    </div>

    @if (error()) {
      <div class="alert-error">{{ error() }}</div>
    }

    @if (loading()) {
      <div class="state-loading">
        <div class="state-loading-spinner"></div>
        <span>Chargement…</span>
      </div>
    } @else if (commande()) {
      <div class="tabs-nav">
        <button class="tab-btn" [class.active]="activeTab() === 'entete'" (click)="activeTab.set('entete')">
          En-tête
        </button>
        <button class="tab-btn" [class.active]="activeTab() === 'lignes'" (click)="activeTab.set('lignes')">
          Lignes <span class="tab-count">{{ lines().length }}</span>
        </button>
      </div>

      @if (activeTab() === 'entete') {
        <div class="card" style="max-width:400px;margin-top:20px">
          <form [formGroup]="enteteForm">
            <div class="form-group">
              <label>Date de commande <span style="color:#ef4444">*</span></label>
              <input type="date" formControlName="dateCommande"
                     [class.invalid]="enteteForm.get('dateCommande')!.invalid && enteteForm.get('dateCommande')!.touched">
              @if (enteteForm.get('dateCommande')!.invalid && enteteForm.get('dateCommande')!.touched) {
                <span class="field-error">Date obligatoire</span>
              }
            </div>
          </form>
        </div>
      }

      @if (activeTab() === 'lignes') {
        <div style="margin-top:20px">
          <div style="display:flex;justify-content:flex-end;margin-bottom:12px">
            <button class="btn btn-primary btn-sm" (click)="openAddLigne()">+ Ajouter une ligne</button>
          </div>

          <div class="table-wrap">
            @if (lines().length === 0) {
              <div class="state-empty" style="padding:40px">
                <p class="state-empty-title">Aucune ligne</p>
                <p class="state-empty-sub">Ajoutez au moins une ligne à cette commande.</p>
              </div>
            } @else {
              <table>
                <thead>
                  <tr>
                    <th>Référence</th>
                    <th>Libellé</th>
                    <th>Quantité</th>
                    <th style="width:100px">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @for (l of lines(); track l._idx) {
                    <tr>
                      <td style="font-weight:600">{{ l.articleReference }}</td>
                      <td>{{ l.articleLibelle }}</td>
                      <td>{{ l.quantite }}</td>
                      <td>
                        <div style="display:flex;gap:4px">
                          <button class="btn btn-edit btn-sm" (click)="openEditLigne(l)" title="Modifier">✎</button>
                          <button class="btn btn-danger btn-sm" (click)="removeLigne(l)" title="Supprimer">✕</button>
                        </div>
                      </td>
                    </tr>
                  }
                </tbody>
              </table>
            }
          </div>
        </div>
      }
    }

    @if (showLigneModal()) {
      <div class="modal-overlay" (click)="closeLigneModal()">
        <div class="modal" (click)="$event.stopPropagation()" style="max-width:440px">
          <h2>{{ editingLigneIdx() !== null ? 'Modifier la ligne' : 'Ajouter une ligne' }}</h2>
          <form [formGroup]="ligneForm" (ngSubmit)="saveLigne()">

            <div class="form-group">
              <label>Article <span style="color:#ef4444">*</span></label>
              <select formControlName="articleId"
                      [class.invalid]="ligneForm.get('articleId')!.invalid && ligneForm.get('articleId')!.touched">
                <option value="">— Article —</option>
                @for (a of articles(); track a.id) {
                  <option [value]="a.id">{{ a.reference }} – {{ a.libelle }}</option>
                }
              </select>
            </div>

            <div class="form-group">
              <label>Quantité <span style="color:#ef4444">*</span></label>
              <input type="number" formControlName="quantite" min="1"
                     [class.invalid]="ligneForm.get('quantite')!.invalid && ligneForm.get('quantite')!.touched">
            </div>

            <div class="form-actions">
              <button type="button" class="btn btn-secondary" (click)="closeLigneModal()">Annuler</button>
              <button type="submit" class="btn btn-primary">
                {{ editingLigneIdx() !== null ? 'Mettre à jour' : 'Ajouter' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    }

    <style>
      .tabs-nav { display:flex;gap:4px;border-bottom:2px solid #e2e8f0;margin-top:4px }
      .tab-btn { background:none;border:none;padding:10px 20px;cursor:pointer;font-size:.9rem;color:#64748b;border-bottom:2px solid transparent;margin-bottom:-2px;border-radius:6px 6px 0 0;transition:all .15s }
      .tab-btn:hover { color:#1e293b;background:#f8fafc }
      .tab-btn.active { color:var(--primary);border-bottom-color:var(--primary);font-weight:600 }
      .tab-count { display:inline-flex;align-items:center;justify-content:center;background:#e2e8f0;color:#475569;border-radius:10px;font-size:.7rem;font-weight:700;padding:1px 7px;margin-left:6px }
      .tab-btn.active .tab-count { background:var(--primary);color:#fff }
      .card { background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px }
      .field-error { font-size:.75rem;color:#ef4444;margin-top:4px;display:block }
      .btn-edit { background:#f59e0b;color:#fff }
      .btn-edit:hover { background:#d97706 }
    </style>
  `
})
export class CommandeEditComponent implements OnInit {
  commande  = signal<Commande | null>(null);
  loading   = signal(true);
  saving    = signal(false);
  error     = signal('');
  activeTab = signal<'entete' | 'lignes'>('entete');

  articles = signal<Article[]>([]);
  lines    = signal<LineItem[]>([]);

  showLigneModal  = signal(false);
  editingLigneIdx = signal<number | null>(null);

  enteteForm: FormGroup;
  ligneForm:  FormGroup;

  private articleMap = new Map<number, Article>();
  private nextIdx = 0;

  constructor(
    private route:           ActivatedRoute,
    private router:          Router,
    private commandeService: CommandeService,
    private articleService:  ArticleService,
    private fb:              FormBuilder
  ) {
    this.enteteForm = this.fb.group({
      dateCommande: ['', Validators.required]
    });
    this.ligneForm = this.fb.group({
      articleId: ['', Validators.required],
      quantite:  [1, [Validators.required, Validators.min(1)]]
    });
  }

  ngOnInit() {
    const id = +this.route.snapshot.paramMap.get('id')!;
    Promise.all([
      this.commandeService.getById(id).toPromise(),
      this.articleService.getAll().toPromise()
    ]).then(([commande, articles]) => {
      if (!commande || !articles) { this.error.set('Données introuvables.'); this.loading.set(false); return; }
      if (commande.statut !== 'EN_ATTENTE') { this.router.navigate(['/commandes']); return; }

      this.commande.set(commande);
      this.articles.set(articles);
      articles.forEach(a => this.articleMap.set(a.id, a));

      this.enteteForm.patchValue({
        dateCommande: commande.dateCommande ? commande.dateCommande.split(' ')[0] : ''
      });

      this.lines.set(commande.lignes.map(l => ({
        _idx:             this.nextIdx++,
        articleId:        l.article.id,
        quantite:         l.quantite,
        articleReference: l.article.reference,
        articleLibelle:   l.article.libelle
      })));

      this.loading.set(false);
    }).catch(() => { this.error.set('Impossible de charger la commande.'); this.loading.set(false); });
  }

  openAddLigne() {
    this.editingLigneIdx.set(null);
    this.ligneForm.reset({ articleId: '', quantite: 1 });
    this.showLigneModal.set(true);
  }

  openEditLigne(line: LineItem) {
    this.editingLigneIdx.set(line._idx);
    this.ligneForm.patchValue({ articleId: String(line.articleId), quantite: line.quantite });
    this.showLigneModal.set(true);
  }

  closeLigneModal() { this.showLigneModal.set(false); }

  saveLigne() {
    if (this.ligneForm.invalid) { this.ligneForm.markAllAsTouched(); return; }

    const v         = this.ligneForm.value;
    const articleId = +v.articleId;
    const article   = this.articleMap.get(articleId)!;

    const lineData: Omit<LineItem, '_idx'> = {
      articleId,
      quantite:         +v.quantite,
      articleReference: article.reference,
      articleLibelle:   article.libelle
    };

    const idx = this.editingLigneIdx();
    if (idx === null) {
      this.lines.update(ls => [...ls, { _idx: this.nextIdx++, ...lineData }]);
    } else {
      this.lines.update(ls => ls.map(l => l._idx !== idx ? l : { _idx: idx, ...lineData }));
    }

    this.closeLigneModal();
  }

  removeLigne(line: LineItem) {
    if (!confirm('Supprimer cette ligne ?')) return;
    this.lines.update(ls => ls.filter(l => l._idx !== line._idx));
  }

  save() {
    if (this.enteteForm.invalid) {
      this.activeTab.set('entete');
      this.enteteForm.markAllAsTouched();
      return;
    }
    if (this.lines().length === 0) {
      this.activeTab.set('lignes');
      this.error.set('Ajoutez au moins une ligne à la commande.');
      return;
    }

    this.saving.set(true);
    this.error.set('');

    const payload: CommandePayload = {
      dateCommande: this.enteteForm.value.dateCommande || null,
      lignes: this.lines().map(l => ({ articleId: l.articleId, quantite: l.quantite }))
    };

    this.commandeService.update(this.commande()!.id, payload).subscribe({
      next:  () => this.router.navigate(['/commandes']),
      error: err => { this.error.set(err.error?.message ?? 'Erreur lors de l\'enregistrement.'); this.saving.set(false); }
    });
  }
}
