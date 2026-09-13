import { Component, DestroyRef, OnInit, computed, inject, input, signal } from '@angular/core';
import { takeUntilDestroyed } from '@angular/core/rxjs-interop';
import { FormControl, FormGroup, ReactiveFormsModule } from '@angular/forms';
import { debounceTime, distinctUntilChanged, switchMap } from 'rxjs';
import { AdresseService } from '../../core/services/adresse.service';
import { AdresseSuggestion } from '../../core/models/adresse.model';

/**
 * Champ « Rue » avec suggestions de la Base Adresse Nationale.
 *
 * Reçoit le groupe de formulaire qui porte les contrôles rue, codePostal, ville et pays ;
 * choisir une suggestion remplit les quatre champs. Sans suggestion, la saisie reste libre.
 */
@Component({
  selector: 'app-adresse-autocomplete',
  standalone: true,
  imports: [ReactiveFormsModule],
  template: `
    <div class="autocomplete">
      <input type="text" [formControl]="rue()" placeholder="N° et nom de rue — suggestions dès 3 caractères"
             autocomplete="off" role="combobox" aria-autocomplete="list"
             [attr.aria-expanded]="open()"
             (keydown)="onKeydown($event)" (blur)="close()">
      @if (open()) {
        <ul class="autocomplete-list" role="listbox">
          @for (s of suggestions(); track s.label; let i = $index) {
            <li role="option" [class.active]="i === activeIndex()" [attr.aria-selected]="i === activeIndex()"
                (mousedown)="$event.preventDefault(); select(s)">
              {{ s.label }}
            </li>
          }
        </ul>
      }
    </div>
  `
})
export class AdresseAutocompleteComponent implements OnInit {
  group = input.required<FormGroup>();

  protected rue = computed(() => this.group().get('rue') as FormControl<string | null>);
  protected suggestions = signal<AdresseSuggestion[]>([]);
  protected activeIndex = signal(-1);
  protected open = computed(() => this.suggestions().length > 0);

  private adresseService = inject(AdresseService);
  private destroyRef = inject(DestroyRef);

  ngOnInit(): void {
    this.rue().valueChanges.pipe(
      debounceTime(300),
      distinctUntilChanged(),
      switchMap(value => this.adresseService.search(value ?? '')),
      takeUntilDestroyed(this.destroyRef)
    ).subscribe(list => {
      this.suggestions.set(list);
      this.activeIndex.set(-1);
    });
  }

  select(s: AdresseSuggestion): void {
    // Sans émission : choisir une suggestion ne relance pas de recherche.
    this.group().patchValue({ rue: s.rue, codePostal: s.codePostal, ville: s.ville, pays: s.pays }, { emitEvent: false });
    this.group().markAsDirty();
    this.close();
  }

  close(): void {
    this.suggestions.set([]);
    this.activeIndex.set(-1);
  }

  onKeydown(event: KeyboardEvent): void {
    const count = this.suggestions().length;
    if (!count) return;

    if (event.key === 'ArrowDown') {
      event.preventDefault();
      this.activeIndex.update(i => (i + 1) % count);
    } else if (event.key === 'ArrowUp') {
      event.preventDefault();
      this.activeIndex.update(i => (i <= 0 ? count - 1 : i - 1));
    } else if (event.key === 'Enter' && this.activeIndex() >= 0) {
      event.preventDefault();
      this.select(this.suggestions()[this.activeIndex()]);
    } else if (event.key === 'Escape') {
      this.close();
    }
  }
}
