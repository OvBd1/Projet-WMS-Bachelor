<?php

namespace App\Tests\Functional;

/**
 * Passerelle vers la Base Adresse Nationale.
 *
 * Aucun appel externe : en test, ADRESSE_API_BASE_URL pointe vers une adresse injoignable
 * (voir .env.test). La normalisation des réponses est couverte par les tests unitaires.
 */
final class AdresseApiTest extends ApiTestCase
{
    public function testLaRechercheExigeUneAuthentification(): void
    {
        self::assertSame(401, $this->api('GET', '/api/adresses/search?q=8+boulevard+du+port')->getStatusCode());
    }

    public function testUneRequeteTropCourteRenvoieUneListeVide(): void
    {
        $this->createUser('admin@test.fr', 'ROLE_ADMIN');

        $response = $this->api('GET', '/api/adresses/search?q=ab', null, $this->login('admin@test.fr'));

        self::assertSame(200, $response->getStatusCode());
        self::assertSame([], $this->decode($response));
    }

    public function testServiceIndisponibleDegradationSilencieuse(): void
    {
        $this->createUser('admin@test.fr', 'ROLE_ADMIN');

        $response = $this->api('GET', '/api/adresses/search?q=8+boulevard+du+port', null, $this->login('admin@test.fr'));

        self::assertSame(200, $response->getStatusCode(), 'Une panne du service tiers ne doit pas casser la saisie.');
        self::assertSame([], $this->decode($response));
    }
}
