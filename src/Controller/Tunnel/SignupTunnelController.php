<?php

namespace Wexample\SymfonyUserDemo\Controller\Tunnel;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyTunnels\Attribute\TunnelRoute;
use Wexample\SymfonyTunnels\Controller\AbstractTunnelController;
use Wexample\SymfonyUserDemo\Service\Tunnel\SignupTunnelManagerService;
use Wexample\SymfonyUserDemo\Traits\SymfonyUserDemoBundleClassTrait;

/**
 * Mounts the sign-up below the user pages: each step at user/signup/<step>.
 * Out of Controller\Pages, so the menu built from the pages leaves it out.
 */
#[Route(path: 'user/signup/', name: 'user_signup_')]
final class SignupTunnelController extends AbstractTunnelController
{
    use SymfonyUserDemoBundleClassTrait;

    public static function getTunnelManagerClass(): string
    {
        return SignupTunnelManagerService::class;
    }

    #[TunnelRoute(cursorPlaceholder: '{step}')]
    public function index(Request $request): Response
    {
        return $this->handleTunnelRequest($request);
    }
}
