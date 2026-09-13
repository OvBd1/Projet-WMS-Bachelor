<?php

namespace App\Tests\Unit\Service;

use App\DTO\RegisterDTO;
use App\Entity\Dossier;
use App\Entity\Utilisateur;
use App\Repository\DossierRepository;
use App\Repository\UtilisateurRepository;
use App\Service\UtilisateurService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UtilisateurServiceTest extends TestCase
{
    private UtilisateurRepository&MockObject $repo;
    private DossierRepository&MockObject $dossierRepo;
    private EntityManagerInterface&MockObject $em;
    private UserPasswordHasherInterface&MockObject $hasher;
    private UtilisateurService $service;

    protected function setUp(): void
    {
        $this->repo        = $this->createMock(UtilisateurRepository::class);
        $this->dossierRepo = $this->createMock(DossierRepository::class);
        $this->em          = $this->createMock(EntityManagerInterface::class);
        $this->hasher      = $this->createMock(UserPasswordHasherInterface::class);
        $this->hasher->method('hashPassword')->willReturn('mot-de-passe-hache');

        $this->service = new UtilisateurService($this->repo, $this->dossierRepo, $this->em, $this->hasher);
    }

    private function dto(string $role = 'ROLE_USER', ?int $dossierId = null): RegisterDTO
    {
        $dto            = new RegisterDTO();
        $dto->email     = 'jean@test.fr';
        $dto->nom       = 'Dupont';
        $dto->prenom    = 'Jean';
        $dto->password  = 'secret123';
        $dto->role      = $role;
        $dto->dossierId = $dossierId;

        return $dto;
    }

    public function testRefuseUnEmailDejaUtilise(): void
    {
        $this->repo->method('findOneBy')->with(['email' => 'jean@test.fr'])->willReturn(new Utilisateur());
        $this->em->expects(self::never())->method('persist');

        $this->expectExceptionMessage('Cet email est déjà utilisé.');

        $this->service->register($this->dto('ROLE_ADMIN'));
    }

    public function testUnUtilisateurStandardDoitEtreRattacheAUnDossier(): void
    {
        $this->em->expects(self::never())->method('persist');

        $this->expectException(\DomainException::class);

        $this->service->register($this->dto('ROLE_USER', null));
    }

    public function testRefuseUnDossierInconnu(): void
    {
        $this->dossierRepo->method('find')->willReturn(null);

        $this->expectExceptionMessage('Dossier 42 introuvable.');

        $this->service->register($this->dto('ROLE_USER', 42));
    }

    public function testCreeUnAdministrateurSansDossierAvecUnMotDePasseHache(): void
    {
        $this->hasher->expects(self::once())->method('hashPassword')
            ->with(self::isInstanceOf(Utilisateur::class), 'secret123');
        $this->em->expects(self::once())->method('persist');
        $this->em->expects(self::once())->method('flush');

        $user = $this->service->register($this->dto('ROLE_ADMIN'));

        self::assertSame(['ROLE_ADMIN'], $user->getRoles());
        self::assertNull($user->getDossier());
        self::assertSame('mot-de-passe-hache', $user->getPassword());
        self::assertNotSame('secret123', $user->getPassword());
    }

    public function testRattacheUnUtilisateurStandardASonDossier(): void
    {
        $dossier = (new Dossier())->setCode('D1')->setRaisonSociale('Dossier 1');
        $this->dossierRepo->method('find')->with(7)->willReturn($dossier);

        $user = $this->service->register($this->dto('ROLE_USER', 7));

        self::assertSame($dossier, $user->getDossier());
    }

    public function testLaSerialisationNExposeJamaisLeMotDePasse(): void
    {
        $user = (new Utilisateur())->setEmail('jean@test.fr')->setPassword('mot-de-passe-hache');

        self::assertArrayNotHasKey('password', $this->service->normalize($user));
    }
}
