<?php

namespace App\Tests\Functional;

final class CommandeTest extends ApiTestCase
{
    private string $token;
    private int $dossierId;
    private int $articleId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dossierId = $this->createDossier('D1');
        $this->createUser('admin@test.fr', 'ROLE_ADMIN');
        $this->token     = $this->login('admin@test.fr');
        $this->articleId = $this->createArticle($this->token, $this->dossierId, 'ART-1');
    }

    private function lignes(int $quantite = 3): array
    {
        return [['articleId' => $this->articleId, 'quantite' => $quantite]];
    }

    public function testUnNumeroDeCommandeLisibleEstAttribueAutomatiquement(): void
    {
        $response = $this->api('POST', '/api/commandes', ['lignes' => $this->lignes()], $this->token, $this->dossierId);

        self::assertSame(201, $response->getStatusCode(), (string) $response->getContent());
        $commande = $this->decode($response);
        self::assertSame(sprintf('CMD-%05d', $commande['id']), $commande['numeroCommande']);
        self::assertSame('EN_ATTENTE', $commande['statut']);
    }

    public function testLaDateDExpeditionEstModifiableEtEffacable(): void
    {
        $commande = $this->decode($this->api('POST', '/api/commandes', [
            'dateExpedition' => '2026-10-01',
            'lignes'         => $this->lignes(),
        ], $this->token, $this->dossierId));
        self::assertSame('2026-10-01', $commande['dateExpedition']);

        $modifiee = $this->decode($this->api('PUT', '/api/commandes/' . $commande['id'], [
            'dateCommande'   => '2026-09-13',
            'dateExpedition' => '2026-10-15',
            'lignes'         => $this->lignes(5),
        ], $this->token, $this->dossierId));
        self::assertSame('2026-10-15', $modifiee['dateExpedition']);
        self::assertSame($commande['numeroCommande'], $modifiee['numeroCommande'], 'Le numéro ne change pas.');
        self::assertSame(5, $modifiee['lignes'][0]['quantite']);

        $effacee = $this->decode($this->api('PUT', '/api/commandes/' . $commande['id'], [
            'dateCommande' => '2026-09-13',
            'lignes'       => $this->lignes(5),
        ], $this->token, $this->dossierId));
        self::assertNull($effacee['dateExpedition']);
    }

    public function testUneCommandeSansLigneEstRejetee(): void
    {
        $response = $this->api('POST', '/api/commandes', ['lignes' => []], $this->token, $this->dossierId);

        self::assertSame(422, $response->getStatusCode());
    }
}
