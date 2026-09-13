import { Component, OnInit, inject, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormArray, FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { RouterModule } from '@angular/router';
import { forkJoin } from 'rxjs';
import { ReceptionService, ReceptionPayload } from '../../../core/services/reception.service';
import { ArticleService } from '../../../core/services/article.service';
import { EmplacementService } from '../../../core/services/emplacement.service';
import { TiersService } from '../../../core/services/tiers.service';
import { Reception } from '../../../core/models/reception.model';
import { Article } from '../../../core/models/article.model';
import { Emplacement } from '../../../core/models/emplacement.model';
import { Tiers } from '../../../core/models/tiers.model';
import { ConfirmService } from '../../../core/services/confirm.service';

@Component({
  selector: 'app-receptions-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, RouterModule],
  templateUrl: './receptions-list.html',
  styleUrl: './receptions-list.css'
})
export class ReceptionsListComponent implements OnInit {
  private confirm = inject(ConfirmService);

  receptions   = signal<Reception[]>([]);
  articles     = signal<Article[]>([]);
  emplacements = signal<Emplacement[]>([]);
  tiers        = signal<Tiers[]>([]);
  loading      = signal(false);
  error        = signal('');
  formError    = signal('');
  showForm     = signal(false);
  saving       = signal(false);
  form: FormGroup;

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

  async cancelForm() {
    if (await this.confirm.confirmDiscard(this.form)) this.closeForm();
  }

  closeForm() { this.showForm.set(false); this.saving.set(false); }

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

  async valider(r: Reception) {
    const ok = await this.confirm.ask({
      title: 'Valider la réception',
      message: `Valider la réception #${r.id} ? Le stock sera mis à jour.`,
      confirmLabel: 'Valider',
      variant: 'success'
    });
    if (!ok) return;
    this.receptionService.valider(r.id).subscribe({
      next:  () => this.load(),
      error: err => this.error.set(err.error?.message ?? 'Validation impossible.')
    });
  }

  async annuler(r: Reception) {
    const ok = await this.confirm.ask({
      title: 'Annuler la réception',
      message: r.statut === 'VALIDEE'
        ? `Annuler la réception #${r.id} VALIDÉE ? Le stock sera décrémenté en conséquence.`
        : `Annuler la réception #${r.id} ?`,
      confirmLabel: 'Annuler la réception',
      variant: 'warning'
    });
    if (!ok) return;
    this.receptionService.annuler(r.id).subscribe({
      next:  () => this.load(),
      error: err => this.error.set(err.error?.message ?? 'Annulation impossible.')
    });
  }

  async delete(r: Reception) {
    const ok = await this.confirm.ask({
      title: 'Supprimer la réception',
      message: `Supprimer la réception #${r.id} ? Cette action est irréversible.`,
      confirmLabel: 'Supprimer',
      variant: 'danger'
    });
    if (!ok) return;
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
