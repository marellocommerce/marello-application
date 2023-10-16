<?php

namespace Marello\Bundle\RuleBundle\Validator\Constraints;

use Oro\Component\ExpressionLanguage\BasicExpressionLanguageValidator;
use Oro\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExpressionLanguageSyntaxValidator extends ConstraintValidator
{
    /**
     * @var ExpressionLanguage
     */
    private $expressionParser;

    public function __construct(ExpressionLanguage $expressionParser)
    {
        $this->expressionParser = $expressionParser;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        try {
            $this->expressionParser->parse($value, []);
        } catch (SyntaxError $ex) {
            $this->context->addViolation($ex->getMessage());
        }
    }
}
