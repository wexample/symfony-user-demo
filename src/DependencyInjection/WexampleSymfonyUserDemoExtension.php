<?php

namespace Wexample\SymfonyUserDemo\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Wexample\SymfonyHelpers\DependencyInjection\AbstractWexampleSymfonyExtension;

class WexampleSymfonyUserDemoExtension extends AbstractWexampleSymfonyExtension
{
    public const string PARAMETER_EMAIL_TWO_FACTOR = 'wexample_symfony_user_demo.email_two_factor';

    public function load(
        array $configs,
        ContainerBuilder $container
    ): void {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $container->setParameter(self::PARAMETER_EMAIL_TWO_FACTOR, $config['email_two_factor']);

        $this->loadConfig(
            __DIR__,
            $container
        );
    }
}
