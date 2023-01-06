<?php

namespace OroMediaLab\NxCoreBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use OroMediaLab\NxCoreBundle\Tests\DatabasePrimer;

abstract class BaseTest extends KernelTestCase
{
    protected $em;

    protected function setUp(): void
    {
        parent::setUp();
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        DatabasePrimer::prime($kernel);
        $this->em = $container->get('doctrine')->getManager();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->em->close();
        $this->em = null;
    }
}
