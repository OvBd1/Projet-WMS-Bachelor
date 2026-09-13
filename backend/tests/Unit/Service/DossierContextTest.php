<?php

namespace App\Tests\Unit\Service;

use App\Doctrine\DossierFilter;
use App\Entity\Dossier;
use App\Entity\Utilisateur;
use App\Repository\DossierRepository;
use App\Service\DossierContext;
use App\Tests\Support\EntityIdTrait;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\FilterCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Résolution du dossier courant et activation du filtre Doctrine.
 */
final class DossierContextTest extends TestCase
{
    use EntityIdTrait;

    private Security&MockObject $security;
    private RequestStack $requestStack;
    private DossierRepository&MockObject $dossierRepo;
    private FilterCollection&MockObject $filters;
    private DossierFilter $filter;
    private DossierContext $context;

    protected function setUp(): void
    {
        $this->security     = $this->createMock(Security::class);
        $this->requestStack = new RequestStack();
        $this->dossierRepo  = $this->createMock(DossierRepository::class);

        // Vrai filtre (ses méthodes de paramètres sont finales), branché sur un gestionnaire simulé.
        $connection = $this->createMock(Connection::class);
        $connection->method('quote')->willReturnCallback(fn (string $v) => "'$v'");
        $filterEm = $this->createMock(EntityManagerInterface::class);
        $filterEm->method('getConnection')->willReturn($connection);
        $filterEm->method('getFilters')->willReturn($this->createMock(FilterCollection::class));
        $this->filter = new DossierFilter($filterEm);

        $this->filters = $this->createMock(FilterCollection::class);
        $this->filters->method('enable')->with('dossier_filter')->willReturn($this->filter);
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getFilters')->willReturn($this->filters);

        $this->context = new DossierContext($this->security, $this->requestStack, $this->dossierRepo, $em);
    }

    private function dossier(int $id): Dossier
    {
        return $this->withId((new Dossier())->setCode('D' . $id)->setRaisonSociale('Dossier ' . $id), $id);
    }

    private function connecter(string $role, ?Dossier $dossier = null): void
    {
        $this->security->method('getUser')->willReturn((new Utilisateur())->setRole($role)->setDossier($dossier));
    }

    private function requeteAvecEnTete(string $dossierId): void
    {
        $request = new Request();
        $request->headers->set('X-Dossier-Id', $dossierId);
        $this->requestStack->push($request);
    }

    public function testUnUtilisateurStandardEstCloisonneASonDossier(): void
    {
        $dossier = $this->dossier(5);
        $this->connecter('ROLE_USER', $dossier);

        self::assertSame($dossier, $this->context->getCurrent());
        self::assertSame("'5'", $this->filter->getParameter('dossierId'));
    }

    public function testUnUtilisateurStandardNePeutPasChoisirSonDossierParEnTete(): void
    {
        $dossier = $this->dossier(5);
        $this->connecter('ROLE_USER', $dossier);
        $this->requeteAvecEnTete('99');
        $this->dossierRepo->expects(self::never())->method('find');

        self::assertSame($dossier, $this->context->getCurrent());
    }

    public function testUnAdministrateurTravailleSurLeDossierDeLEnTete(): void
    {
        $dossier = $this->dossier(7);
        $this->connecter('ROLE_ADMIN');
        $this->requeteAvecEnTete('7');
        $this->dossierRepo->method('find')->with(7)->willReturn($dossier);

        self::assertSame($dossier, $this->context->getCurrent());
        self::assertSame("'7'", $this->filter->getParameter('dossierId'));
    }

    public function testSansDossierLeFiltreNeLaissePasserAucuneDonnee(): void
    {
        $this->connecter('ROLE_ADMIN');

        self::assertNull($this->context->getCurrent());
        self::assertSame("'0'", $this->filter->getParameter('dossierId'));
    }

    public function testSansUtilisateurConnecteLeFiltreEstToutDeMemeActive(): void
    {
        $this->security->method('getUser')->willReturn(null);
        $this->filters->expects(self::once())->method('enable')->with('dossier_filter');

        self::assertNull($this->context->getCurrent());
    }

    public function testLeDossierEstResoluUneSeuleFoisParRequete(): void
    {
        $this->security->expects(self::once())->method('getUser')
            ->willReturn((new Utilisateur())->setRole('ROLE_USER')->setDossier($this->dossier(5)));
        $this->filters->expects(self::once())->method('enable');

        $this->context->getCurrent();
        $this->context->getCurrent();
    }

    public function testGetCurrentOrThrowExigeUnDossier(): void
    {
        $this->connecter('ROLE_ADMIN');

        $this->expectException(\DomainException::class);

        $this->context->getCurrentOrThrow();
    }
}
