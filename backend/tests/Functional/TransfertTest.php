<?php

namespace App\Tests\Functional;

/**
 * Un transfert compose deux mouvements (sortie + entrée) dans une seule transaction.
 */
final class TransfertTest extends ApiTestCase
{
    private string $token;
    private int $dossierId;
    private int $articleId;
    private int $sourceId;
    private int $destinationId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dossierId = $this->createDossier('D1');
        $this->createUser('admin@test.fr', 'ROLE_ADMIN');
        $this->token         = $this->login('admin@test.fr');
        $this->articleId     = $this->createArticle($this->token, $this->dossierId, 'ART-1');
        $this->sourceId      = $this->createEmplacement($this->token, $this->dossierId, 'A-01');
        $this->destinationId = $this->createEmplacement($this->token, $this->dossierId, 'B-01');

        // 10 unités en A-01
        $receptionId = $this->createReception($this->token, $this->dossierId, $this->articleId, $this->sourceId, 10);
        $this->api('PATCH', "/api/receptions/$receptionId/valider", null, $this->token, $this->dossierId);
    }

    private function transferer(int $quantite, ?int $destinationId = null): \Symfony\Component\HttpFoundation\Response
    {
        return $this->api('POST', '/api/transferts', [
            'articleId'                => $this->articleId,
            'emplacementSourceId'      => $this->sourceId,
            'emplacementDestinationId' => $destinationId ?? $this->destinationId,
            'quantite'                 => $quantite,
        ], $this->token, $this->dossierId);
    }

    public function testLeTransfertDeplaceLeStock(): void
    {
        $response = $this->transferer(4);

        self::assertSame(201, $response->getStatusCode(), (string) $response->getContent());
        self::assertSame(6, $this->stockQuantite($this->token, $this->dossierId, $this->articleId, $this->sourceId));
        self::assertSame(4, $this->stockQuantite($this->token, $this->dossierId, $this->articleId, $this->destinationId));
    }

    public function testUnTransfertSuperieurAuStockEstRefuseSansRienModifier(): void
    {
        $response = $this->transferer(50);

        self::assertSame(400, $response->getStatusCode());
        self::assertStringContainsString('Stock insuffisant', $this->decode($response)['message']);
        self::assertSame(10, $this->stockQuantite($this->token, $this->dossierId, $this->articleId, $this->sourceId));
        self::assertSame(0, $this->stockQuantite($this->token, $this->dossierId, $this->articleId, $this->destinationId));
    }

    public function testUnTransfertVersLeMemeEmplacementEstRefuse(): void
    {
        $response = $this->transferer(4, $this->sourceId);

        self::assertSame(400, $response->getStatusCode());
        self::assertSame(10, $this->stockQuantite($this->token, $this->dossierId, $this->articleId, $this->sourceId));
    }

    public function testUneQuantiteNulleEstRejeteeParLaValidation(): void
    {
        $response = $this->transferer(0);

        self::assertSame(422, $response->getStatusCode());
        self::assertArrayHasKey('quantite', $this->decode($response)['errors']);
    }
}
