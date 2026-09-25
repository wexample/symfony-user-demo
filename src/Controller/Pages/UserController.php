<?php

namespace Wexample\SymfonyUserDemo\Controller\Pages;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Wexample\SymfonyHelpers\Helper\RoleHelper;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;
use Wexample\SymfonyUser\Service\FormProcessor\LoginFormProcessor;
use Wexample\SymfonyUserDemo\Enum\DemoAccount;
use Wexample\SymfonyUserDemo\Repository\DemoUserRepository;
use Wexample\SymfonyUserDemo\Traits\SymfonyUserDemoBundleClassTrait;

#[Route(path: 'user/', name: 'user_')]
final class UserController extends AbstractPagesController
{
    use SymfonyUserDemoBundleClassTrait;
    use TargetPathTrait;

    private const string FIREWALL = 'main';

    /**
     * Public, like the credentials of the demo accounts it prints.
     */
    #[Route(name: 'index', path: '')]
    public function index(
        Request $request,
        LoginFormProcessor $loginFormProcessor,
        DemoUserRepository $demoUserRepository
    ): Response {
        // The page decides where a successful login lands, the way a tunnel
        // step embedding the form would.
        $this->saveTargetPath(
            $request->getSession(),
            self::FIREWALL,
            $this->generateUrl('user_account')
        );

        return $this->renderPage('index', [
            'login_form' => $loginFormProcessor->createForm()->createView(),
            'accounts' => DemoAccount::cases(),
            'accounts_created' => (bool) $demoUserRepository->findOneByUserIdentifier(DemoAccount::ACTIVE->getEmail()),
        ]);
    }

    #[Route(name: 'account', path: 'account')]
    #[IsGranted(RoleHelper::ROLE_USER)]
    public function account(): Response
    {
        return $this->renderPage('account');
    }
}
