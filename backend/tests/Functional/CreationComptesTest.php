<?php

namespace App\Tests\Functional;

/**
 * Seul un administrateur connecté peut créer des comptes.
 */
final class CreationComptesTest extends ApiTestCase
{
    private function payload(array $override = []): array
    {
        return $override + [
            'email'    => 'nouveau@test.fr',
            'nom'      => 'Martin',
            'prenom'   => 'Paul',
            'password' => 'motdepasse',
            'role'     => 'ROLE_USER',
        ];
    }

    public function testInscriptionPubliqueSupprimee(): void
    {
        $response = $this->api('POST', '/api/auth/register', $this->payload(['role' => 'ROLE_ADMIN']));

        self::assertSame(404, $response->getStatusCode());
    }

    public function testCreationAnonymeRefusee(): void
    {
        self::assertSame(401, $this->api('POST', '/api/utilisateurs', $this->payload())->getStatusCode());
    }

    public function testCreationParUnUtilisateurStandardRefusee(): void
    {
        $dossierId = $this->createDossier('D1');
        $this->createUser('user@test.fr', 'ROLE_USER', $dossierId);
        $token = $this->login('user@test.fr');

        $response = $this->api('POST', '/api/utilisateurs', $this->payload(['dossierId' => $dossierId]), $token);

        self::assertSame(403, $response->getStatusCode());
    }

    public function testCreationParUnAdministrateur(): void
    {
        $dossierId = $this->createDossier('D1');
        $this->createUser('admin@test.fr', 'ROLE_ADMIN');
        $token = $this->login('admin@test.fr');

        $response = $this->api('POST', '/api/utilisateurs', $this->payload(['dossierId' => $dossierId]), $token);

        self::assertSame(201, $response->getStatusCode());
        $user = $this->decode($response);
        self::assertSame('ROLE_USER', $user['role']);
        self::assertSame($dossierId, $user['dossier']['id']);
        self::assertArrayNotHasKey('password', $user, 'Le mot de passe ne doit jamais être sérialisé.');

        // Le compte créé peut se connecter.
        self::assertNotEmpty($this->login('nouveau@test.fr', 'motdepasse'));
    }

    public function testUtilisateurStandardSansDossierRefuse(): void
    {
        $this->createUser('admin@test.fr', 'ROLE_ADMIN');
        $token = $this->login('admin@test.fr');

        $response = $this->api('POST', '/api/utilisateurs', $this->payload(), $token);

        self::assertSame(409, $response->getStatusCode());
    }

    public function testEmailDejaUtiliseRefuse(): void
    {
        $this->createUser('admin@test.fr', 'ROLE_ADMIN');
        $token = $this->login('admin@test.fr');

        $response = $this->api('POST', '/api/utilisateurs', $this->payload(['email' => 'admin@test.fr', 'role' => 'ROLE_ADMIN']), $token);

        self::assertSame(409, $response->getStatusCode());
    }
}
