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
  templateUrl: './commandes-list.html'
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
