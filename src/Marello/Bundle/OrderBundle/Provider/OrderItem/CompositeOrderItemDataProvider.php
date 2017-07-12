<?php

namespace Marello\Bundle\OrderBundle\Provider\OrderItem;

class CompositeOrderItemDataProvider implements OrderItemDataProviderInterface
{
    /**
     * @var OrderItemDataProviderInterface[]
     */
    protected $providers = [];

    /**
     * @var $translator
     */
    protected $translator;

    /**
     * @param $translator
     */
    public function __construct($translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param string $identifier
     * @param OrderItemDataProviderInterface $provider
     */
    public function addProvider($identifier, OrderItemDataProviderInterface $provider)
    {
        $this->providers[$identifier] = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getData($channelId, array $products)
    {
        $result = [];
        $productCount = count($products);

        foreach ($this->providers as $type => $provider) {
            $providerData = $provider->getData($channelId, $products);

            foreach ($providerData as $identifier => $data) {
                $result[$identifier][$type] = $data;
            }
            $resultCount = count($result);

            if ($productCount !== $resultCount) {
                foreach ($products as $product) {
                    if (!array_key_exists(self::IDENTIFIER_PREFIX . $product['product'], $result)) {
                        $result[self::IDENTIFIER_PREFIX . $product['product']] = [
                            'message' => $this->translator
                                ->trans('marello.order.orderitem.messages.product_not_salable'),
                        ];
                    }
                }
            }
        }

        return $result;
    }
}
