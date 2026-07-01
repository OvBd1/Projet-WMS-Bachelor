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
import { ConfirmDialogComponent } from '../../../shared/confirm-dialog/confirm-dialog';

@Component({
  selector: 'app-transferts-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, ConfirmDialogComponent],
  templateUrl: './transferts-list.html'
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

  // Garde d'abandon de saisie : demande confirmation si le formulaire a été modifié.
  pendingClose = signal<(() => void) | null>(null);
  tryCloseForm() { if (this.form.dirty) { this.pendingClose.set(() => this.closeForm()); } else { this.closeForm(); } }
  confirmDiscard() { const close = this.pendingClose(); this.pendingClose.set(null); close?.(); }
  cancelDiscard()  { this.pendingClose.set(null); }

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
