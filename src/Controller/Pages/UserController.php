<?php

namespace Wexample\SymfonyUserDemo\Controller\Pages;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Wexample\SymfonyHelpers\Helper\RoleHelper;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;
use Wexample\SymfonyUser\Entity\AbstractUser;
use Wexample\SymfonyUser\Service\FormProcessor\ChangePasswordFormProcessor;
use Wexample\SymfonyUser\Service\FormProcessor\LoginFormProcessor;
use Wexample\SymfonyUser\Service\FormProcessor\MagicLinkRequestFormProcessor;
use Wexample\SymfonyUserDemo\Enum\DemoAccount;
use Wexample\SymfonyUserDemo\Service\DemoAccountsService;
use Wexample\SymfonyUserDemo\Service\SessionSecurityMessageSenderService;
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
     * Where the demo delivers its links and codes, in place of a real inbox.
     */
    #[Route(name: 'mailbox', path: 'mailbox')]
    public function mailbox(SessionSecurityMessageSenderService $sender): Response
    {
        return $this->renderPage('mailbox', [
            'mail' => $sender->getLastMail(),
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
    public function revokeDevices(EntityManagerInterface $entityManager): RedirectResponse
    {
        $user = $this->getUser();

        if ($user instanceof AbstractUser) {
            $user->revokeTrustedDevices();
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_account');
    }

    #[Route(name: 'account', path: 'account')]
    #[IsGranted(RoleHelper::ROLE_USER)]
    public function account(ChangePasswordFormProcessor $changePasswordFormProcessor): Response
    {
        return $this->renderPage('account', [
            'change_password_form' => $changePasswordFormProcessor->createForm()->createView(),
        ]);
    }
}
