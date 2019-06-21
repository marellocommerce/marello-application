<?php

namespace Marello\Bundle\SubscriptionBundle\Twig;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Marello\Bundle\SubscriptionBundle\Migrations\Data\ORM\LoadPaymentTermData;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

class SubscriptionExtension extends \Twig_Extension
{
    const NAME = 'marello_subscription';

    /**
     * @var Registry
     */
    private $doctrine;

    /**
     * @param Registry $doctrine
     */
    public function __construct(
        Registry $doctrine
    ) {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                'format_payment_freq',
                array($this, 'formatPaymentFreq')
            ),
        );
    }

    /**
     * @param string $paymentFreq
     * @return string
     */
    public function formatPaymentFreq($paymentFreq)
    {
        $className = ExtendHelper::buildEnumValueClassName(LoadPaymentTermData::ENUM_CLASS);

        /** @var EnumValueRepository $enumRepo */
        $enumRepo = $this->doctrine->getManagerForClass($className)->getRepository($className);
        /** @var AbstractEnumValue[] $enumValues */
        $enumValues = $enumRepo->findAll();
        foreach ($enumValues as $enumVal) {
            if ((int)$enumVal->getId() === $paymentFreq) {
                return $enumVal->getName();
            }
        }

        return $paymentFreq;
    }
    
    /**
     * {@deprecated}
     */
    public function getName()
    {
        return self::NAME;
    }
}
