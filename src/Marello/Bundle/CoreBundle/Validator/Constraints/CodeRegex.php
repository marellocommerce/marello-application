<?php

namespace Marello\Bundle\CoreBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for entity 'code' attribute
 */
class CodeRegex extends Constraint
{
    /**
     * @var string
     */
    public $message = 'marello.core.code.not_match_regex';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'marello_core.validator_constraints.code_regex_validator';
    }
}
