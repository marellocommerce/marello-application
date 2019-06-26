<?php

namespace Marello\Bundle\SubscriptionBundle\Provider;

use Marello\Bundle\CoreBundle\Model\AdditionalPlaceholderDataInterface;

class AdditionalPlaceholderDataProvider implements AdditionalPlaceholderDataInterface
{
    /**
     * {@inheritdoc}
     * @return string
     */
    public function getName()
    {
        return 'customer_subscriptions';
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getLabel()
    {
        return 'marello.subscription.entity_plural_label';
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getPlaceholder()
    {
        return 'marello_customer_additional_info_subscriptions';
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function getPlaceHolderSections()
    {
        return ['customer'];
    }
}
