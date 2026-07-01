import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormArray, FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { forkJoin } from 'rxjs';
import { ReceptionService, ReceptionPayload } from '../../../core/services/reception.service';
import { ArticleService } from '../../../core/services/article.service';
import { EmplacementService } from '../../../core/services/emplacement.service';
import { TiersService, TiersPayload } from '../../../core/services/tiers.service';
import { Reception } from '../../../core/models/reception.model';
import { Article } from '../../../core/models/article.model';
import { Emplacement } from '../../../core/models/emplacement.model';
import { Tiers } from '../../../core/models/tiers.model';

@Component({
  selector: 'app-receptions-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './receptions-list.html'
})
export class ReceptionsListComponent implements OnInit {
  receptions   = signal<Reception[]>([]);
  articles     = signal<Article[]>([]);
  emplacements = signal<Emplacement[]>([]);
  tiers        = signal<Tiers[]>([]);
  loading      = signal(false);
  error        = signal('');
  formError    = signal('');
  showForm     = signal(false);
  saving       = signal(false);
  showTiersForm  = signal(false);
  savingTiers    = signal(false);
  tiersFormError = signal('');
  form: FormGroup;
  tiersForm: FormGroup;

  private articleMap = new Map<number, Article>();

  constructor(
    private receptionService: ReceptionService,
    private articleService: ArticleService,
    private emplacementService: EmplacementService,
    private tiersService: TiersService,
    private fb: FormBuilder
  ) {
    this.form = this.fb.group({
      tiersId:       [''],
      dateReception: [new Date().toISOString().split('T')[0]],
      lignes:        this.fb.array([])
    });
    this.tiersForm = this.fb.group({
      nom:       ['', [Validators.required, Validators.maxLength(255)]],
      type:      ['FOURNISSEUR', Validators.required],
      email:     ['', [Validators.email, Validators.maxLength(255)]],
      telephone: ['', Validators.maxLength(30)],
      adresse:   ['']
    });
  }

  get lignes(): FormArray { return this.form.get('lignes') as FormArray; }

  newLigne() {
    return this.fb.group({
      articleId:     ['', Validators.required],
      emplacementId: ['', Validators.required],
      quantite:      [1, [Validators.required, Validators.min(1)]],
      dlc:           [''],
      numeroSerie:   ['']
    });
  }

  ngOnInit() { this.load(); }

  load() {
    this.loading.set(true);
    this.error.set('');
    this.receptionService.getAll().subscribe({
      next:  data => { this.receptions.set(data); this.loading.set(false); },
      error: ()   => { this.error.set('Impossible de charger les réceptions.'); this.loading.set(false); }
    });
  }

  openCreate() {
    this.formError.set('');
    this.lignes.clear();
    this.addLigne();
    this.form.patchValue({ tiersId: '', dateReception: new Date().toISOString().split('T')[0] });

    forkJoin({
      articles:     this.articleService.getAll(),
      emplacements: this.emplacementService.getAll(),
      tiers:        this.tiersService.getAll()
    }).subscribe({
      next: ({ articles, emplacements, tiers }) => {
        this.articles.set(articles);
        this.emplacements.set(emplacements);
        this.tiers.set(tiers);
        this.articleMap.clear();
        articles.forEach(a => this.articleMap.set(a.id, a));
        this.showForm.set(true);
      },
      error: () => this.error.set('Impossible de charger les données du formulaire.')
    });
  }

  closeForm() { this.showForm.set(false); this.saving.set(false); }

  openTiersForm() {
    this.tiersFormError.set('');
    this.tiersForm.reset({ nom: '', type: 'FOURNISSEUR', email: '', telephone: '', adresse: '' });
    this.showTiersForm.set(true);
  }

  closeTiersForm() {
    this.showTiersForm.set(false);
    this.savingTiers.set(false);
    this.tiersForm.reset({ nom: '', type: 'FOURNISSEUR', email: '', telephone: '', adresse: '' });
  }

  submitTiers() {
    if (this.tiersForm.invalid) { this.tiersForm.markAllAsTouched(); return; }
    this.savingTiers.set(true);
    this.tiersFormError.set('');
    const v = this.tiersForm.value;
    const payload: TiersPayload = {
      nom:       v.nom,
      type:      v.type,
      email:     v.email || null,
      telephone: v.telephone || null,
      adresse:   v.adresse || null
    };
    this.tiersService.create(payload).subscribe({
      next: created => {
        // Recharge la liste puis sélectionne automatiquement le tiers créé.
        this.tiersService.getAll().subscribe({
          next: data => {
            this.tiers.set(data);
            this.form.patchValue({ tiersId: String(created.id) });
          }
        });
        this.closeTiersForm();
      },
      error: err => { this.tiersFormError.set(err.error?.message ?? 'Erreur lors de la création du tiers.'); this.savingTiers.set(false); }
    });
  }

  addLigne() { this.lignes.push(this.newLigne()); }

  removeLigne(i: number) { if (this.lignes.length > 1) this.lignes.removeAt(i); }

  onArticleChange(index: number) {
    const lg = this.lignes.at(index);
    const articleId = +lg.get('articleId')!.value;
    const article = this.articleMap.get(articleId);

    const dlcCtrl = lg.get('dlc')!;
    const nsCtrl  = lg.get('numeroSerie')!;

    if (article?.gestionDlc) {
      dlcCtrl.setValidators([Validators.required]);
    } else {
      dlcCtrl.clearValidators();
      dlcCtrl.setValue('');
    }

    if (article?.gestionNumeroSerie) {
      nsCtrl.setValidators([Validators.required]);
    } else {
      nsCtrl.clearValidators();
      nsCtrl.setValue('');
    }

    dlcCtrl.updateValueAndValidity();
    nsCtrl.updateValueAndValidity();
  }

  getArticleFlags(index: number): { gestionDlc: boolean; gestionNumeroSerie: boolean } {
    const articleId = +this.lignes.at(index).get('articleId')!.value;
    const article = this.articleMap.get(articleId);
    return { gestionDlc: article?.gestionDlc ?? false, gestionNumeroSerie: article?.gestionNumeroSerie ?? false };
  }

  submit() {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving.set(true);
    this.formError.set('');

    const v = this.form.value;
    const payload: ReceptionPayload = {
      tiersId:       v.tiersId ? +v.tiersId : null,
      dateReception: v.dateReception || null,
      lignes: this.lignes.value.map((l: any) => ({
        articleId:     +l.articleId,
        emplacementId: +l.emplacementId,
        quantite:      +l.quantite,
        dlc:           l.dlc || null,
        numeroSerie:   l.numeroSerie || null
      }))
    };

    this.receptionService.create(payload).subscribe({
      next:  () => { this.load(); this.closeForm(); },
      error: err => { this.formError.set(err.error?.message ?? 'Erreur lors de l\'enregistrement.'); this.saving.set(false); }
    });
  }

  valider(r: Reception) {
    if (!confirm(`Valider la réception #${r.id} ? Le stock sera mis à jour.`)) return;
    this.receptionService.valider(r.id).subscribe({
      next:  () => this.load(),
      error: err => this.error.set(err.error?.message ?? 'Validation impossible.')
    });
  }

  annuler(r: Reception) {
    if (!confirm(`Annuler la réception #${r.id} ?`)) return;
    this.receptionService.annuler(r.id).subscribe({
      next:  () => this.load(),
      error: err => this.error.set(err.error?.message ?? 'Annulation impossible.')
    });
  }

  delete(r: Reception) {
    if (!confirm(`Supprimer la réception #${r.id} ?`)) return;
    this.receptionService.delete(r.id).subscribe({
      next:  () => this.load(),
      error: err => this.error.set(err.error?.message ?? 'Suppression impossible.')
    });
  }

  badgeClass(statut: string): string {
    return statut === 'EN_ATTENTE' ? 'badge badge-warning'
         : statut === 'VALIDEE'    ? 'badge badge-success'
         :                           'badge badge-danger';
  }
}
