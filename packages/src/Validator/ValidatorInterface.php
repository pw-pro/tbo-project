<?php

declare(strict_types=1);

namespace Tab\Packages\Validator;

interface ValidatorInterface
{
    /** @param null|string[] $validationGroups */
    public function validate(object $object, ?array $validationGroups = null): Violations;
}