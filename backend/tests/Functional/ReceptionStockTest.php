<?php

namespace App\Tests\Functional;

/**
 * Le stock est une conséquence des réceptions : il ne varie qu'à la validation ou à l'annulation.
 */
final class ReceptionStockTest extends ApiTestCase
{
    private string $token;
    private int $dossierId;
    private int $articleId;
    private int $emplacementId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dossierId = $this->createDossier('D1');
        $this->createUser('admin@test.fr', 'ROLE_ADMIN');
        $this->token         = $this->login('admin@test.fr');
        $this->articleId     = $this->createArticle($this->token, $this->dossierId, 'ART-1');
        $this->emplacementId = $this->createEmplacement($this->token, $this->dossierId, 'A-01');
    }

    private function stock(): int
    {
        return $this->stockQuantite($this->token, $this->dossierId, $this->articleId, $this->emplacementId);
    }

    public function testUneReceptionEnAttenteNeModifiePasLeStock(): void
    {
        $this->createReception($this->token, $this->dossierId, $this->articleId, $this->emplacementId, 10);

        self::assertSame(0, $this->stock());
    }

    public function testLaValidationIncrementeLeStock(): void
    {
        $id = $this->createReception($this->token, $this->dossierId, $this->articleId, $this->emplacementId, 10);

        $response = $this->api('PATCH', "/api/receptions/$id/valider", null, $this->token, $this->dossierId);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('VALIDEE', $this->decode($response)['statut']);
        self::assertSame(10, $this->stock());
    }

    public function testDeuxReceptionsValideesSeCumulent(): void
    {
        foreach ([10, 5] as $quantite) {
            $id = $this->createReception($this->token, $this->dossierId, $this->articleId, $this->emplacementId, $quantite);
            $this->api('PATCH', "/api/receptions/$id/valider", null, $this->token, $this->dossierId);
        }

        self::assertSame(15, $this->stock(), 'Une seule ligne de stock par article et emplacement.');
    }

    public function testUneDoubleValidationEstRefuseeSansDoublerLeStock(): void
    {
        $id = $this->createReception($this->token, $this->dossierId, $this->articleId, $this->emplacementId, 10);
        $this->api('PATCH', "/api/receptions/$id/valider", null, $this->token, $this->dossierId);

        $response = $this->api('PATCH', "/api/receptions/$id/valider", null, $this->token, $this->dossierId);

        self::assertSame(409, $response->getStatusCode());
        self::assertSame(10, $this->stock());
    }

    public function testLAnnulationDUneReceptionValideeRetireLeStock(): void
    {
        $id = $this->createReception($this->token, $this->dossierId, $this->articleId, $this->emplacementId, 10);
        $this->api('PATCH', "/api/receptions/$id/valider", null, $this->token, $this->dossierId);

        $response = $this->api('PATCH', "/api/receptions/$id/annuler", null, $this->token, $this->dossierId);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('ANNULEE', $this->decode($response)['statut']);
        self::assertSame(0, $this->stock());
    }

    public function testLaSaisieDirecteDuStockEstImpossible(): void
    {
        $id = $this->createReception($this->token, $this->dossierId, $this->articleId, $this->emplacementId, 10);
        $this->api('PATCH', "/api/receptions/$id/valider", null, $this->token, $this->dossierId);
        $stocks = $this->decode($this->api('GET', '/api/stocks', null, $this->token, $this->dossierId));

        $response = $this->api('PATCH', '/api/stocks/' . $stocks[0]['id'], ['quantite' => 999], $this->token, $this->dossierId);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame(10, $this->stock());
    }
}
