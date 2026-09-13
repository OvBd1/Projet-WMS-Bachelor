<?php

namespace App\Tests\Functional;

final class AuthenticationTest extends ApiTestCase
{
    public function testConnexionAvecIdentifiantsValidesRenvoieUnJeton(): void
    {
        $this->createUser('alice@test.fr', 'ROLE_ADMIN');

        $response = $this->api('POST', '/api/auth/login', ['email' => 'alice@test.fr', 'password' => self::PASSWORD]);

        self::assertSame(200, $response->getStatusCode());
        $token = $this->decode($response)['token'] ?? '';
        self::assertCount(3, explode('.', $token), 'Le jeton doit être un JWT (en-tête.charge.signature).');
    }

    public function testLeJetonPorteLIdentiteDeLUtilisateur(): void
    {
        $id = $this->createUser('alice@test.fr', 'ROLE_ADMIN');

        $token = $this->login('alice@test.fr');
        $payload = json_decode(base64_decode(strtr(explode('.', $token)[1], '-_', '+/')), true);

        self::assertSame($id, $payload['id']);
        self::assertSame('Alice', $payload['prenom']);
        self::assertSame('Durand', $payload['nom']);
        self::assertContains('ROLE_ADMIN', $payload['roles']);
    }

    public function testConnexionAvecMauvaisMotDePasseRefusee(): void
    {
        $this->createUser('alice@test.fr');

        $response = $this->api('POST', '/api/auth/login', ['email' => 'alice@test.fr', 'password' => 'mauvais']);

        self::assertSame(401, $response->getStatusCode());
        self::assertArrayNotHasKey('token', $this->decode($response) ?? []);
    }

    public function testRouteProtegeeSansJetonRefusee(): void
    {
        self::assertSame(401, $this->api('GET', '/api/articles')->getStatusCode());
    }

    public function testRouteProtegeeAvecJetonInvalideRefusee(): void
    {
        self::assertSame(401, $this->api('GET', '/api/articles', null, 'jeton.invalide.signature')->getStatusCode());
    }
}
