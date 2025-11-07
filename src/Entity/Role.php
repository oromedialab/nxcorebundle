<?php

declare(strict_types=1);

namespace OroMediaLab\NxCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\UuidableInterface;
use Knp\DoctrineBehaviors\Model\Uuidable\UuidableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;


#[ORM\Entity]
#[ORM\Table(name: "role")]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'dtype', type: 'string')]
#[ORM\DiscriminatorMap(['role' => 'OroMediaLab\NxCoreBundle\Entity\Role', 'app_role' => 'App\Entity\Role'])]
class Role implements UuidableInterface, TimestampableInterface
{
    use UuidableTrait;
    use TimestampableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(type: 'string', length: 50, unique: true, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $permissions = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }

    public function setPermissions(?array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function hasPermission(string $permission): bool
    {
        $currentPermissions = $this->getPermissions();
        return in_array($permission, $currentPermissions, true);
    }

    public function hasPermissions(array $permissions): array
    {
        $currentPermissions = $this->getPermissions();
        $results = [];
        
        foreach ($permissions as $permission) {
            $results[] = in_array($permission, $currentPermissions, true);
        }
        
        return $results;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}