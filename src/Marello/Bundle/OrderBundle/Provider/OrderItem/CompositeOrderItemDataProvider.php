<?php

namespace Marello\Bundle\OrderBundle\Provider\OrderItem;

use Symfony\Component\Translation\TranslatorInterface;

class CompositeOrderItemDataProvider implements OrderItemDataProviderInterface
{
    /**
     * @var OrderItemDataProviderInterface[]
     */
    protected $providers = [];

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
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
            $resultCount = count($providerData);

            if ($productCount !== $resultCount) {
                foreach ($products as $product) {
                    if (!array_key_exists(self::IDENTIFIER_PREFIX . $product, $result) ||
                    !array_key_exists($type, $result[self::IDENTIFIER_PREFIX . $product])) {
                        $result[self::IDENTIFIER_PREFIX . $product] = [
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
