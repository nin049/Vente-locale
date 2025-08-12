<?php

namespace App\Command;

use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:user:roles',
    description: 'Affiche les rôles d\'un utilisateur',
)]
class UserRolesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $utilisateur = $this->entityManager->getRepository(Utilisateur::class)
            ->findOneBy(['email' => $email]);

        if (!$utilisateur) {
            $io->error("Utilisateur avec l'email '$email' non trouvé.");
            return Command::FAILURE;
        }

        $io->title("Rôles de l'utilisateur {$utilisateur->getPrenom()} {$utilisateur->getNom()}");
        
        $roles = $utilisateur->getRoles();
        
        if (empty($roles)) {
            $io->warning('Aucun rôle assigné à cet utilisateur.');
        } else {
            $io->listing($roles);
        }

        $io->success('Vérification terminée.');

        return Command::SUCCESS;
    }
}
