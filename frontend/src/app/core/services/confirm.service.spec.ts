import { ConfirmService } from './confirm.service';

describe('ConfirmService', () => {
  let service: ConfirmService;

  beforeEach(() => {
    service = new ConfirmService();
  });

  it('expose la demande avec les valeurs par défaut', () => {
    service.ask({ title: 'Titre', message: 'Message' });

    expect(service.request()).toMatchObject({
      title: 'Titre',
      message: 'Message',
      confirmLabel: 'Confirmer',
      variant: 'primary'
    });
  });

  it('résout true quand l\'utilisateur confirme et ferme la modale', async () => {
    const answer = service.ask({ title: 'Supprimer', message: 'Sûr ?', variant: 'danger' });

    service.answer(true);

    await expect(answer).resolves.toBe(true);
    expect(service.request()).toBeNull();
  });

  it('résout false quand l\'utilisateur annule', async () => {
    const answer = service.ask({ title: 'Supprimer', message: 'Sûr ?' });

    service.answer(false);

    await expect(answer).resolves.toBe(false);
  });

  it('refuse la demande précédente si une nouvelle est ouverte', async () => {
    const first  = service.ask({ title: 'Première', message: '…' });
    const second = service.ask({ title: 'Seconde', message: '…' });

    await expect(first).resolves.toBe(false);
    expect(service.request()?.title).toBe('Seconde');

    service.answer(true);
    await expect(second).resolves.toBe(true);
  });
});
