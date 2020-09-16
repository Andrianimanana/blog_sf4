<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class UserManageCommand extends Command
{
    protected static $defaultName = 'app:user:manage';
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('Commande pour la géstion role users')
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse mail users')
            ->addArgument('role', InputArgument::REQUIRED, 'Le role que vous voulez ajouter au users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $email  = $input->getArgument('email');
        $role   = $input->getArgument('role');
        $repo   = $this->em->getRepository(User::class);
        $user   = $repo->findOneByEmail($email);# or findOneBy(["email" => $email])
        if ($email && $user) {
            $user->addRoles($role);
            $this->em->flush();
            $io->success(sprintf('Role %s a été bien ajouter sur le compte de %s', $role, $email));
        }else{
            $io->error('Une erreur se produite lors de l\'ajout role user');
        }

        return 0;
    }
}
