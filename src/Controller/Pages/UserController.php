<?php

namespace Wexample\SymfonyUserDemo\Controller\Pages;

use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Wexample\SymfonyDesignSystem\Attribute\MenuItem;
use Wexample\SymfonyHelpers\Helper\RoleHelper;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;
use Wexample\SymfonyUser\Entity\AbstractUser;
use Wexample\SymfonyUser\Interface\MemberInterface;
use Wexample\SymfonyUser\Routing\UserRoute;
use Wexample\SymfonyUser\Service\AccountAdministrationService;
use Wexample\SymfonyUser\Service\FormProcessor\LoginFormProcessor;
use Wexample\SymfonyUser\Service\FormProcessor\MagicLinkRequestFormProcessor;
use Wexample\SymfonyUser\Service\MembershipService;
use Wexample\SymfonyUserDemo\Entity\DemoTeam;
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
     * The menu entry the user pages stand under — and what other demos about
     * a user, its notifications, join with #[MenuItem].
     */
    final public const string MENU_GROUP = 'user';

    /**
     * Public, like the credentials of the demo accounts it prints.
     */
    #[Route(name: 'index', path: '')]
    public function index(
        Request $request,
        LoginFormProcessor $loginFormProcessor,
        MagicLinkRequestFormProcessor $magicLinkRequestFormProcessor,
        DemoAccountsService $demoAccounts,
        AccountAdministrationService $administration
    ): Response {
        // The page decides where a successful login lands, the way a tunnel
        // step embedding the form would.
        $this->saveTargetPath(
            $request->getSession(),
            self::FIREWALL,
            $this->generateUrl(UserRoute::PROFILE)
        );

        return $this->renderPage('index', [
            'login_form' => $loginFormProcessor->createForm()->createView(),
            'magic_link_request_form' => $magicLinkRequestFormProcessor->createForm()->createView(),
            'accounts' => DemoAccount::cases(),
            'accounts_created' => $demoAccounts->exist(),
            // The administration screens are symfony-user-ds's, and there
            // only where the application declared administration.page_role.
            'may_administer' => $administration->canAdminister(),
        ]);
    }

    /**
     * The demo has no phone at hand: it shows the code the authenticator app
     * of the account would, whether signed in or waiting for the code. Links
     * and codes sent by mail land in the development mailbox (symfony-mail-ds).
     */
    #[Route(name: 'authenticator', path: 'authenticator')]
    #[MenuItem(self::MENU_GROUP, 1)]
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

    /**
     * The demo teams, with what the signed-in account holds in each and the
     * members page of symfony-user-ds where it may open it.
     */
    #[Route(name: 'teams', path: 'teams')]
    #[IsGranted(RoleHelper::ROLE_USER)]
    public function teams(
        EntityManagerInterface $entityManager,
        MembershipService $membershipService
    ): Response {
        $user = $this->getUser();

        return $this->renderPage('teams', [
            'teams' => array_map(static fn (DemoTeam $team) => [
                'team' => $team,
                // The grants given, not every one they include.
                'grants' => $user instanceof MemberInterface ? ($membershipService->getMembership($user, $team)?->getGrants() ?? []) : [],
                'count' => count($team->getMemberships()),
            ], $entityManager->getRepository(DemoTeam::class)->findBy([], ['name' => 'ASC'])),
        ]);
    }

    /**
     * The text of the demo terms, the `terms.text_route` of the design-system
     * app: readable while the terms gate holds a visitor.
     */
    #[Route(name: 'terms_text', path: 'terms')]
    #[MenuItem(self::MENU_GROUP, 2)]
    public function termsText(): Response
    {
        return $this->renderPage('terms_text');
    }
}
