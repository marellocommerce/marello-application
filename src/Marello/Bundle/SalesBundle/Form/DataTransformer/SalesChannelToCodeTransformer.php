<?php

namespace Marello\Bundle\SalesBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class SalesChannelToCodeTransformer implements DataTransformerInterface
{
    /** @var ObjectManager */
    protected $om;

    /**
     * SalesChannelToCodeTransformer constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($value)
    {
        if (!$value) {
            return null;
        }

        if (!$value instanceof SalesChannel) {
            throw new TransformationFailedException();
        }

        return $value->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return null;
        }

        $channel = $this->om->getRepository(SalesChannel::class)->findOneBy(['code' => $value]);

        if (!$channel) {
            throw new TransformationFailedException();
        }

        return $channel;
    }
}
