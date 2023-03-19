<?php

namespace OroMediaLab\NxCoreBundle;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

class NxCoreBundle extends AbstractBundle
{
    protected string $extensionAlias = 'nxcore';

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
        $container->services()->get('nxcore.controller.v1.contact_message_controller')->arg(0, $config['email_messages']['contact_form']);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()->children()
            ->arrayNode('email_messages')
                ->children()
                    ->arrayNode('contact_form')
                        ->children()
                            ->scalarNode('subject')->end()
                            ->scalarNode('from')->end()
                            ->scalarNode('to')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
            ->arrayNode('validators')
                ->children()
                    ->arrayNode('namespaces')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('name')->end()
                                ->scalarNode('class')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('templates')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('name')->end()
                                ->scalarNode('class')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('routes')
                        ->arrayPrototype()
                            ->children()
                                ->scalarNode('route')->end()
                                ->arrayNode('validators')
                                    ->arrayPrototype()
                                        ->children()
                                            ->scalarNode('field')->end()
                                            ->scalarNode('required')->end()
                                            ->scalarNode('constraint')->end()
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();
    }
}
