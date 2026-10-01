<?php

namespace Wexample\SymfonyUserDemo\Controller\Pages;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Wexample\SymfonyHelpers\Helper\RoleHelper;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;
use Wexample\SymfonyUser\Entity\AbstractUser;
use Wexample\SymfonyUser\Service\FormProcessor\ChangePasswordFormProcessor;
use Wexample\SymfonyUser\Repository\TermsAcceptanceRepository;
use Wexample\SymfonyUser\Service\TrustedDeviceService;
use Wexample\SymfonyUser\Service\FormProcessor\LoginFormProcessor;
use Wexample\SymfonyUser\Service\FormProcessor\MagicLinkRequestFormProcessor;
use Wexample\SymfonyUserDemo\Enum\DemoAccount;
use Wexample\SymfonyUserDemo\Service\DemoAccountsService;
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
        MagicLinkRequestFormProcessor $magicLinkRequestFormProcessor,
        DemoAccountsService $demoAccounts
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
            'magic_link_request_form' => $magicLinkRequestFormProcessor->createForm()->createView(),
            'accounts' => DemoAccount::cases(),
            'accounts_created' => $demoAccounts->exist(),
        ]);
    }

    /**
     * The demo has no phone at hand: it shows the code the authenticator app
     * of the account would, whether signed in or waiting for the code. Links
     * and codes sent by mail land in the development mailbox (symfony-mail-ds).
     */
    #[Route(name: 'authenticator', path: 'authenticator')]
    public function authenticator(
        TokenStorageInterface $tokenStorage,
        #[Autowire(service: 'scheb_two_factor.security.totp_factory')]
        TotpFactory $totpFactory
    ): Response {
        $user = $tokenStorage->getToken()?->getUser();

        return $this->renderPage('authenticator', [
            'totp_code' => $user instanceof AbstractUser && $user->isTotpAuthenticationEnabled()
                ? $totpFactory->createTotpForUser($user)->now()
                : null,
        ]);
    }

    /**
     * Public on purpose: a visitor may have changed a demo password, any other
     * visitor can put it back.
     */
    #[Route(name: 'reset_accounts', path: 'reset-accounts', methods: [Request::METHOD_POST])]
    public function resetAccounts(DemoAccountsService $demoAccounts): RedirectResponse
    {
        $demoAccounts->reset();

        return $this->redirectToRoute('user_index');
    }

    #[Route(name: 'revoke_devices', path: 'account/revoke-devices', methods: [Request::METHOD_POST])]
    #[IsGranted(RoleHelper::ROLE_USER)]
    public function revokeDevices(TrustedDeviceService $trustedDevices): RedirectResponse
    {
        $user = $this->getUser();

        if ($user instanceof AbstractUser) {
            $trustedDevices->revokeAll($user);
        }

        return $this->redirectToRoute('user_account');
    }

    #[Route(name: 'account', path: 'account')]
    #[IsGranted(RoleHelper::ROLE_USER)]
    public function account(
        ChangePasswordFormProcessor $changePasswordFormProcessor,
        TermsAcceptanceRepository $termsAcceptanceRepository
    ): Response {
        $user = $this->getUser();

        return $this->renderPage('account', [
            'change_password_form' => $changePasswordFormProcessor->createForm()->createView(),
            'terms_history' => $user instanceof AbstractUser ? $termsAcceptanceRepository->findHistory($user) : [],
        ]);
    }

    /**
     * The text of the demo terms, the `terms.text_route` of the design-system
     * app: readable while the terms gate holds a visitor.
     */
    #[Route(name: 'terms_text', path: 'terms')]
    public function termsText(): Response
    {
        return $this->renderPage('terms_text');
    }
}
