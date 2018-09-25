<?php

namespace Marello\Bundle\SalesBundle\Condition;

use Marello\Bundle\SalesBundle\Entity\SalesChannel;
use Oro\Bundle\IntegrationBundle\Manager\TypesRegistry;
use Oro\Bundle\IntegrationBundle\Provider\PingableInterface;
use Oro\Component\ConfigExpression\Condition\AbstractCondition;
use Oro\Component\ConfigExpression\ContextAccessorAwareInterface;
use Oro\Component\ConfigExpression\ContextAccessorAwareTrait;
use Oro\Component\ConfigExpression\Exception\InvalidArgumentException;

/**
 * Check if integration valid
 * Usage:
 * @sales_channel_has_valid_integration:
 *      salesChannel: ~
 */
class HasValidIntegration extends AbstractCondition implements ContextAccessorAwareInterface
{
    use ContextAccessorAwareTrait;

    const NAME = 'sales_channel_has_valid_integration';

    /**
     * @var TypesRegistry
     */
    protected $integrationTypesRegistry;

    /**
     * @var SalesChannel
     */
    protected $salesChannel;

    public function __construct(TypesRegistry $integrationTypesRegistry)
    {
        $this->integrationTypesRegistry = $integrationTypesRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (array_key_exists('salesChannel', $options)) {
            $this->salesChannel = $options['salesChannel'];
        } elseif (array_key_exists(0, $options)) {
            $this->salesChannel = $options[0];
        }

        if (!$this->salesChannel) {
            throw new InvalidArgumentException('Missing "salesChannel" option');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    protected function isConditionAllowed($context)
    {
        /** @var SalesChannel $salesChannel */
        $salesChannel = $this->resolveValue($context, $this->salesChannel, false);

        if (null !== $salesChannel && $channel = $salesChannel->getIntegrationChannel()) {
            if ($channel->isEnabled()) {
                $transportEntity = $channel->getTransport();
                $transport = $this->integrationTypesRegistry->getTransportTypeBySettingEntity(
                    $transportEntity,
                    $channel->getType()
                );
                if ($transport && $transport instanceof PingableInterface) {
                    $transport->init($transportEntity);

                    $pingResult = $transport->ping();
                    if (false === $pingResult) {
                        $this->setMessage(
                            'Integration connection test for this Sales Channel was failed,
                             please check settings or internet connection before proceed'
                        );
                    }

                    return $pingResult;
                }
            }
            $this->setMessage('Integration for this Sales Channel is disabled, please enable it before proceed');
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return $this->convertToArray([$this->salesChannel]);
    }

    /**
     * {@inheritdoc}
     */
    public function compile($factoryAccessor)
    {
        return $this->convertToPhpCode([$this->salesChannel], $factoryAccessor);
    }
}
