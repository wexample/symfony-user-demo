<?php

namespace Wexample\SymfonyUserDemo\Service;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RequestStack;
use Wexample\SymfonyUser\Entity\AbstractUser;
use Wexample\SymfonyUser\Enum\SecurityMessageType;
use Wexample\SymfonyUser\Interface\SecurityMessageSenderInterface;

/**
 * The demo sends no mail: the message lands in the session of whoever asked
 * for it, and the mailbox page shows it. A visitor only ever sees the links
 * and codes they caused themselves.
 */
class SessionSecurityMessageSenderService implements SecurityMessageSenderInterface
{
    private const string SESSION_KEY = 'user_demo_mailbox';

    public function __construct(
        private readonly RequestStack $requestStack
    ) {
    }

    public function send(
        AbstractUser $user,
        SecurityMessageType $type,
        string $value,
        DateTimeImmutable $expiresAt
    ): void {
        $this->requestStack->getSession()->set(self::SESSION_KEY, [
            'type' => $type->value,
            'is_link' => $type->carriesLink(),
            'to' => $user->getEmail(),
            'value' => $value,
            'expires_at' => $expiresAt,
        ]);
    }

    /**
     * @return array{type: string, is_link: bool, to: string, value: string, expires_at: DateTimeImmutable}|null
     */
    public function getLastMail(): ?array
    {
        return $this->requestStack->getSession()->get(self::SESSION_KEY);
    }
}
