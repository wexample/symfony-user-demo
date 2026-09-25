<?php

namespace Wexample\SymfonyUserDemo\Service\Tunnel\Signup;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Wexample\SymfonyTunnels\Class\TunnelCursor;
use Wexample\SymfonyTunnels\Service\Step\AbstractTunnelStep;
use Wexample\SymfonyUser\Entity\AbstractUser;
use Wexample\SymfonyUser\Service\FormProcessor\Tunnel\UserMailFormProcessor;
use Wexample\SymfonyUser\Service\Tunnel\Step\AbstractUserMailStep;
use Wexample\SymfonyUser\Service\Tunnel\Step\LoginStep;
use Wexample\SymfonyUserDemo\Entity\DemoUser;

/**
 * The demo only says which step follows and which entity a new account is.
 */
class SignupUserMailStep extends AbstractUserMailStep
{
    public function __construct(
        UserMailFormProcessor $formProcessor,
        LoginStep $loginStep,
        Security $security,
        UserProviderInterface $userProvider,
        EntityManagerInterface $entityManager,
        private readonly SignupWelcomeStep $welcomeStep,
    ) {
        parent::__construct($formProcessor, $loginStep, $security, $userProvider, $entityManager);
    }

    protected function getStepAfter(TunnelCursor $cursor): AbstractTunnelStep
    {
        return $this->welcomeStep;
    }

    protected function createUser(string $email): AbstractUser
    {
        return new DemoUser();
    }
}
