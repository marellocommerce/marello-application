<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 20/04/2018
 * Time: 13:40
 */

namespace Marello\Bundle\MageBridgeBundle\Provider;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;

class SalesChannelProvider
{
    /** @var ObjectManager $manager */
    protected $manager;

    /**
     * ChannelProvider constructor.
     * @param ObjectManager $manager
     */
    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    public function getSalesChannelList()
    {
        $result = $this->manager
            ->getRepository(SalesChannel::class)
            ->findBy(['channelType' => 'magento']);

        $formatedResult = [];
        foreach($result as $channel) {
            $formatedResult[$channel->getId()] = $channel->getName();
        }

        return $formatedResult;
    }
}
