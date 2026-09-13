<?php

namespace App\Command;

use App\DTO\RegisterDTO;
use App\Service\UtilisateurService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Crée un compte administrateur depuis la ligne de commande.
 *
 * Seul moyen de créer le premier administrateur d'une installation neuve :
 * l'API ne permet la création de comptes qu'à un administrateur déjà connecté.
 */
#[AsCommand(name: 'app:create-admin', description: 'Crée un compte administrateur')]
class CreateAdminCommand extends Command
{
    public function __construct(
        private UtilisateurService $service,
        private ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse e-mail de connexion')
            ->addArgument('nom', InputArgument::REQUIRED, 'Nom')
            ->addArgument('prenom', InputArgument::REQUIRED, 'Prénom')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Mot de passe (demandé en saisie masquée si absent)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dto           = new RegisterDTO();
        $dto->email    = $input->getArgument('email');
        $dto->nom      = $input->getArgument('nom');
        $dto->prenom   = $input->getArgument('prenom');
        $dto->password = (string) ($input->getOption('password') ?? $io->askHidden('Mot de passe'));
        $dto->role     = 'ROLE_ADMIN';

        $errors = $this->validator->validate($dto);
        if (\count($errors) > 0) {
            foreach ($errors as $e) {
                $io->error("{$e->getPropertyPath()} : {$e->getMessage()}");
            }
            return Command::FAILURE;
        }

        try {
            $user = $this->service->register($dto);
        } catch (\DomainException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        $io->success("Administrateur {$user->getEmail()} créé.");

        return Command::SUCCESS;
    }
}
