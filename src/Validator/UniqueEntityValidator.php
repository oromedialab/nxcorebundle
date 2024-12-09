<?php

namespace OroMediaLab\NxCoreBundle\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueEntityValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueEntity) {
            throw new UnexpectedTypeException($constraint, UniqueEntity::class);
        }
        $recordExist = $this->entityManager->getRepository($constraint->getEntityFqcn())->findOneBy([$constraint->getProperty() => $value]);
        if ($recordExist) {
            $this->context->buildViolation($constraint->getMessage())->addViolation();
        }
    }
}
