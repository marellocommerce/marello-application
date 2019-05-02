<?php

namespace Marello\Bundle\SalesBundle\Provider;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class EnabledSalesChannelsChoicesProviderDecorator implements SalesChannelsChoicesProviderInterface
{
    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannels()
    {
        return array_reduce(
            $this->doctrineHelper
                ->getEntityManagerForClass(SalesChannel::class)
                ->getRepository(SalesChannel::class)
                ->findBy(['active' => true]),
            function (array $result, SalesChannel $channel) {
                $label = $channel->getName();
                $result[$label] = $channel->getCode();

                return $result;
            },
            []
        );
    }
}
