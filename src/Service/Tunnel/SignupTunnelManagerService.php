<?php

namespace Wexample\SymfonyUserDemo\Service\Tunnel;

use Wexample\SymfonyTunnels\Interface\TunnelSessionStorageInterface;
use Wexample\SymfonyTunnels\Service\AbstractTunnelManagerService;
use Wexample\SymfonyTunnels\Service\Step\AbstractTunnelStep;
use Wexample\SymfonyUserDemo\Service\Tunnel\Signup\SignupUserMailStep;

/**
 * A sign-up built on the two user steps of symfony-user: the email first,
 * the login only for an address that has an account.
 */
class SignupTunnelManagerService extends AbstractTunnelManagerService
{
    public function __construct(
        TunnelSessionStorageInterface $sessionStorage,
        private readonly SignupUserMailStep $userMailStep,
    ) {
        parent::__construct($sessionStorage);
    }

    public static function getName(): string
    {
        return 'signup';
    }

    public function getEntrypointStep(): AbstractTunnelStep
    {
        return $this->userMailStep;
    }
}
