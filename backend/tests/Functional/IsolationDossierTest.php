<?php

namespace App\Tests\Functional;

/**
 * Multi-tenance : le cloisonnement est appliqué au niveau de la persistance (DossierFilter).
 */
final class IsolationDossierTest extends ApiTestCase
{
    private int $dossier1;
    private int $dossier2;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dossier1 = $this->createDossier('D1');
        $this->dossier2 = $this->createDossier('D2');
        $this->createUser('admin@test.fr', 'ROLE_ADMIN');
        $this->createUser('user1@test.fr', 'ROLE_USER', $this->dossier1);
        $this->createUser('user2@test.fr', 'ROLE_USER', $this->dossier2);
        $this->adminToken = $this->login('admin@test.fr');

        $this->createArticle($this->adminToken, $this->dossier1, 'ART-D1');
        $this->createArticle($this->adminToken, $this->dossier2, 'ART-D2');
    }

    /** @return list<string> */
    private function references(string $token, ?int $dossierId = null): array
    {
        $response = $this->api('GET', '/api/articles', null, $token, $dossierId);
        self::assertSame(200, $response->getStatusCode());

        return array_column($this->decode($response), 'reference');
    }

    public function testUnUtilisateurNeVoitQueLesArticlesDeSonDossier(): void
    {
        self::assertSame(['ART-D1'], $this->references($this->login('user1@test.fr')));
        self::assertSame(['ART-D2'], $this->references($this->login('user2@test.fr')));
    }

    public function testUnUtilisateurNePeutPasChoisirUnAutreDossierParEnTete(): void
    {
        // L'en-tête X-Dossier-Id n'est pris en compte que pour un administrateur.
        self::assertSame(['ART-D1'], $this->references($this->login('user1@test.fr'), $this->dossier2));
    }

    public function testUnUtilisateurNeLitPasUnArticleDUnAutreDossierParSonIdentifiant(): void
    {
        $articleD2 = $this->decode($this->api('GET', '/api/articles', null, $this->adminToken, $this->dossier2))[0]['id'];

        $response = $this->api('GET', "/api/articles/$articleD2", null, $this->login('user1@test.fr'));

        self::assertSame(404, $response->getStatusCode());
    }

    public function testUnAdministrateurVoitLeDossierSelectionne(): void
    {
        self::assertSame(['ART-D1'], $this->references($this->adminToken, $this->dossier1));
        self::assertSame(['ART-D2'], $this->references($this->adminToken, $this->dossier2));
    }

    public function testUneMemeReferenceEstAutoriseeDansDeuxDossiers(): void
    {
        $this->createArticle($this->adminToken, $this->dossier1, 'COMMUNE');
        $this->createArticle($this->adminToken, $this->dossier2, 'COMMUNE');

        self::assertContains('COMMUNE', $this->references($this->adminToken, $this->dossier1));
        self::assertContains('COMMUNE', $this->references($this->adminToken, $this->dossier2));
    }
}
