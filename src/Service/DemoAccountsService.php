<?php

namespace Wexample\SymfonyUserDemo\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Wexample\SymfonyUserDemo\Entity\DemoUser;
use Wexample\SymfonyUserDemo\Enum\DemoAccount;
use Wexample\SymfonyUserDemo\Repository\DemoUserRepository;

/**
 * Creates the demo accounts, or puts them back as DemoAccount describes them
 * once visitors have changed them: their credentials are public.
 */
class DemoAccountsService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DemoUserRepository $demoUserRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function exist(): bool
    {
        return (bool) $this->demoUserRepository->findOneByUserIdentifier(DemoAccount::ACTIVE->getEmail());
    }

    public function reset(): void
    {
        // The accounts the sign-up tunnel created for visitors' addresses.
        $demoEmails = array_map(static fn (DemoAccount $account): string => $account->getEmail(), DemoAccount::cases());
        foreach ($this->demoUserRepository->findAll() as $user) {
            if (! in_array($user->getEmail(), $demoEmails, true)) {
                $this->entityManager->remove($user);
            }
        }

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
                ->setLocked($account->isLocked())
                ->setEmailTwoFactorEnabled(true)
                ->setTotpSecret(null, null)
                ->setBackupCodes([])
                ->revokeTrustedDevices();

            $user->setPassword($this->passwordHasher->hashPassword($user, $account->getPassword()));
        }

        $this->entityManager->flush();
    }
}
