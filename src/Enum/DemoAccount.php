<?php

namespace Wexample\SymfonyUserDemo\Enum;

/**
 * The test accounts of the demo. Their credentials are public on purpose:
 * the demo page prints them so anyone can try the login form.
 */
enum DemoAccount: string
{
    case ACTIVE = 'demo';

    case LOCKED = 'demo-locked';

    case INACTIVE = 'demo-inactive';

    public function getUsername(): string
    {
        return $this->value;
    }

    public function getEmail(): string
    {
        return $this->value . '@example.com';
    }

    public function getPassword(): string
    {
        return $this->value . '-password';
    }

    /**
     * None: an administrator is no demo account whose password the page
     * prints, but the application's own (`user:create-admin`).
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        return [];
    }

    public function isEnabled(): bool
    {
        return $this !== self::INACTIVE;
    }

    public function isLocked(): bool
    {
        return $this === self::LOCKED;
    }
}
