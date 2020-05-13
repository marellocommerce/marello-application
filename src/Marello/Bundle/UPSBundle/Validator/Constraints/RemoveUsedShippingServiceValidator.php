<?php

namespace Marello\Bundle\UPSBundle\Validator\Constraints;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Marello\Bundle\ShippingBundle\Method\Factory\IntegrationShippingMethodFactoryInterface;
use Marello\Bundle\ShippingBundle\Method\Validator\Result\ShippingMethodValidatorResultInterface;
use Marello\Bundle\ShippingBundle\Method\Validator\ShippingMethodValidatorInterface;
use Marello\Bundle\UPSBundle\Entity\UPSSettings;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RemoveUsedShippingServiceValidator extends ConstraintValidator
{
    const ALIAS = 'marello_ups_remove_used_shipping_service_validator';

    /**
     * @internal
     */
    const VIOLATION_PATH = 'applicableShippingServices';

    /**
     * @var IntegrationShippingMethodFactoryInterface
     */
    private $integrationShippingMethodFactory;

    /**
     * @var ShippingMethodValidatorInterface
     */
    private $shippingMethodValidator;

    /**
     * @param IntegrationShippingMethodFactoryInterface $integrationShippingMethodFactory
     * @param ShippingMethodValidatorInterface          $shippingMethodValidator
     */
    public function __construct(
        IntegrationShippingMethodFactoryInterface $integrationShippingMethodFactory,
        ShippingMethodValidatorInterface $shippingMethodValidator
    ) {
        $this->integrationShippingMethodFactory = $integrationShippingMethodFactory;
        $this->shippingMethodValidator = $shippingMethodValidator;
    }

    /**
     * @param UPSSettings                                   $value
     * @param Constraint|RemoveUsedShippingServiceConstraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof UPSSettings) {
            return;
        }

        if (!$value->getChannel() instanceof Channel) {
            return;
        }

        $upsShippingMethod = $this->integrationShippingMethodFactory->create($value->getChannel());
        $shippingMethodValidatorResult = $this->shippingMethodValidator->validate($upsShippingMethod);

        $this->handleValidationResult($shippingMethodValidatorResult);
    }

    /**
     * @param ShippingMethodValidatorResultInterface $shippingMethodValidatorResult
     */
    private function handleValidationResult(ShippingMethodValidatorResultInterface $shippingMethodValidatorResult)
    {
        if ($shippingMethodValidatorResult->getErrors()->isEmpty()) {
            return;
        }

        /** @var ExecutionContextInterface $context */
        $context = $this->context;

        foreach ($shippingMethodValidatorResult->getErrors() as $error) {
            $context->buildViolation($error->getMessage())
                ->setTranslationDomain(null)
                ->atPath(static::VIOLATION_PATH)
                ->addViolation();
        }
    }
}
