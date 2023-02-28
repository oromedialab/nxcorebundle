<?php

declare(strict_types=1);

namespace OroMediaLab\NxCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\DiscriminatorMap(['admin' => 'UserAdmin'])]
class UserAdmin extends User
{
    #[ORM\Column(type: 'string')]
    protected $role = 'ROLE_ADMIN';

    public function getRole()
    {
        return $this->role;
    }
}
