<?php

namespace Wexample\SymfonyUserDemo\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    /** Each demo team, with the grants the demo account holds there. */
    private const array TEAMS = [
        'Editorial' => ['administrator'],
        'Studio' => ['watcher'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DemoUserRepository $demoUserRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        // The administrator's password, when the application keeps it secret:
        // the administration screens then belong to whoever runs the demo.
        #[Autowire('%env(default::DEMO_ADMIN_PASSWORD)%')]
        private readonly ?string $adminPassword = null,
        #[Autowire('%kernel.debug%')]
        private readonly bool $debug = false,
    ) {
    }

    /**
     * The password an account is given. The administrator's is the secret one
     * when there is one; without it, it stays the printed one where the demo is
     * being developed, and nobody's in production, so that a forgotten secret
     * closes the administration rather than opening it to every visitor.
     */
    public function passwordOf(DemoAccount $account): string
    {
        if (DemoAccount::ADMIN !== $account) {
            return $account->getPassword();
        }

        if (null !== $this->adminPassword && '' !== $this->adminPassword) {
            return $this->adminPassword;
        }

        return $this->debug ? $account->getPassword() : bin2hex(random_bytes(24));
    }

    /**
     * Whether the page may print the account's password.
     */
    public function isPasswordPublic(DemoAccount $account): bool
    {
        return DemoAccount::ADMIN !== $account
            || ((null === $this->adminPassword || '' === $this->adminPassword) && $this->debug);
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
                ->setRoles($account->getRoles())
                ->setEnabled($account->isEnabled())
                ->setLocked($account->isLocked())
                ->setEmailTwoFactorEnabled(true)
                ->setTotpSecret(null, null)
                ->setBackupCodes([])
                ->revokeTrustedDevices();

            $user->setPassword($this->passwordHasher->hashPassword($user, $this->passwordOf($account)));
        }

        $this->entityManager->flush();

        $this->resetTeams();
    }

    /**
     * Two teams: demo administers the first and watches the second, which
     * demo-admin reaches through its platform role only.
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
