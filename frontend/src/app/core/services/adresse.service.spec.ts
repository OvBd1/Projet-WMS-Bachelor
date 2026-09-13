import { TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { firstValueFrom } from 'rxjs';
import { AdresseService } from './adresse.service';
import { environment } from '../../../environments/environment';

describe('AdresseService', () => {
  const url = `${environment.apiUrl}/adresses/search`;
  let service: AdresseService;
  let httpMock: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()]
    });
    service = TestBed.inject(AdresseService);
    httpMock = TestBed.inject(HttpTestingController);
  });

  afterEach(() => httpMock.verify());

  it('renvoie une liste vide sans appel HTTP pour moins de 3 caractères', async () => {
    await expect(firstValueFrom(service.search('  ab  '))).resolves.toEqual([]);
    httpMock.expectNone(() => true);
  });

  it('interroge la passerelle avec la requête nettoyée et la limite', async () => {
    const suggestion = { label: '8 Rue de Paris 75001 Paris', rue: '8 Rue de Paris', codePostal: '75001', ville: 'Paris', pays: 'France' };
    const result = firstValueFrom(service.search('  8 rue de paris ', 3));

    const req = httpMock.expectOne(r => r.url === url);
    expect(req.request.method).toBe('GET');
    expect(req.request.params.get('q')).toBe('8 rue de paris');
    expect(req.request.params.get('limit')).toBe('3');
    req.flush([suggestion]);

    await expect(result).resolves.toEqual([suggestion]);
  });

  it('renvoie une liste vide en cas d\'erreur HTTP', async () => {
    const result = firstValueFrom(service.search('rue de lyon'));

    httpMock.expectOne(r => r.url === url).flush('boom', { status: 500, statusText: 'Server Error' });

    await expect(result).resolves.toEqual([]);
  });
});
