<?php

namespace Marello\Bundle\RuleBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ExpressionLanguageSyntax extends Constraint
{
    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'marello_rule.validator_constraints.expression_language_syntax_validator';
    }
}
