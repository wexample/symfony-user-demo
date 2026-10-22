<?php

namespace Wexample\SymfonyUserDemo\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Wexample\SymfonyHelpers\Command\AbstractBundleCommand;
use Wexample\SymfonyHelpers\Service\BundleService;
use Wexample\SymfonyUserDemo\Enum\DemoAccount;
use Wexample\SymfonyUserDemo\Service\DemoAccountsService;
use Wexample\SymfonyUserDemo\WexampleSymfonyUserDemoBundle;

class CreateAccountsCommand extends AbstractBundleCommand
{
    protected static $defaultDescription = 'Creates or resets the test accounts of the user demo pages';

    public function __construct(
        BundleService $bundleService,
        private readonly DemoAccountsService $demoAccounts,
        ?string $name = null,
    ) {
        parent::__construct($bundleService, $name);
    }

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyUserDemoBundle::class;
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $this->demoAccounts->reset();

        foreach (DemoAccount::cases() as $account) {
            $output->writeln($account->getEmail());
        }

        return self::SUCCESS;
    }
}
