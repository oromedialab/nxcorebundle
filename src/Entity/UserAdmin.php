<?php

declare(strict_types=1);

namespace OroMediaLab\NxCoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\DiscriminatorMap(['admin' => 'UserAdmin'])]
class UserAdmin extends User
{
    // Role is now handled by the Role entity relationship in parent User class
}
