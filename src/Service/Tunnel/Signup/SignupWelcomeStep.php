<?php

namespace Wexample\SymfonyUserDemo\Service\Tunnel\Signup;

use Symfony\Bundle\SecurityBundle\Security;
use Wexample\SymfonyTunnels\Class\TunnelCursor;
use Wexample\SymfonyTunnels\Service\Step\AbstractTunnelStep;
use Wexample\SymfonyUser\Service\Tunnel\Step\AbstractUserMailStep;
use Wexample\SymfonyUserDemo\Repository\DemoUserRepository;

/**
 * Where the tunnel ends: the account it goes on with, signed in or created.
 */
class SignupWelcomeStep extends AbstractTunnelStep
{
    public function __construct(
        private readonly Security $security,
        private readonly DemoUserRepository $demoUserRepository,
    ) {
    }

    public static function getName(): string
    {
        return 'welcome';
    }

    public function initAsCurrentStep(TunnelCursor $cursor): void
    {
        parent::initAsCurrentStep($cursor);

        $cursor->manager->setSessionComplete();
    }

    public function buildViewParams(TunnelCursor $cursor): array
    {
        $identifier = $cursor->manager->getVariableValue(AbstractUserMailStep::VARIABLE_USER);

        return [
            'account' => $identifier ? $this->demoUserRepository->findOneByUserIdentifier($identifier) : null,
            'signed_in' => $this->security->isGranted('IS_AUTHENTICATED_FULLY'),
        ];
    }
}
