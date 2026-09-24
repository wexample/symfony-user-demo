<?php

namespace Wexample\SymfonyUserDemo\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Wexample\SymfonyHelpers\Command\AbstractBundleCommand;
use Wexample\SymfonyHelpers\Service\BundleService;
use Wexample\SymfonyUserDemo\Entity\DemoUser;
use Wexample\SymfonyUserDemo\Repository\DemoUserRepository;
use Wexample\SymfonyUserDemo\WexampleSymfonyUserDemoBundle;

/**
 * Creates a test account for the demo pages. The password is asked for,
 * never passed as an argument, so it stays out of the shell history.
 */
class CreateUserCommand extends AbstractBundleCommand
{
    protected static $defaultDescription = 'Creates a test account for the user demo pages';

    public function __construct(
        BundleService $bundleService,
        private readonly EntityManagerInterface $entityManager,
        private readonly DemoUserRepository $demoUserRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        ?string $name = null,
    ) {
        parent::__construct($bundleService, $name);
    }

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyUserDemoBundle::class;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addOption('username', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $io = new SymfonyStyle($input, $output);
        $email = (string) $input->getArgument('email');

        if ($this->demoUserRepository->findOneByUserIdentifier($email)) {
            $io->error(sprintf('%s already exists.', $email));

            return self::FAILURE;
        }

        $user = (new DemoUser())
            ->setEmail($email)
            ->setUsername($input->getOption('username'))
            ->setEnabled(true);

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, (string) $io->askHidden('Password'))
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('%s created.', $user->getUserIdentifier()));

        return self::SUCCESS;
    }
}
