<?php

namespace Wexample\SymfonyUserDemo\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Wexample\SymfonyHelpers\Command\AbstractBundleCommand;
use Wexample\SymfonyHelpers\Service\BundleService;
use Wexample\SymfonyUserDemo\Entity\DemoUser;
use Wexample\SymfonyUserDemo\Enum\DemoAccount;
use Wexample\SymfonyUserDemo\Repository\DemoUserRepository;
use Wexample\SymfonyUserDemo\WexampleSymfonyUserDemoBundle;

/**
 * Creates the demo accounts, or puts them back as DemoAccount describes them
 * when a visitor has changed one.
 */
class CreateAccountsCommand extends AbstractBundleCommand
{
    protected static $defaultDescription = 'Creates or resets the test accounts of the user demo pages';

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

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        foreach (DemoAccount::cases() as $account) {
            $user = $this->demoUserRepository->findOneByUserIdentifier($account->getEmail());

            if (! $user) {
                $user = new DemoUser();
                $this->entityManager->persist($user);
            }

            $user
                ->setEmail($account->getEmail())
                ->setUsername($account->getUsername())
                ->setRoles([])
                ->setEnabled($account->isEnabled())
                ->setLocked($account->isLocked());

            $user->setPassword($this->passwordHasher->hashPassword($user, $account->getPassword()));

            $output->writeln($account->getEmail());
        }

        $this->entityManager->flush();

        return self::SUCCESS;
    }
}
