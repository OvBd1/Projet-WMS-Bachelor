<?php

namespace App\Tests\Functional;

use App\Entity\Dossier;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base des tests fonctionnels : vraies requêtes HTTP à travers le noyau Symfony,
 * sur une base de test reconstruite (schéma créé une fois, tables vidées avant chaque test).
 *
 * Piège connu : le noyau est redémarré avant CHAQUE requête et avant chaque préparation
 * de données. Sinon DossierContext conserve en mémoire le dossier d'une requête précédente
 * (filtre Doctrine déjà activé, entités d'un autre gestionnaire) et Doctrine lève
 * « a new entity was found through the relationship Stock#dossier ».
 * En production, chaque requête HTTP part d'un processus neuf : le test reproduit cette condition.
 */
abstract class ApiTestCase extends WebTestCase
{
    protected const PASSWORD = 'secret123';

    private static bool $schemaReady = false;

    protected function setUp(): void
    {
        parent::setUp();

        $em = $this->freshEntityManager();
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        if (!self::$schemaReady) {
            $tool = new SchemaTool($em);
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
            self::$schemaReady = true;
            return;
        }

        $connection = $em->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($metadata as $classMetadata) {
            $connection->executeStatement('TRUNCATE TABLE ' . $connection->quoteIdentifier($classMetadata->getTableName()));
        }
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected function tearDown(): void
    {
        self::ensureKernelShutdown();
        parent::tearDown();
    }

    // ── Requêtes ────────────────────────────────────────────────

    /**
     * Envoie une requête JSON à travers un noyau neuf.
     */
    protected function api(string $method, string $uri, ?array $body = null, ?string $token = null, ?int $dossierId = null): Response
    {
        self::ensureKernelShutdown();
        $client = static::createClient();

        $server = ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'];
        if ($token !== null) {
            $server['HTTP_AUTHORIZATION'] = 'Bearer ' . $token;
        }
        if ($dossierId !== null) {
            $server['HTTP_X_DOSSIER_ID'] = (string) $dossierId;
        }

        $client->request($method, $uri, [], [], $server, $body !== null ? json_encode($body) : null);

        return $client->getResponse();
    }

    protected function decode(Response $response): mixed
    {
        return json_decode((string) $response->getContent(), true);
    }

    protected function login(string $email, string $password = self::PASSWORD): string
    {
        $response = $this->api('POST', '/api/auth/login', ['email' => $email, 'password' => $password]);
        self::assertSame(200, $response->getStatusCode(), 'Connexion impossible pour ' . $email);

        return $this->decode($response)['token'];
    }

    // ── Données de test (hors requêtes HTTP) ────────────────────

    protected function freshEntityManager(): EntityManagerInterface
    {
        self::ensureKernelShutdown();
        self::bootKernel();

        return static::getContainer()->get(EntityManagerInterface::class);
    }

    protected function createDossier(string $code): int
    {
        $em = $this->freshEntityManager();
        $dossier = (new Dossier())->setCode($code)->setRaisonSociale('Société ' . $code);
        $em->persist($dossier);
        $em->flush();

        return $dossier->getId();
    }

    protected function createUser(string $email, string $role = 'ROLE_USER', ?int $dossierId = null): int
    {
        $em = $this->freshEntityManager();
        $user = (new Utilisateur())
            ->setEmail($email)
            ->setNom('Durand')
            ->setPrenom('Alice')
            ->setRole($role)
            ->setDossier($dossierId ? $em->find(Dossier::class, $dossierId) : null)
            // Hachage natif, vérifiable par le hacheur « auto » de Symfony.
            ->setPassword(password_hash(self::PASSWORD, PASSWORD_BCRYPT, ['cost' => 4]));
        $em->persist($user);
        $em->flush();

        return $user->getId();
    }

    // ── Données de test via l'API (dossier sélectionné par en-tête) ─

    protected function createArticle(string $token, int $dossierId, string $reference): int
    {
        $response = $this->api('POST', '/api/articles', ['reference' => $reference, 'libelle' => 'Article ' . $reference], $token, $dossierId);
        self::assertSame(201, $response->getStatusCode(), (string) $response->getContent());

        return $this->decode($response)['id'];
    }

    protected function createEmplacement(string $token, int $dossierId, string $code): int
    {
        $type = $this->api('POST', '/api/types-emplacement', ['libelle' => 'Type ' . $code], $token, $dossierId);
        self::assertSame(201, $type->getStatusCode(), (string) $type->getContent());

        $response = $this->api('POST', '/api/emplacements', ['code' => $code, 'typeEmplacementId' => $this->decode($type)['id']], $token, $dossierId);
        self::assertSame(201, $response->getStatusCode(), (string) $response->getContent());

        return $this->decode($response)['id'];
    }

    protected function createReception(string $token, int $dossierId, int $articleId, int $emplacementId, int $quantite): int
    {
        $response = $this->api('POST', '/api/receptions', [
            'lignes' => [['articleId' => $articleId, 'emplacementId' => $emplacementId, 'quantite' => $quantite]],
        ], $token, $dossierId);
        self::assertSame(201, $response->getStatusCode(), (string) $response->getContent());

        return $this->decode($response)['id'];
    }

    protected function stockQuantite(string $token, int $dossierId, int $articleId, int $emplacementId): int
    {
        $response = $this->api('GET', '/api/stocks', null, $token, $dossierId);
        self::assertSame(200, $response->getStatusCode());

        foreach ($this->decode($response) as $stock) {
            if ($stock['article']['id'] === $articleId && $stock['emplacement']['id'] === $emplacementId) {
                return $stock['quantite'];
            }
        }

        return 0;
    }
}
