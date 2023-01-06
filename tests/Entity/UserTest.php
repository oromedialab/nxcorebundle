<?php

namespace OroMediaLab\NxCoreBundle\Tests\Entity;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use OroMediaLab\NxCoreBundle\Entity\User;

class UserTest extends BaseTest
{
    protected function initPasswordHasher(): UserPasswordHasher
    {
        $passwordHasherFactory = new PasswordHasherFactory([
            PasswordAuthenticatedUserInterface::class => ['algorithm' => 'auto'],
        ]);
        return new UserPasswordHasher($passwordHasherFactory);
    }

    protected function createAndFetchUserEntity()
    {
        $passwordHasher = $this->initPasswordHasher();
        $user = new User;
        $user->setName('Test User');
        $user->setUsername('test_user');
        $user->setPassword($passwordHasher->hashPassword($user, 'test_password'));
        $this->em->persist($user);
        $this->em->flush();
        return $this->em->getRepository(User::class)->findOneByUsername('test_user');
    }

    public function testItCanCreateUser()
    {
        $passwordHasher = $this->initPasswordHasher();
        $user = $this->createAndFetchUserEntity();
        $this->assertEquals(36, strlen($user->getUuid()), 'User does not contain valid uuid');
        $this->assertEquals('Test User', $user->getname(), 'User not found with name `Test User`');
        $this->assertEquals('test_user', $user->getUsername(), 'User not found with username `test_user`');
        $this->assertTrue($passwordHasher->isPasswordValid($user, 'test_password'), 'User has invalid password');
        $this->assertTrue($user->isEnabled(), 'User is not enabled');
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getCreatedAt(), 'User does not contain created_at timestamp');
        $this->assertInstanceOf(\DateTimeInterface::class, $user->getUpdatedAt(), 'User does not contain updated_at timestamp');
    }

    public function testItContainsProperties()
    {
        $this->assertClassHasAttribute('id', User::class, 'Property `id` does not exist');
        $this->assertClassHasAttribute('uuid', User::class, 'Property `uuid` does not exist');
        $this->assertClassHasAttribute('username', User::class, 'Property `username` does not exist');
        $this->assertClassHasAttribute('password', User::class, 'Property `password` does not exist');
        $this->assertClassHasAttribute('name', User::class, 'Property `name` does not exist');
        $this->assertClassHasAttribute('emailAddress', User::class, 'Property `emailAddress` does not exist');
        $this->assertClassHasAttribute('contactNumber', User::class, 'Property `contactNumber` does not exist');
        $this->assertClassHasAttribute('enabled', User::class, 'Property `password` does not exist');
        $this->assertClassHasAttribute('createdAt', User::class, 'Property `createdAt` does not exist');
        $this->assertClassHasAttribute('updatedAt', User::class, 'Property `updatedAt` does not exist');
    }

    public function testItContainsMethods()
    {
        $this->assertTrue(method_exists(User::class, 'getId'), 'Method `getId` does not exist');
        $this->assertTrue(method_exists(User::class, 'getUuid'), 'Method `getUuid` does not exist');
        $this->assertTrue(method_exists(User::class, 'setUsername'), 'Method `setUsername` does not exist');
        $this->assertTrue(method_exists(User::class, 'getUsername'), 'Method `getUsername` does not exist');
        $this->assertTrue(method_exists(User::class, 'setPassword'), 'Method `setPassword` does not exist');
        $this->assertTrue(method_exists(User::class, 'getPassword'), 'Method `getPassword` does not exist');
        $this->assertTrue(method_exists(User::class, 'setName'), 'Method `setName` does not exist');
        $this->assertTrue(method_exists(User::class, 'getName'), 'Method `getName` does not exist');
        $this->assertTrue(method_exists(User::class, 'setEmailAddress'), 'Method `setEmailAddress` does not exist');
        $this->assertTrue(method_exists(User::class, 'getEmailAddress'), 'Method `getEmailAddress` does not exist');
        $this->assertTrue(method_exists(User::class, 'setContactNumber'), 'Method `setContactNumber` does not exist');
        $this->assertTrue(method_exists(User::class, 'getContactNumber'), 'Method `getContactNumber` does not exist');
        $this->assertTrue(method_exists(User::class, 'setEnabled'), 'Method `setEnabled` does not exist');
        $this->assertTrue(method_exists(User::class, 'isEnabled'), 'Method `isEnabled` does not exist');
        $this->assertTrue(method_exists(User::class, 'getCreatedAt'), 'Method `getCreatedAt` does not exist');
        $this->assertTrue(method_exists(User::class, 'getUpdatedAt'), 'Method `getUpdatedAt` does not exist');
    }
}
