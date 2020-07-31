<?php

namespace Marello\Bundle\Magento2Bundle\Validator;

use Doctrine\Common\Collections\Collection;
use Marello\Bundle\Magento2Bundle\Model\WebsiteToSalesChannelMapItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ValidWebsiteToSalesChannelMapItemsValidator extends ConstraintValidator
{
    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Collection $value
     * @param Constraints\ValidWebsiteToSalesChannelMapItems $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Collection) {
            throw new UnexpectedTypeException($value, Collection::class);
        }

        if ($value->count() < 1) {
            $violationBuilder = $this->context
                ->buildViolation('Collection should contains at least one item.');

            $violationBuilder->addViolation();

            return;
        }

        foreach ($value as $index => $mapItem) {
            if (!$mapItem instanceof WebsiteToSalesChannelMapItem) {
                throw new UnexpectedTypeException($mapItem, WebsiteToSalesChannelMapItem::class);
            }

            $validationResult = $this->validator->validate($mapItem);
            if ($validationResult->count()) {
                $validationMessages = \array_map(function (ConstraintViolationInterface $constraintViolation) {
                    return 'property: ' . $constraintViolation->getPropertyPath() .
                        ', message: ' . $constraintViolation->getMessage();
                }, \iterator_to_array($validationResult));

                $violationBuilder = $this->context
                    ->buildViolation(
                        'Map item with index {{ index }} is invalid. Validation messages: {{ validation_messages }}'
                    )->setParameter(
                        '{{ index }}', $index
                    )->setParameter(
                        '{{ validation_messages }}', implode('; ', $validationMessages)
                    )
                ;

                $violationBuilder->addViolation();
            }
        }
    }
}
