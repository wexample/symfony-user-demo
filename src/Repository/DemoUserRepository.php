<?php

namespace Wexample\SymfonyUserDemo\Repository;

use Wexample\SymfonyUser\Repository\AbstractUserRepository;
use Wexample\SymfonyUserDemo\Entity\DemoUser;

/**
 * @method DemoUser|null findOneByUserIdentifier(string $identifier)
 */
class DemoUserRepository extends AbstractUserRepository
{
    public static function getEntityClassName(): string
    {
        return DemoUser::class;
    }
}
