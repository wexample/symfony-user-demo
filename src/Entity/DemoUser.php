<?php

namespace Wexample\SymfonyUserDemo\Entity;

use Doctrine\ORM\Mapping as ORM;
use Wexample\SymfonyUser\Entity\AbstractUser;
use Wexample\SymfonyUser\Entity\Traits\UserWithNameTrait;
use Wexample\SymfonyUserDemo\Repository\DemoUserRepository;

/**
 * The test accounts of the demo, created from the console only.
 */
#[ORM\Entity(repositoryClass: DemoUserRepository::class)]
#[ORM\Table(name: 'demo_user')]
class DemoUser extends AbstractUser
{
    use UserWithNameTrait;
}
