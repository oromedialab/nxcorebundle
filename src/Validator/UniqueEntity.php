<?php

namespace OroMediaLab\NxCoreBundle\Validator;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class UniqueEntity extends Constraint
{
    protected string $property;
    
    protected string $entityFqcn;

    protected string $message = 'A record matching the criteria already exists.';

    #[HasNamedArguments]
    public function __construct(
        string $property,
        string $entityFqcn,
        ?string $message = null,
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct(
            options: [
                'property' => $property,
                'entityFqcn' => $entityFqcn,
                'message' => $message ?? $this->message,
            ],
            groups: $groups,
            payload: $payload
        );

        $this->property = $property;
        $this->entityFqcn = $entityFqcn;
        $this->message = $message ?? $this->message;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getEntityFqcn(): string
    {
        return $this->entityFqcn;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getRequiredOptions(): array
    {
        return ['property', 'entityFqcn'];
    }
}
