<?php

namespace Marello\Bundle\Magento2Bundle\Validator;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\ORM\PersistentCollection;
use Marello\Bundle\Magento2Bundle\Provider\Magento2ChannelType;
use Marello\Bundle\Magento2Bundle\Provider\TrackedSalesChannelProvider;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SalesChannelsAttachedToIntegrationValidator extends ConstraintValidator
{
    /** @var TrackedSalesChannelProvider */
    protected $salesChannelInfosProvider;

    /**
     * @param TrackedSalesChannelProvider $salesChannelInfosProvider
     */
    public function __construct(TrackedSalesChannelProvider $salesChannelInfosProvider)
    {
        $this->salesChannelInfosProvider = $salesChannelInfosProvider;
    }

    /**
     * @param SalesChannelGroup $value
     * @param Constraints\SalesChannelsAttachedToIntegration $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value->getId() ||
            null === $value->getIntegrationChannel() ||
            $value->getIntegrationChannel()->getType() !== Magento2ChannelType::TYPE) {
            return;
        }

        $salesChannels = $value->getSalesChannels();
        if ($salesChannels instanceof AbstractLazyCollection && !$salesChannels->isInitialized()) {
            return;
        }

        if ($salesChannels instanceof PersistentCollection && !$salesChannels->isDirty()) {
            return;
        }

        if ($salesChannels instanceof PersistentCollection) {
            $salesChannels = $salesChannels->unwrap();
        }

        $salesChannelIdsUsedInIntegration = $this->salesChannelInfosProvider->getSalesChannelIdsByIntegrationId(
            $value->getIntegrationChannel()->getId()
        );

        if (empty($salesChannelIdsUsedInIntegration)) {
            return;
        }

        $existingSalesChannelIds = $salesChannels->map(function (SalesChannel $salesChannel) {
            return $salesChannel->getId();
        })->toArray();

        $restrictedToRemoveSalesChannelIds = \array_diff($salesChannelIdsUsedInIntegration, $existingSalesChannelIds);
        if (empty($restrictedToRemoveSalesChannelIds)) {
            return;
        }

        $violationBuilder = $this->context
            ->buildViolation($constraint->message)
            ->setParameters([
                '{{ forbidden_to_remove_sales_channel_ids }}'  => implode(', ', $restrictedToRemoveSalesChannelIds)
            ]);

        $violationBuilder->addViolation();
    }
}
