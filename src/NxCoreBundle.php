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
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
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
            ->end()
        ;
    }
}
