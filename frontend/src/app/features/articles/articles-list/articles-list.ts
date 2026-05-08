import { Component, OnInit, signal } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormBuilder, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';
import { ArticleService } from '../../../core/services/article.service';
import { TypeConditionnementService } from '../../../core/services/type-conditionnement.service';
import { Article } from '../../../core/models/article.model';
import { TypeConditionnement } from '../../../core/models/type-conditionnement.model';
import { environment } from '../../../../environments/environment';

@Component({
  selector: 'app-articles-list',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  template: `
    <div class="page-header">
      <h1>Articles</h1>
      <button class="btn btn-primary" (click)="openCreate()">+ Nouvel article</button>
    </div>

    @if (error()) {
      <div class="alert-error">{{ error() }}</div>
    }

    <div class="table-wrap">
      @if (loading()) {
        <div class="state-loading">
          <div class="state-loading-spinner"></div>
          <span>Chargement des articles…</span>
        </div>
      } @else if (articles().length === 0) {
        <div class="state-empty">
          <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
          </svg>
          <p class="state-empty-title">Aucun article</p>
          <p class="state-empty-sub">Commencez par créer votre premier article.</p>
        </div>
      } @else {
        <table>
          <thead>
            <tr>
              <th>Image</th>
              <th>Référence</th>
              <th>Libellé</th>
              <th>Conditionnement</th>
              <th>DLC</th>
              <th>N° Série</th>
              <th style="width:120px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @for (a of articles(); track a.id) {
              <tr>
                <td style="width:60px">
                  @if (a.imagePath) {
                    <img [src]="imageUrl(a.imagePath)" alt="Image article"
                         style="width:48px;height:48px;object-fit:cover;border-radius:4px;cursor:pointer"
                         (click)="openImagePreview(a)">
                  } @else {
                    <span style="color:var(--color-text-muted);font-size:0.75rem">—</span>
                  }
                </td>
                <td><code>{{ a.reference }}</code></td>
                <td>{{ a.libelle }}</td>
                <td>{{ a.typeConditionnement?.libelle || '—' }}</td>
                <td>
                  <span [class]="a.gestionDlc ? 'badge badge-success' : 'badge badge-neutral'">
                    {{ a.gestionDlc ? 'Oui' : 'Non' }}
                  </span>
                </td>
                <td>
                  <span [class]="a.gestionNumeroSerie ? 'badge badge-success' : 'badge badge-neutral'">
                    {{ a.gestionNumeroSerie ? 'Oui' : 'Non' }}
                  </span>
                </td>
                <td>
                  <div class="btn-group">
                    <button class="btn btn-secondary btn-sm" (click)="openEdit(a)">Éditer</button>
                    <button class="btn btn-danger btn-sm" (click)="delete(a)">Suppr.</button>
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
        <div class="modal" style="max-width:560px" (click)="$event.stopPropagation()">
          <h2>{{ editing() ? 'Modifier l\'article' : 'Nouvel article' }}</h2>
          <form [formGroup]="form" (ngSubmit)="submit()">

            <div class="form-group">
              <label>Référence *</label>
              <input formControlName="reference" placeholder="REF-001" maxlength="50"
                     [class.invalid]="form.get('reference')!.invalid && form.get('reference')!.touched">
            </div>

            <div class="form-group">
              <label>Libellé *</label>
              <input formControlName="libelle" placeholder="Nom de l'article"
                     [class.invalid]="form.get('libelle')!.invalid && form.get('libelle')!.touched">
            </div>

            <div class="form-group">
              <label>Description</label>
              <textarea formControlName="description" placeholder="Description optionnelle" rows="3"></textarea>
            </div>

            <div class="form-group">
              <label>Type de conditionnement</label>
              <select formControlName="typeConditionnementId">
                <option [ngValue]="null">— Aucun —</option>
                @for (tc of typesConditionnement(); track tc.id) {
                  <option [ngValue]="tc.id">{{ tc.libelle }}</option>
                }
              </select>
            </div>

            <div style="display:flex;gap:2rem;margin-bottom:1rem">
              <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:500">
                <input type="checkbox" formControlName="gestionDlc" style="width:auto;margin:0">
                Gestion DLC
              </label>
              <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;font-weight:500">
                <input type="checkbox" formControlName="gestionNumeroSerie" style="width:auto;margin:0">
                Gestion numéro de série
              </label>
            </div>

            @if (editing()) {
              <div class="form-group">
                <label>Image</label>
                @if (editing()!.imagePath) {
                  <div style="margin-bottom:0.5rem">
                    <img [src]="imageUrl(editing()!.imagePath!)" alt="Image actuelle"
                         style="width:80px;height:80px;object-fit:cover;border-radius:6px;border:1px solid var(--color-border)">
                    <span style="margin-left:0.75rem;font-size:0.85rem;color:var(--color-text-muted)">Image actuelle</span>
                  </div>
                }
                <input type="file" accept="image/jpeg,image/png,image/webp,image/gif"
                       (change)="onFileSelected($event)"
                       style="font-size:0.875rem">
                <span style="font-size:0.75rem;color:var(--color-text-muted)">JPEG, PNG, WebP ou GIF — 5 Mo max</span>
              </div>
            }

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

    @if (previewArticle()) {
      <div class="modal-overlay" (click)="previewArticle.set(null)" style="z-index:1100">
        <img [src]="imageUrl(previewArticle()!.imagePath!)" alt="Aperçu"
             style="max-width:90vw;max-height:90vh;border-radius:8px;box-shadow:0 8px 32px rgba(0,0,0,.4)"
             (click)="$event.stopPropagation()">
      </div>
    }
  `,
  styles: [`
    .badge { display:inline-block; padding:0.2rem 0.55rem; border-radius:9999px; font-size:0.75rem; font-weight:600; }
    .badge-success { background:var(--color-success-bg, #dcfce7); color:var(--color-success, #16a34a); }
    .badge-neutral { background:var(--color-surface-2, #f1f5f9); color:var(--color-text-muted, #94a3b8); }
  `]
})
export class ArticlesListComponent implements OnInit {
  articles             = signal<Article[]>([]);
  typesConditionnement = signal<TypeConditionnement[]>([]);
  loading              = signal(false);
  error                = signal('');
  formError            = signal('');
  showForm             = signal(false);
  saving               = signal(false);
  editing              = signal<Article | null>(null);
  previewArticle       = signal<Article | null>(null);
  selectedFile: File | null = null;
  form: FormGroup;

  private readonly apiBase = environment.apiUrl.replace('/api', '');

  constructor(
    private articleService: ArticleService,
    private typeCondService: TypeConditionnementService,
    private fb: FormBuilder
  ) {
    this.form = this.fb.group({
      reference:             ['', [Validators.required, Validators.maxLength(50)]],
      libelle:               ['', [Validators.required, Validators.maxLength(255)]],
      description:           [''],
      gestionDlc:            [false],
      gestionNumeroSerie:    [false],
      typeConditionnementId: [null],
    });
  }

  ngOnInit() {
    this.load();
    this.typeCondService.getAll().subscribe({
      next: data => this.typesConditionnement.set(data),
    });
  }

  load() {
    this.loading.set(true);
    this.error.set('');
    this.articleService.getAll().subscribe({
      next:  data => { this.articles.set(data); this.loading.set(false); },
      error: ()   => { this.error.set('Impossible de charger les articles.'); this.loading.set(false); }
    });
  }

  openCreate() {
    this.editing.set(null);
    this.formError.set('');
    this.selectedFile = null;
    this.form.reset({ gestionDlc: false, gestionNumeroSerie: false, typeConditionnementId: null });
    this.showForm.set(true);
  }

  openEdit(a: Article) {
    this.editing.set(a);
    this.formError.set('');
    this.selectedFile = null;
    this.form.patchValue({
      reference:             a.reference,
      libelle:               a.libelle,
      description:           a.description ?? '',
      gestionDlc:            a.gestionDlc,
      gestionNumeroSerie:    a.gestionNumeroSerie,
      typeConditionnementId: a.typeConditionnement?.id ?? null,
    });
    this.showForm.set(true);
  }

  closeForm() {
    this.showForm.set(false);
    this.saving.set(false);
    this.selectedFile = null;
    this.form.reset();
    this.editing.set(null);
  }

  onFileSelected(event: Event) {
    const input = event.target as HTMLInputElement;
    this.selectedFile = input.files?.[0] ?? null;
  }

  openImagePreview(a: Article) {
    this.previewArticle.set(a);
  }

  imageUrl(path: string): string {
    return `${this.apiBase}${path}`;
  }

  submit() {
    if (this.form.invalid) { this.form.markAllAsTouched(); return; }
    this.saving.set(true);
    this.formError.set('');
    const val = this.form.value;
    const payload = {
      reference:             val.reference,
      libelle:               val.libelle,
      description:           val.description || null,
      gestionDlc:            val.gestionDlc ?? false,
      gestionNumeroSerie:    val.gestionNumeroSerie ?? false,
      typeConditionnementId: val.typeConditionnementId || null,
    };

    const obs = this.editing()
      ? this.articleService.update(this.editing()!.id, payload)
      : this.articleService.create(payload);

    obs.subscribe({
      next: article => {
        if (this.selectedFile) {
          this.articleService.uploadImage(article.id, this.selectedFile).subscribe({
            next:  () => { this.load(); this.closeForm(); },
            error: () => { this.formError.set('Article sauvegardé, mais l\'upload d\'image a échoué.'); this.saving.set(false); this.load(); }
          });
        } else {
          this.load();
          this.closeForm();
        }
      },
      error: err => { this.formError.set(err.error?.message ?? 'Erreur lors de la sauvegarde.'); this.saving.set(false); }
    });
  }

  delete(a: Article) {
    if (!confirm(`Supprimer l'article "${a.libelle}" ?`)) return;
    this.articleService.delete(a.id).subscribe({
      next:  () => this.load(),
      error: () => this.error.set('Suppression impossible (article lié à des stocks ou commandes).')
    });
  }
}
