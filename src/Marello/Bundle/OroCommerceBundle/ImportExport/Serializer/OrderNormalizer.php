<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\OrderBundle\Entity\Customer;
use Marello\Bundle\OrderBundle\Entity\Order;
use Marello\Bundle\OrderBundle\Entity\OrderItem;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\AbstractExportWriter;
use Marello\Bundle\OroCommerceBundle\ImportExport\Writer\OrderExportWriter;
use Marello\Bundle\PricingBundle\DependencyInjection\Configuration;
use Marello\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DenormalizerInterface;

class OrderNormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    const PAYMENT_STATUS = 'orocommerce_payment_status';
    const PAID_FULLY_STATUS = 'full';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @param Registry $registry
     * @param ConfigManager $configManager
     */
    public function __construct(Registry $registry, ConfigManager $configManager)
    {
        parent::__construct($registry);
        $this->configManager = $configManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null, array $context = array())
    {
        return ($data instanceof Order && isset($context['channel']) &&
            $this->getIntegrationChannel($context['channel']));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null, array $context = array())
    {
        return isset($data['type']) && $data['type'] === 'orders' && ($type == Order::class);
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if ($object instanceof Order && $object->getOrderReference()) {
            if (in_array(
                $context[AbstractExportWriter::ACTION_FIELD],
                [OrderExportWriter::CANCEL_ACTION, OrderExportWriter::SHIPPED_ACTION]
            )) {
                return [
                    'data' => [
                        'type' => 'orders',
                        'id' => $object->getOrderReference(),
                        'relationships' => [
                            'internal_status' => [
                                'data' => [
                                    'type' => 'orderinternalstatuses',
                                    'id' => $context[AbstractExportWriter::ACTION_FIELD]
                                ]
                            ]
                        ]
                    ]
                ];
            } elseif ($context[AbstractExportWriter::ACTION_FIELD] === OrderExportWriter::PAID_ACTION) {
                return [
                    'data' => [
                        'type' => 'paymentstatuses',
                        'attributes' => [
                            'entityClass' => 'Oro\Bundle\OrderBundle\Entity\Order',
                            'entityIdentifier' => $object->getOrderReference(),
                            'paymentStatus' => self::PAID_FULLY_STATUS
                        ]
                    ]
                ];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        /** @var Order $order */
        $order = $this->createOrder($data);
        return $order->getItems()->count() > 0 ? $order : null;
    }

    /**
     * @param array $data
     * @return Order
     */
    public function createOrder(array $data)
    {
        $paymentStatus = $this->getProperty($data, 'paymentStatus');
        $taxValue = $this->getProperty($data, 'taxvalues');
        $result = $this->getProperty($taxValue, 'result');
        $shipping = $this->getProperty($result, 'shipping');
        $total = $this->getProperty($result, 'total');
        $subtotal = (float)$this->getProperty($data, 'subtotalValue');
        if ($this->configManager->get(Configuration::VAT_SYSTEM_CONFIG_PATH)) {
            $subtotal = $subtotal + (float)$total['taxAmount'];
        }
        $order = new Order();
        $customer = $this->prepareCustomer($data);
        if ($paymentStatus) {
            $order->setData([
                self::PAYMENT_STATUS => $this->getProperty($paymentStatus, 'paymentStatus')
            ]);
        }

        $order
            ->setOrderReference($this->getProperty($data, 'id'))
            ->setShippingMethod(
                sprintf(
                    '%s, %s',
                    $this->getProperty($data, 'shippingMethod'),
                    $this->getProperty($data, 'shippingMethodType')
                )
            )
            ->setShippingAmountInclTax($shipping['includingTax'])
            ->setShippingAmountExclTax($shipping['excludingTax'])
            ->setDiscountAmount((float)$this->getProperty($data, 'totalDiscountsAmount'))
            ->setGrandTotal((float)$this->getProperty($data, 'totalValue'))
            ->setTotalTax((float)$total['taxAmount'])
            ->setSubtotal($subtotal)
            ->setCurrency($this->getProperty($data, 'currency'))
            ->setCustomer($customer);

        if($billingAddress = $this->getProperty($data, 'billingAddress')) {
            if ($billingAddress) {
                $billingAddress = $this->prepareAddress($billingAddress);
                if ($billingAddress) {
                    $order->setShippingAddress($billingAddress);
                }
            }
        }

        if($shippingAddress = $this->getProperty($data, 'shippingAddress')) {
            if ($shippingAddress) {
                $shippingAddress = $this->prepareAddress($shippingAddress);
                if ($shippingAddress) {
                    $order->setShippingAddress($shippingAddress);
                }
            }
        }

        $this->prepareOrderItems(
            $this->getProperty($data, 'lineItems'),
            $order
        );

        return $order;
    }

    /**
     * @param array $data
     * @return Customer
     */
    private function prepareCustomer(array $data)
    {
        $customer = new Customer();
        $customerUser = $this->getProperty($data, 'customerUser');

        if ($firstName = $this->getProperty($customerUser, 'firstName')) {
            $customer->setFirstName($firstName);
        }
        if ($lastName = $this->getProperty($customerUser, 'lastName')) {
            $customer->setLastName($lastName);
        }
        if ($middleName = $this->getProperty($customerUser, 'middleName')) {
            $customer->setMiddleName($middleName);
        }
        if ($namePrefix = $this->getProperty($customerUser, 'namePrefix')) {
            $customer->setNamePrefix($namePrefix);
        }
        if ($nameSuffix = $this->getProperty($customerUser, 'nameSuffix')) {
            $customer->setNameSuffix($nameSuffix);
        }
        if ($email = $this->getProperty($customerUser, 'email')) {
            $customer->setEmail($email);
        }

        if($shippingAddress = $this->getProperty($data, 'shippingAddress')) {
            if ($shippingAddress) {
                $shippingAddress = $this->prepareAddress($shippingAddress);
                if ($shippingAddress) {
                    $customer->setPrimaryAddress($shippingAddress);
                }
            }
        }


        return $customer;
    }

    /**
     * @param array $lineItems
     * @param Order $order
     */
    private function prepareOrderItems(array $lineItems, Order $order)
    {
        foreach ($lineItems as $lineItem) {
            $product = $this->registry
                ->getManagerForClass(Product::class)
                ->getRepository(Product::class)
                ->findOneBy(['sku' => $this->getProperty($lineItem, 'productSku')]);

            if ($product) {
                $this->prepareOrderItem($lineItem, $product, $order);
            }
        }
    }

    /**
     * @param array $lineItem
     * @param Product $product
     * @param Order $order
     */
    private function prepareOrderItem(array $lineItem, Product $product, Order $order)
    {
        $taxValue = $this->getProperty($lineItem, 'taxvalues');
        $result = $this->getProperty($taxValue, 'result');
        $row = $this->getProperty($result, 'row');
        $quantity = $this->getProperty($lineItem, 'quantity');
        if ($this->configManager->get(Configuration::VAT_SYSTEM_CONFIG_PATH)) {
            $price = (float)$row['includingTax']/(float)$quantity;
        } else {
            $price = (float)$row['excludingTax']/(float)$quantity;
        }

        $item = new OrderItem();
        $item
            ->setPrice((float)$price)
            ->setQuantity((float)$quantity)
            ->setTax((float)$row['taxAmount'])
            ->setRowTotalInclTax((float)$row['includingTax'])
            ->setRowTotalExclTax((float)$row['excludingTax'])
            ->setProduct($product)
            ->setProductName((string)$product->getName());

        $order->addItem($item);
    }

    /**
     * @param array $data
     * @return MarelloAddress
     */
    private function prepareAddress(array $data)
    {
        if (isset($data['type']) && 'orderaddresses' === $data['type']) {
            $countryCode = $this->getProperty($data, 'country')['id'];
            $regionCode = $this->getProperty($data, 'region')['id'];

            $country = $this->registry
                ->getManagerForClass(Country::class)
                ->getRepository(Country::class)
                ->find($countryCode);
            $region = $this->registry
                ->getManagerForClass(Region::class)
                ->getRepository(Region::class)
                ->find($regionCode);
            if ($country && $region) {
                $address = new MarelloAddress();
                $address
                    ->setCountry($country)
                    ->setRegion($region);
                if ($firstName = $this->getProperty($data, 'firstName')) {
                    $address->setFirstName($firstName);
                }
                if ($lastName = $this->getProperty($data, 'lastName')) {
                    $address->setLastName($lastName);
                }
                if ($middleName = $this->getProperty($data, 'middleName')) {
                    $address->setMiddleName($middleName);
                }
                if ($namePrefix = $this->getProperty($data, 'namePrefix')) {
                    $address->setNamePrefix($namePrefix);
                }
                if ($nameSuffix = $this->getProperty($data, 'nameSuffix')) {
                    $address->setNameSuffix($nameSuffix);
                }
                if ($city = $this->getProperty($data, 'city')) {
                    $address->setCity($city);
                }
                if ($postalCode = $this->getProperty($data, 'postalCode')) {
                    $address->setPostalCode($postalCode);
                }
                if ($street = $this->getProperty($data, 'street')) {
                    $address->setStreet($street);
                }
                if ($street2 = $this->getProperty($data, 'street2')) {
                    $address->setStreet2($street2);
                }
                if ($phone = $this->getProperty($data, 'phone')) {
                    $address->setPhone($phone);
                }

                return $address;
            }
        }

        return null;
    }

    /**
     * @param mixed $data
     * @param $property
     * @return mixed|null
     */
    private function getProperty($data, $property)
    {
        if (!is_array($data)) {
            return null;
        }
        if (isset($data[$property])) {
            return $data[$property];
        }
        if (isset($data['attributes'][$property])) {
            return $data['attributes'][$property];
        }
        if (isset($data['relationships'][$property])) {
            return $data['relationships'][$property]['data'];
        }

        return null;
    }
}
