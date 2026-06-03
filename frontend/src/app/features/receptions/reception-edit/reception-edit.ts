import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { forkJoin } from 'rxjs';
import { ReceptionService, ReceptionPayload } from '../../../core/services/reception.service';
import { ArticleService } from '../../../core/services/article.service';
import { EmplacementService } from '../../../core/services/emplacement.service';
import { TiersService } from '../../../core/services/tiers.service';
import { Reception } from '../../../core/models/reception.model';
import { Article } from '../../../core/models/article.model';
import { Emplacement } from '../../../core/models/emplacement.model';
import { Tiers } from '../../../core/models/tiers.model';

interface LineItem {
  _idx: number;
  articleId: number;
  emplacementId: number;
  quantite: number;
  dlc: string | null;
  numeroSerie: string | null;
  articleReference: string;
  articleLibelle: string;
  emplacementCode: string;
  gestionDlc: boolean;
  gestionNumeroSerie: boolean;
}

@Component({
  selector: 'app-reception-edit',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  template: `
    <div class="page-header">
      <div style="display:flex;align-items:center;gap:12px">
        <a [routerLink]="['/receptions', reception()?.id]" class="btn btn-secondary btn-sm">← Retour</a>
        <h1 style="margin:0">Modifier la réception #{{ reception()?.id }}</h1>
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
    } @else if (reception()) {
      <div class="tabs-nav">
        <button class="tab-btn" [class.active]="activeTab() === 'entete'" (click)="activeTab.set('entete')">
          En-tête
        </button>
        <button class="tab-btn" [class.active]="activeTab() === 'lignes'" (click)="activeTab.set('lignes')">
          Lignes <span class="tab-count">{{ lines().length }}</span>
        </button>
      </div>

      @if (activeTab() === 'entete') {
        <div class="card" style="max-width:560px;margin-top:20px">
          <form [formGroup]="enteteForm">
            <div class="form-group">
              <label>Fournisseur / Tiers</label>
              <select formControlName="tiersId">
                <option value="">— Aucun tiers —</option>
                @for (t of tiers(); track t.id) {
                  <option [value]="t.id">{{ t.nom }} ({{ t.type }})</option>
                }
              </select>
            </div>
            <div class="form-group">
              <label>Date de réception <span style="color:#ef4444">*</span></label>
              <input type="date" formControlName="dateReception"
                     [class.invalid]="enteteForm.get('dateReception')!.invalid && enteteForm.get('dateReception')!.touched">
              @if (enteteForm.get('dateReception')!.invalid && enteteForm.get('dateReception')!.touched) {
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
                <p class="state-empty-sub">Ajoutez au moins une ligne à cette réception.</p>
              </div>
            } @else {
              <table>
                <thead>
                  <tr>
                    <th>Référence</th>
                    <th>Libellé</th>
                    <th>Emplacement</th>
                    <th>Quantité</th>
                    <th>DLC</th>
                    <th>N° série</th>
                    <th style="width:100px">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @for (l of lines(); track l._idx) {
                    <tr>
                      <td style="font-weight:600">{{ l.articleReference }}</td>
                      <td>{{ l.articleLibelle }}</td>
                      <td>
                        <span style="background:#f1f5f9;padding:2px 8px;border-radius:4px;font-family:monospace">{{ l.emplacementCode }}</span>
                      </td>
                      <td>{{ l.quantite }}</td>
                      <td>
                        @if (l.gestionDlc) {
                          @if (l.dlc) {
                            <span [class]="dlcClass(l.dlc)">{{ l.dlc | date:'dd/MM/yyyy' }}</span>
                          } @else {
                            <span style="color:#ef4444;font-size:.75rem">Non renseignée</span>
                          }
                        } @else {
                          <span style="color:#94a3b8">—</span>
                        }
                      </td>
                      <td>
                        @if (l.gestionNumeroSerie) {
                          @if (l.numeroSerie) {
                            <span style="font-family:monospace;font-size:.85rem">{{ l.numeroSerie }}</span>
                          } @else {
                            <span style="color:#ef4444;font-size:.75rem">Non renseigné</span>
                          }
                        } @else {
                          <span style="color:#94a3b8">—</span>
                        }
                      </td>
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
        <div class="modal" (click)="$event.stopPropagation()" style="max-width:520px">
          <h2>{{ editingLigneIdx() !== null ? 'Modifier la ligne' : 'Ajouter une ligne' }}</h2>
          <form [formGroup]="ligneForm" (ngSubmit)="saveLigne()">

            <div class="form-group">
              <label>Article <span style="color:#ef4444">*</span></label>
              <select formControlName="articleId" (change)="onLigneArticleChange()"
                      [class.invalid]="ligneForm.get('articleId')!.invalid && ligneForm.get('articleId')!.touched">
                <option value="">— Article —</option>
                @for (a of articles(); track a.id) {
                  <option [value]="a.id">{{ a.reference }} – {{ a.libelle }}</option>
                }
              </select>
            </div>

            <div class="form-group">
              <label>Emplacement <span style="color:#ef4444">*</span></label>
              <select formControlName="emplacementId"
                      [class.invalid]="ligneForm.get('emplacementId')!.invalid && ligneForm.get('emplacementId')!.touched">
                <option value="">— Emplacement —</option>
                @for (e of emplacements(); track e.id) {
                  <option [value]="e.id">{{ e.code }}</option>
                }
              </select>
            </div>

            <div class="form-group">
              <label>Quantité <span style="color:#ef4444">*</span></label>
              <input type="number" formControlName="quantite" min="1"
                     [class.invalid]="ligneForm.get('quantite')!.invalid && ligneForm.get('quantite')!.touched">
            </div>

            @if (getLigneArticleFlags().gestionDlc || getLigneArticleFlags().gestionNumeroSerie) {
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                @if (getLigneArticleFlags().gestionDlc) {
                  <div class="form-group" style="margin:0">
                    <label>DLC <span style="color:#ef4444">*</span></label>
                    <input type="date" formControlName="dlc"
                           [class.invalid]="ligneForm.get('dlc')!.invalid && ligneForm.get('dlc')!.touched">
                  </div>
                }
                @if (getLigneArticleFlags().gestionNumeroSerie) {
                  <div class="form-group" style="margin:0">
                    <label>N° série <span style="color:#ef4444">*</span></label>
                    <input type="text" formControlName="numeroSerie" placeholder="N° série"
                           [class.invalid]="ligneForm.get('numeroSerie')!.invalid && ligneForm.get('numeroSerie')!.touched">
                  </div>
                }
              </div>
            }

            @if (ligneFormError()) {
              <div class="alert-error" style="margin-top:12px">{{ ligneFormError() }}</div>
            }
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
      .dlc-ok      { color:#059669;font-weight:500 }
      .dlc-warning { color:#d97706;font-weight:500 }
      .dlc-expired { color:#ef4444;font-weight:500 }
    </style>
  `
})
export class ReceptionEditComponent implements OnInit {
  reception    = signal<Reception | null>(null);
  loading      = signal(true);
  saving       = signal(false);
  error        = signal('');
  activeTab    = signal<'entete' | 'lignes'>('entete');

  articles     = signal<Article[]>([]);
  emplacements = signal<Emplacement[]>([]);
  tiers        = signal<Tiers[]>([]);
  lines        = signal<LineItem[]>([]);

  showLigneModal  = signal(false);
  editingLigneIdx = signal<number | null>(null);
  ligneFormError  = signal('');

  enteteForm: FormGroup;
  ligneForm:  FormGroup;

  private articleMap     = new Map<number, Article>();
  private emplacementMap = new Map<number, Emplacement>();
  private nextIdx = 0;

  constructor(
    private route:            ActivatedRoute,
    private router:           Router,
    private receptionService: ReceptionService,
    private articleService:   ArticleService,
    private emplacementService: EmplacementService,
    private tiersService:     TiersService,
    private fb:               FormBuilder
  ) {
    this.enteteForm = this.fb.group({
      tiersId:       [''],
      dateReception: ['', Validators.required]
    });
    this.ligneForm = this.fb.group({
      articleId:     ['', Validators.required],
      emplacementId: ['', Validators.required],
      quantite:      [1, [Validators.required, Validators.min(1)]],
      dlc:           [''],
      numeroSerie:   ['']
    });
  }

  ngOnInit() {
    const id = +this.route.snapshot.paramMap.get('id')!;
    forkJoin({
      reception:    this.receptionService.getById(id),
      articles:     this.articleService.getAll(),
      emplacements: this.emplacementService.getAll(),
      tiers:        this.tiersService.getAll()
    }).subscribe({
      next: ({ reception, articles, emplacements, tiers }) => {
        if (reception.statut !== 'EN_ATTENTE') {
          this.router.navigate(['/receptions', id]);
          return;
        }
        this.reception.set(reception);
        this.articles.set(articles);
        this.emplacements.set(emplacements);
        this.tiers.set(tiers);
        articles.forEach(a => this.articleMap.set(a.id, a));
        emplacements.forEach(e => this.emplacementMap.set(e.id, e));

        this.enteteForm.patchValue({
          tiersId:       reception.tiers?.id ?? '',
          dateReception: reception.dateReception ? reception.dateReception.split(' ')[0] : ''
        });

        this.lines.set(reception.lignes.map(l => ({
          _idx:               this.nextIdx++,
          articleId:          l.article.id,
          emplacementId:      l.emplacement.id,
          quantite:           l.quantite,
          dlc:                l.dlc,
          numeroSerie:        l.numeroSerie,
          articleReference:   l.article.reference,
          articleLibelle:     l.article.libelle,
          emplacementCode:    l.emplacement.code,
          gestionDlc:         l.article.gestionDlc,
          gestionNumeroSerie: l.article.gestionNumeroSerie
        })));

        this.loading.set(false);
      },
      error: () => { this.error.set('Impossible de charger la réception.'); this.loading.set(false); }
    });
  }

  openAddLigne() {
    this.editingLigneIdx.set(null);
    this.ligneFormError.set('');
    this.ligneForm.reset({ articleId: '', emplacementId: '', quantite: 1, dlc: '', numeroSerie: '' });
    this.clearLigneValidators();
    this.showLigneModal.set(true);
  }

  openEditLigne(line: LineItem) {
    this.editingLigneIdx.set(line._idx);
    this.ligneFormError.set('');
    this.ligneForm.patchValue({
      articleId:     String(line.articleId),
      emplacementId: String(line.emplacementId),
      quantite:      line.quantite,
      dlc:           line.dlc ?? '',
      numeroSerie:   line.numeroSerie ?? ''
    });
    this.updateLigneValidators(line.articleId);
    this.showLigneModal.set(true);
  }

  closeLigneModal() { this.showLigneModal.set(false); }

  onLigneArticleChange() {
    this.updateLigneValidators(+this.ligneForm.get('articleId')!.value);
  }

  getLigneArticleFlags(): { gestionDlc: boolean; gestionNumeroSerie: boolean } {
    const article = this.articleMap.get(+this.ligneForm.get('articleId')!.value);
    return { gestionDlc: article?.gestionDlc ?? false, gestionNumeroSerie: article?.gestionNumeroSerie ?? false };
  }

  saveLigne() {
    if (this.ligneForm.invalid) { this.ligneForm.markAllAsTouched(); return; }

    const v           = this.ligneForm.value;
    const articleId   = +v.articleId;
    const emplacementId = +v.emplacementId;
    const article     = this.articleMap.get(articleId)!;
    const emplacement = this.emplacementMap.get(emplacementId)!;

    const lineData: Omit<LineItem, '_idx'> = {
      articleId,
      emplacementId,
      quantite:           +v.quantite,
      dlc:                v.dlc || null,
      numeroSerie:        v.numeroSerie || null,
      articleReference:   article.reference,
      articleLibelle:     article.libelle,
      emplacementCode:    emplacement.code,
      gestionDlc:         article.gestionDlc,
      gestionNumeroSerie: article.gestionNumeroSerie
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
      this.error.set('Ajoutez au moins une ligne à la réception.');
      return;
    }

    this.saving.set(true);
    this.error.set('');

    const h = this.enteteForm.value;
    const payload: ReceptionPayload = {
      tiersId:       h.tiersId ? +h.tiersId : null,
      dateReception: h.dateReception || null,
      lignes: this.lines().map(l => ({
        articleId:     l.articleId,
        emplacementId: l.emplacementId,
        quantite:      l.quantite,
        dlc:           l.dlc,
        numeroSerie:   l.numeroSerie
      }))
    };

    this.receptionService.update(this.reception()!.id, payload).subscribe({
      next:  () => this.router.navigate(['/receptions', this.reception()!.id]),
      error: err => { this.error.set(err.error?.message ?? 'Erreur lors de l\'enregistrement.'); this.saving.set(false); }
    });
  }

  dlcClass(dlc: string): string {
    const diff = (new Date(dlc).getTime() - Date.now()) / 86400000;
    return diff < 0 ? 'dlc-expired' : diff < 30 ? 'dlc-warning' : 'dlc-ok';
  }

  private clearLigneValidators() {
    ['dlc', 'numeroSerie'].forEach(f => {
      this.ligneForm.get(f)!.clearValidators();
      this.ligneForm.get(f)!.setValue('');
      this.ligneForm.get(f)!.updateValueAndValidity();
    });
  }

  private updateLigneValidators(articleId: number) {
    const article = this.articleMap.get(articleId);
    const dlcCtrl = this.ligneForm.get('dlc')!;
    const nsCtrl  = this.ligneForm.get('numeroSerie')!;
    article?.gestionDlc           ? dlcCtrl.setValidators([Validators.required]) : dlcCtrl.clearValidators();
    article?.gestionNumeroSerie   ? nsCtrl.setValidators([Validators.required])  : nsCtrl.clearValidators();
    dlcCtrl.updateValueAndValidity();
    nsCtrl.updateValueAndValidity();
  }
}
