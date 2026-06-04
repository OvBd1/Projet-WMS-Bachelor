import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ActivatedRoute, Router, RouterModule } from '@angular/router';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { forkJoin } from 'rxjs';
import { CommandeService, CommandePayload } from '../../../core/services/commande.service';
import { ArticleService } from '../../../core/services/article.service';
import { TiersService } from '../../../core/services/tiers.service';
import { Commande, StatutCommande } from '../../../core/models/commande.model';
import { Article } from '../../../core/models/article.model';
import { Tiers } from '../../../core/models/tiers.model';

interface LineItem {
  _idx: number;
  articleId: number;
  quantite: number;
  articleReference: string;
  articleLibelle: string;
}

const BADGE: Record<StatutCommande, string> = {
  EN_ATTENTE: 'badge-orange',
  PREPAREE:   'badge-blue',
  EXPEDIEE:   'badge-green',
  ANNULEE:    'badge-red'
};

@Component({
  selector: 'app-commande-edit',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './commande-edit.html',
  styleUrl: './commande-edit.css'
})
export class CommandeEditComponent implements OnInit {
  commande  = signal<Commande | null>(null);
  loading   = signal(true);
  saving    = signal(false);
  error     = signal('');
  formError = signal('');
  activeTab = signal<'entete' | 'lignes'>('entete');

  articles = signal<Article[]>([]);
  tiers    = signal<Tiers[]>([]);
  lines    = signal<LineItem[]>([]);

  showLigneModal  = signal(false);
  showStatutModal = signal(false);
  editingLigneIdx = signal<number | null>(null);

  readonly statuts: StatutCommande[] = ['EN_ATTENTE', 'PREPAREE', 'EXPEDIEE', 'ANNULEE'];

  enteteForm: FormGroup;
  ligneForm:  FormGroup;

  private articleMap = new Map<number, Article>();
  private nextIdx = 0;

  constructor(
    private route:           ActivatedRoute,
    private router:          Router,
    private commandeService: CommandeService,
    private articleService:  ArticleService,
    private tiersService:    TiersService,
    private fb:              FormBuilder
  ) {
    this.enteteForm = this.fb.group({
      dateCommande: ['', Validators.required],
      tiersId:      ['']
    });
    this.ligneForm = this.fb.group({
      articleId: ['', Validators.required],
      quantite:  [1, [Validators.required, Validators.min(1)]]
    });
  }

  ngOnInit() {
    const id = +this.route.snapshot.paramMap.get('id')!;
    forkJoin({
      commande: this.commandeService.getById(id),
      articles: this.articleService.getAll(),
      tiers:    this.tiersService.getAll()
    }).subscribe({
      next: ({ commande, articles, tiers }) => {
        this.commande.set(commande);
        this.articles.set(articles);
        this.tiers.set(tiers);
        articles.forEach(a => this.articleMap.set(a.id, a));

        this.enteteForm.patchValue({
          dateCommande: commande.dateCommande ? commande.dateCommande.split(' ')[0] : '',
          tiersId:      commande.tiers?.id ?? ''
        });

        this.lines.set((commande.lignes ?? []).map(l => ({
          _idx:             this.nextIdx++,
          articleId:        l.article.id,
          quantite:         l.quantite,
          articleReference: l.article.reference,
          articleLibelle:   l.article.libelle
        })));

        this.loading.set(false);
      },
      error: () => { this.error.set('Impossible de charger la commande.'); this.loading.set(false); }
    });
  }

  badgeClass(statut: StatutCommande): string { return BADGE[statut]; }

  openStatutModal() { this.formError.set(''); this.showStatutModal.set(true); }
  closeStatutModal() { this.showStatutModal.set(false); }

  changeStatut(statut: StatutCommande) {
    this.saving.set(true);
    this.commandeService.updateStatut(this.commande()!.id, statut).subscribe({
      next: updated => {
        this.commande.update(c => c ? { ...c, statut: updated.statut } : null);
        this.saving.set(false);
        this.closeStatutModal();
      },
      error: err => { this.formError.set(err.error?.message ?? 'Erreur lors du changement de statut.'); this.saving.set(false); }
    });
  }

  delete() {
    if (!confirm(`Supprimer la commande #${this.commande()!.id} ?`)) return;
    this.commandeService.delete(this.commande()!.id).subscribe({
      next:  () => this.router.navigate(['/commandes']),
      error: () => this.error.set('Suppression impossible.')
    });
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

    const v = this.enteteForm.value;
    const payload: CommandePayload = {
      dateCommande: v.dateCommande || null,
      tiersId:      v.tiersId ? +v.tiersId : null,
      lignes:       this.lines().map(l => ({ articleId: l.articleId, quantite: l.quantite }))
    };

    this.commandeService.update(this.commande()!.id, payload).subscribe({
      next:  () => this.router.navigate(['/commandes']),
      error: err => { this.error.set(err.error?.message ?? 'Erreur lors de l\'enregistrement.'); this.saving.set(false); }
    });
  }
}
