<?php

namespace Wexample\SymfonyUserDemo\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('wexample_symfony_user_demo');

        $treeBuilder->getRootNode()
            ->children()
                ->booleanNode('email_two_factor')
                    ->info('Whether the demo accounts ask the code sent by mail at sign-in. Their addresses receive nothing (example.com): '
                        .'only where the development mailbox keeps the mails can a visitor read the code. Taken on the next reset of the accounts.')
                    ->defaultFalse()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
