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
  templateUrl: './reception-edit.html',
  styleUrl: './reception-edit.css'
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
