<?php

namespace Wexample\SymfonyUserDemo\Controller\Pages\DesignSystem;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Wexample\SymfonyHelpers\Helper\RoleHelper;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;
use Wexample\SymfonyLoader\Controller\Pages\AbstractDesignSystemController;
use Wexample\SymfonyUser\Service\FormProcessor\LoginFormProcessor;
use Wexample\SymfonyUserDemo\Traits\SymfonyUserDemoBundleClassTrait;

#[Route(
    name: 'wexample_user_demo_',
    path: AbstractDesignSystemController::CONTROLLER_BASE_ROUTE . '/user/',
)]
final class UserController extends AbstractPagesController
{
    use SymfonyUserDemoBundleClassTrait;
    use TargetPathTrait;

    private const string FIREWALL = 'main';

    /**
     * Public: the login form is shown to anyone, only the test accounts
     * created from the console can get past it.
     */
    #[Route(name: 'index', path: '')]
    public function index(
        Request $request,
        LoginFormProcessor $loginFormProcessor
    ): Response {
        // The page decides where a successful login lands, the way a tunnel
        // step embedding the form would.
        $this->saveTargetPath(
            $request->getSession(),
            self::FIREWALL,
            $this->generateUrl('wexample_user_demo_account')
        );

        return $this->renderPage('index', [
            'login_form' => $loginFormProcessor->createForm()->createView(),
        ]);
    }

    #[Route(name: 'account', path: 'account')]
    #[IsGranted(RoleHelper::ROLE_USER)]
    public function account(): Response
    {
        return $this->renderPage('account');
    }
}
