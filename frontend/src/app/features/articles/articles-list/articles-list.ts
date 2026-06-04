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
  templateUrl: './articles-list.html',
  styleUrl: './articles-list.css'
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
