<?php

namespace Wexample\SymfonyUserDemo\Service;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RequestStack;
use Wexample\SymfonyUser\Entity\AbstractUser;
use Wexample\SymfonyUser\Enum\SecurityLinkType;
use Wexample\SymfonyUser\Interface\SecurityLinkSenderInterface;

/**
 * The demo sends no mail: the link lands in the session of whoever asked for
 * it, and the mailbox page shows it. A visitor only ever sees the links they
 * requested themselves.
 */
class SessionSecurityLinkSenderService implements SecurityLinkSenderInterface
{
    private const string SESSION_KEY = 'user_demo_mailbox';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function send(
        AbstractUser $user,
        SecurityLinkType $type,
        string $url,
        DateTimeImmutable $expiresAt
    ): void {
        $this->requestStack->getSession()->set(self::SESSION_KEY, [
            'type' => $type->value,
            'to' => $user->getEmail(),
            'url' => $url,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * @return array{type: string, to: string, url: string, expires_at: DateTimeImmutable}|null
     */
    public function getLastMail(): ?array
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY);
    }
}
