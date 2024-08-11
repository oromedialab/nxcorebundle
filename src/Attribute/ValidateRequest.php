<?php

namespace OroMediaLab\NxCoreBundle\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class ValidateRequest
{
    public function __construct(
        public array $rules
    ) {}

    public function getRules()
    {
        return $this->rules;
    }
}
