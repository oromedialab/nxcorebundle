<?php

declare(strict_types=1);

namespace OroMediaLab\NxCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\UuidableInterface;
use Knp\DoctrineBehaviors\Model\Uuidable\UuidableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

#[ORM\Entity()]
#[ORM\Table(name: "country")]
class Country implements UuidableInterface, TimestampableInterface, TranslatableInterface
{
    use UuidableTrait;
    use TimestampableTrait;
    use TranslatableTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(name: 'alpha2_code', length: 2)]
    private string $alpha2Code;

    #[ORM\Column(name: 'alpha3_code', length: 3)]
    private string $alpha3Code;

    #[ORM\Column(name: 'currency', length: 10)]
    private string $currency;

    #[ORM\Column(name: 'calling_code', length: 5)]
    private string $callingCode;

    #[ORM\Column(name: 'flag', type: 'text')]
    private string $flag;

    #[ORM\Column(name: 'slug', length: 50, nullable: true)]
    private string $slug;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setAlpha2Code(string $alpha2Code): self
    {
        $this->alpha2Code = $alpha2Code;
        return $this;
    }

    public function getAlpha2Code(): string
    {
        return $this->alpha2Code;
    }

    public function setAlpha3Code(string $alpha3Code): self
    {
        $this->alpha3Code = $alpha3Code;
        return $this;
    }

    public function getAlpha3Code(): string
    {
        return $this->alpha3Code;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCallingCode(string $callingCode): self
    {
        $this->callingCode = $callingCode;
        return $this;
    }

    public function getCallingCode(): string
    {
        return $this->callingCode;
    }

    public function setFlag(string $flag): self
    {
        $this->flag = $flag;
        return $this;
    }

    public function getFlag(): string
    {
        return $this->flag;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}
