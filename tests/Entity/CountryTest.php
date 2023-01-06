<?php

namespace OroMediaLab\NxCoreBundle\Tests\Entity;

use OroMediaLab\NxCoreBundle\Entity\Country;

class CountryTest extends BaseTest
{
    public function testItContainsProperties()
    {
        $this->assertClassHasAttribute('id', Country::class, 'Property `id` does not exist');
        $this->assertClassHasAttribute('uuid', Country::class, 'Property `uuid` does not exist');
        $this->assertClassHasAttribute('alpha2Code', Country::class, 'Property `alpha2Code` does not exist');
        $this->assertClassHasAttribute('alpha3Code', Country::class, 'Property `alpha3Code` does not exist');
        $this->assertClassHasAttribute('currency', Country::class, 'Property `currency` does not exist');
        $this->assertClassHasAttribute('callingCode', Country::class, 'Property `callingCode` does not exist');
        $this->assertClassHasAttribute('slug', Country::class, 'Property `slug` does not exist');
        $this->assertClassHasAttribute('createdAt', Country::class, 'Property `createdAt` does not exist');
        $this->assertClassHasAttribute('updatedAt', Country::class, 'Property `updatedAt` does not exist');
    }

    public function testItContainsMethods()
    {
        $this->assertTrue(method_exists(Country::class, 'getId'), 'Method `getId` does not exist');
        $this->assertTrue(method_exists(Country::class, 'getUuid'), 'Method `getUuid` does not exist');
        $this->assertTrue(method_exists(Country::class, 'setAlpha2Code'), 'Method `setAlpha2Code` does not exist');
        $this->assertTrue(method_exists(Country::class, 'getAlpha2Code'), 'Method `getAlpha2Code` does not exist');
        $this->assertTrue(method_exists(Country::class, 'setAlpha3Code'), 'Method `setAlpha3Code` does not exist');
        $this->assertTrue(method_exists(Country::class, 'getAlpha3Code'), 'Method `getAlpha3Code` does not exist');
        $this->assertTrue(method_exists(Country::class, 'setCurrency'), 'Method `setCurrency` does not exist');
        $this->assertTrue(method_exists(Country::class, 'getCurrency'), 'Method `getCurrency` does not exist');
        $this->assertTrue(method_exists(Country::class, 'setCallingCode'), 'Method `setCallingCode` does not exist');
        $this->assertTrue(method_exists(Country::class, 'getCallingCode'), 'Method `getCallingCode` does not exist');
        $this->assertTrue(method_exists(Country::class, 'setSlug'), 'Method `setSlug` does not exist');
        $this->assertTrue(method_exists(Country::class, 'getSlug'), 'Method `getSlug` does not exist');
        $this->assertTrue(method_exists(Country::class, 'setFlag'), 'Method `setFlag` does not exist');
        $this->assertTrue(method_exists(Country::class, 'getFlag'), 'Method `getFlag` does not exist');
        $this->assertTrue(method_exists(Country::class, 'getCreatedAt'), 'Method `getCreatedAt` does not exist');
        $this->assertTrue(method_exists(Country::class, 'getUpdatedAt'), 'Method `getUpdatedAt` does not exist');
    }
}
