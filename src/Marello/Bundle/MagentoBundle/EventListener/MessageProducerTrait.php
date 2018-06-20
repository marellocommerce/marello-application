<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 12/06/2018
 * Time: 12:35
 */

namespace Marello\Bundle\MagentoBundle\EventListener;

use Oro\Component\MessageQueue\Client\MessageProducerInterface;

trait MessageProducerTrait
{
    protected $messageProducer;

    /**
     * @return mixed
     */
    public function getMessageProducer()
    {
        return $this->messageProducer;
    }

    /**
     * @param $messageProducer
     * @return $this
     */
    public function setMessageProducer(MessageProducerInterface $messageProducer)
    {
        $this->messageProducer = $messageProducer;

        return $this;
    }
}
