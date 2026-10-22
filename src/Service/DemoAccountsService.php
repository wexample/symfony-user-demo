<?php

namespace Wexample\SymfonyUserDemo\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Wexample\SymfonyHelpers\Helper\RoleHelper;
use Wexample\SymfonyUserDemo\Entity\DemoTeam;
use Wexample\SymfonyUserDemo\Entity\DemoTeamMembership;
use Wexample\SymfonyUserDemo\Entity\DemoUser;
use Wexample\SymfonyUserDemo\Enum\DemoAccount;
use Wexample\SymfonyUserDemo\Repository\DemoUserRepository;

/**
 * Creates the demo accounts and their teams, or puts them back as DemoAccount
 * describes them once visitors have changed them: their credentials are
 * public.
 */
class DemoAccountsService
{
    /**
     * The administrator the demo once had, its password printed on the page:
     * removed whatever its roles, an administrator being kept otherwise.
     */
    private const string RETIRED_ADMIN_EMAIL = 'demo-admin@example.com';

    /** Each demo team, with the grants the demo account holds there. */
    private const array TEAMS = [
        'Editorial' => ['administrator'],
        'Studio' => ['watcher'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DemoUserRepository $demoUserRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly bool $demoEmailTwoFactor,
    ) {
    }

    public function exist(): bool
    {
        return (bool) $this->demoUserRepository->findOneByUserIdentifier(DemoAccount::ACTIVE->getEmail());
    }

    public function reset(): void
    {
        // The accounts the sign-up tunnel created for visitors' addresses; never
        // an administrator's, which is the application's and no visitor's.
        $demoEmails = array_map(static fn (DemoAccount $account): string => $account->getEmail(), DemoAccount::cases());
        foreach ($this->demoUserRepository->findAll() as $user) {
            if (self::RETIRED_ADMIN_EMAIL === $user->getEmail()
                || (! in_array($user->getEmail(), $demoEmails, true)
                    && ! in_array(RoleHelper::ROLE_ADMIN, $user->getRoles(), true))
            ) {
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
                ->setRoles($account->getRoles())
                ->setEnabled($account->isEnabled())
                ->setLocked($account->isLocked())
                ->setEmailTwoFactorEnabled($this->demoEmailTwoFactor)
                ->setTotpSecret(null, null)
                ->setBackupCodes([])
                ->revokeTrustedDevices();

            $user->setPassword($this->passwordHasher->hashPassword($user, $account->getPassword()));
        }

        $this->entityManager->flush();

        $this->resetTeams();
    }

    /**
     * Two teams: demo administers the first and watches the second, which an
     * administrator reaches through their platform role only.
     */
    private function resetTeams(): void
    {
        $this->entityManager->createQuery('DELETE FROM ' . DemoTeamMembership::class)->execute();
        $this->entityManager->createQuery('DELETE FROM ' . DemoTeam::class)->execute();
        $this->entityManager->clear();

        $demo = $this->demoUserRepository->findOneByUserIdentifier(DemoAccount::ACTIVE->getEmail());

        foreach (self::TEAMS as $name => $grants) {
            $team = new DemoTeam($name);
            $this->entityManager->persist($team);
            $this->entityManager->persist(new DemoTeamMembership($team, $demo, $grants));
        }

        $this->entityManager->flush();
    }
}
