<?php

namespace App\Tests\Unit\Command;

use App\Command\CreateAdminCommand;
use App\DTO\RegisterDTO;
use App\Entity\Utilisateur;
use App\Service\UtilisateurService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\Validation;

final class CreateAdminCommandTest extends TestCase
{
    private UtilisateurService&MockObject $service;
    private CommandTester $tester;

    protected function setUp(): void
    {
        $this->service = $this->createMock(UtilisateurService::class);
        $validator     = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
        $this->tester  = new CommandTester(new CreateAdminCommand($this->service, $validator));
    }

    private function executer(string $password = 'secret123'): int
    {
        return $this->tester->execute([
            'email'      => 'admin@test.fr',
            'nom'        => 'Durand',
            'prenom'     => 'Alice',
            '--password' => $password,
        ], ['interactive' => false]);
    }

    public function testCreeUnAdministrateur(): void
    {
        $this->service->expects(self::once())->method('register')
            ->with(self::callback(fn (RegisterDTO $dto) => $dto->role === 'ROLE_ADMIN'
                && $dto->email === 'admin@test.fr'
                && $dto->nom === 'Durand'
                && $dto->prenom === 'Alice'
                && $dto->password === 'secret123'))
            ->willReturn((new Utilisateur())->setEmail('admin@test.fr'));

        self::assertSame(Command::SUCCESS, $this->executer());
        self::assertStringContainsString('admin@test.fr', $this->tester->getDisplay());
    }

    public function testRefuseUnMotDePasseTropCourtSansCreerDeCompte(): void
    {
        $this->service->expects(self::never())->method('register');

        self::assertSame(Command::FAILURE, $this->executer('123'));
        self::assertStringContainsString('password', $this->tester->getDisplay());
    }

    public function testRapporteLesErreursMetier(): void
    {
        $this->service->method('register')->willThrowException(new \DomainException('Cet email est déjà utilisé.'));

        self::assertSame(Command::FAILURE, $this->executer());
        self::assertStringContainsString('Cet email est déjà utilisé.', $this->tester->getDisplay());
    }
}
