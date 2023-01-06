<?php

declare(strict_types=1);

namespace OroMediaLab\NxCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\UuidableInterface;
use Knp\DoctrineBehaviors\Model\Uuidable\UuidableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity()]
#[ORM\Table(name: "user")]
class User implements UuidableInterface, TimestampableInterface, PasswordAuthenticatedUserInterface
{
    use UuidableTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 50)]
    private string $username;

    #[ORM\Column(length: 100)]
    private string $password;

    #[ORM\Column(length: 50)]
    private string $name;

    #[ORM\Column(length: 50, nullable: true)]
    private string $emailAddress;

    #[ORM\Column(length: 50, nullable: true)]
    private string $contactNumber;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setName(string $name): User
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setEmailAddress(string $emailAddress): User
    {
        $this->emailAddress = $emailAddress;
        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setContactNumber(string $contactNumber): User
    {
        $this->contactNumber = $contactNumber;
        return $this;
    }

    public function getContactNumber(): ?string
    {
        return $this->contactNumber;
    }

    public function setEnabled(bool $enabled): User
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
