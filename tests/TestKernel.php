<?php

namespace OroMediaLab\NxCoreBundle\Tests;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class TestKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function __construct()
    {
        parent::__construct('test', true);
    }

    protected function configureContainer(ContainerConfigurator $c): void
    {
        $c->import(__DIR__.'/../config/framework.yaml');
        $c->import(__DIR__.'/../config/services.yaml');
        $c->import(__DIR__.'/../config/doctrine.yaml');
        $c->import(__DIR__.'/../config/security.yaml');
    }
}
