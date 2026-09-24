<?php

namespace Wexample\SymfonyUserDemo\Controller\Pages\DesignSystem;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyLoader\Controller\AbstractPagesController;
use Wexample\SymfonyLoader\Controller\Pages\AbstractDesignSystemController;
use Wexample\SymfonyUserDemo\Traits\SymfonyUserDemoBundleClassTrait;

#[Route(
    name: 'wexample_user_demo_',
    path: AbstractDesignSystemController::CONTROLLER_BASE_ROUTE . '/user/',
)]
final class UserController extends AbstractPagesController
{
    use SymfonyUserDemoBundleClassTrait;

    #[Route(name: 'index', path: '')]
    public function index(): Response
    {
        return $this->renderPage('index');
    }
}
