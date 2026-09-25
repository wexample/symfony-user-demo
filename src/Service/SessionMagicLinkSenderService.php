<?php

namespace Wexample\SymfonyUserDemo\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Wexample\SymfonyUser\Entity\AbstractUser;
use Wexample\SymfonyUser\Interface\MagicLinkSenderInterface;

/**
 * The demo sends no mail: the link lands in the session of whoever asked for
 * it, and the mailbox page shows it. A visitor only ever sees the links they
 * requested themselves.
 */
class SessionMagicLinkSenderService implements MagicLinkSenderInterface
{
    private const string SESSION_KEY = 'user_demo_mailbox';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function send(AbstractUser $user, LoginLinkDetails $link): void
    {
        $this->requestStack->getSession()->set(self::SESSION_KEY, [
            'to' => $user->getEmail(),
            'url' => $link->getUrl(),
            'expires_at' => $link->getExpiresAt(),
        ]);
    }

    /**
     * @return array{to: string, url: string, expires_at: \DateTimeImmutable}|null
     */
    public function getLastMail(): ?array
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY);
    }
}
