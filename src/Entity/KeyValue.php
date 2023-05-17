<?php

declare(strict_types=1);

namespace OroMediaLab\NxCoreBundle\Entity;

use Knp\DoctrineBehaviors\Contract\Entity\UuidableInterface;
use Knp\DoctrineBehaviors\Model\Uuidable\UuidableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OroMediaLab\NxCoreBundle\Repository\KeyValueRepository;

#[ORM\Entity(repositoryClass: KeyValueRepository::class)]
class KeyValue implements UuidableInterface, TimestampableInterface
{
    use UuidableTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $keyName = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $keyValue = null;

    #[ORM\ManyToOne]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getKeyName(): ?string
    {
        return $this->keyName;
    }

    public function setKeyName(string $keyName): self
    {
        $this->keyName = $keyName;
        return $this;
    }

    public function getKeyValue(): ?string
    {
        return $this->keyValue;
    }

    public function setKeyValue(?string $keyValue): self
    {
        $this->keyValue = $keyValue;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;
        return $this;
    }
}
