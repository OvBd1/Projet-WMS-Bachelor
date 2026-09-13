import { TestBed } from '@angular/core/testing';
import { FormControl, FormGroup } from '@angular/forms';
import { of } from 'rxjs';
import { AdresseAutocompleteComponent } from './adresse-autocomplete';
import { AdresseService } from '../../core/services/adresse.service';

describe('AdresseAutocompleteComponent', () => {
  const suggestion = {
    label: '10 Rue de la Paix 75002 Paris',
    rue: '10 Rue de la Paix',
    codePostal: '75002',
    ville: 'Paris',
    pays: 'France'
  };

  it('remplit les champs d\'adresse et ferme la liste quand on choisit une suggestion', async () => {
    TestBed.configureTestingModule({
      imports: [AdresseAutocompleteComponent],
      providers: [{ provide: AdresseService, useValue: { search: vi.fn(() => of([])) } }]
    });

    const form = new FormGroup({
      rue: new FormControl(''),
      codePostal: new FormControl(''),
      ville: new FormControl(''),
      pays: new FormControl('')
    });
    const fixture = TestBed.createComponent(AdresseAutocompleteComponent);
    fixture.componentRef.setInput('group', form);
    await fixture.whenStable();

    // Ouvre la liste directement, sans passer par le debounce de la saisie.
    const component = fixture.componentInstance as any;
    component.suggestions.set([suggestion]);
    fixture.detectChanges();
    expect(fixture.nativeElement.querySelectorAll('li[role="option"]').length).toBe(1);

    component.select(suggestion);
    fixture.detectChanges();

    expect(form.getRawValue()).toEqual({
      rue: '10 Rue de la Paix',
      codePostal: '75002',
      ville: 'Paris',
      pays: 'France'
    });
    expect(form.dirty).toBe(true);
    expect(component.open()).toBe(false);
    expect(fixture.nativeElement.querySelector('ul')).toBeNull();
  });

  it('ne relance pas de recherche quand on choisit une suggestion', async () => {
    TestBed.configureTestingModule({
      imports: [AdresseAutocompleteComponent],
      providers: [{ provide: AdresseService, useValue: { search: vi.fn(() => of([])) } }]
    });

    const form = new FormGroup({
      rue: new FormControl('10 Rue de la Paix'),
      codePostal: new FormControl(''),
      ville: new FormControl(''),
      pays: new FormControl('')
    });
    const fixture = TestBed.createComponent(AdresseAutocompleteComponent);
    fixture.componentRef.setInput('group', form);
    await fixture.whenStable();

    const emissions: unknown[] = [];
    form.get('rue')!.valueChanges.subscribe(v => emissions.push(v));

    // Même rue que la valeur déjà saisie : cas qui bloquait la recherche suivante.
    (fixture.componentInstance as any).select(suggestion);

    expect(emissions).toEqual([]);
    expect(form.get('codePostal')!.value).toBe('75002');
  });
});
