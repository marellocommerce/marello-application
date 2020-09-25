<?php

namespace Marello\Bundle\OroCommerceBundle\ImportExport\Serializer;

use Marello\Bundle\AddressBundle\Entity\MarelloAddress;
use Marello\Bundle\CustomerBundle\Entity\Company;
use Marello\Bundle\CustomerBundle\Entity\Customer;
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
use Oro\Bundle\ImportExportBundle\Serializer\Normalizer\DateTimeNormalizer;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\EntityExtendBundle\Entity\Repository\EnumValueRepository;

class OrderNormalizer extends AbstractNormalizer implements DenormalizerInterface
{
    const PAYMENT_STATUS = 'orocommerce_payment_status';
    const PAID_FULLY_STATUS = 'full';

    /**
     * @var ConfigManager
     */
    protected $configManager;

    /** @var DateTimeNormalizer $isoNormalizer */
    protected $isoNormalizer;

    /**
     * @param Registry $registry
     * @param ConfigManager $configManager
     */
    public function __construct(Registry $registry, ConfigManager $configManager)
    {
        parent::__construct($registry);
        
        $this->configManager = $configManager;
        $this->isoNormalizer = new DateTimeNormalizer(\DateTime::ISO8601, 'Y-m-d', 'H:i:s', 'UTC');
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
        $order = $this->createOrder($data, $context);

        return $order->getItems()->count() > 0 ? $order : null;
    }

    /**
     * @param array $data
     * @param array $context
     * @return Order
     */
    public function createOrder(array $data, array $context = array())
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
        $customer = null;
        if ($this->getProperty($data, 'customerUser')) {
            $customer = $this->prepareCustomer($data);
        }

        if ($paymentStatus) {
            $order->setData([
                self::PAYMENT_STATUS => $this->getProperty($paymentStatus, 'paymentStatus')
            ]);
        }
        $integrationChannel = $this->getIntegrationChannel($context['channel']);
        $order
            ->setOrganization($integrationChannel->getOrganization())
            ->setOrderReference($this->getProperty($data, 'id'))
            ->setPaymentMethod($this->getProperty($data, 'paymentMethod') ? : 'no payment method')
            ->setShippingMethod($this->getProperty($data, 'shippingMethod'))
            ->setShippingMethodType($this->getProperty($data, 'shippingMethodType'))
            ->setPurchaseDate($this->prepareDateTime($this->getProperty($data, 'createdAt'), Order::class))
            ->setShippingAmountInclTax($shipping['includingTax'])
            ->setShippingAmountExclTax($shipping['excludingTax'])
            ->setDiscountAmount((float)$this->getProperty($data, 'totalDiscountsAmount'))
            ->setGrandTotal((float)$this->getProperty($data, 'totalValue'))
            ->setTotalTax((float)$total['taxAmount'])
            ->setSubtotal($subtotal)
            ->setCurrency($this->getProperty($data, 'currency'))
            ->setCustomer($customer)
            ->setBillingAddress($this->prepareAddress($this->getProperty($data, 'billingAddress')))
            ->setShippingAddress($this->prepareAddress($this->getProperty($data, 'shippingAddress')));

        // keep bc for a few methods just to be sure...
        if ($this->getProperty($data, 'poNumber') && method_exists($order, 'setPoNumber')) {
            $order->setPoNumber($this->getProperty($data, 'poNumber'));
        }

        if ($this->getProperty($data, 'customerNotes') && method_exists($order, 'setOrderNote')) {
            $order->setOrderNote($this->getProperty($data, 'customerNotes'));
        }

        if (method_exists($order, 'setShippingMethodReference')) {
            $order->setShippingMethodReference($this->getProperty($data, 'shippingMethod'));
        }

        if (method_exists($order, 'setDeliveryDate') && $this->getProperty($data, 'shipUntil')) {
            $shipUntil = \DateTime::createFromFormat('Y-m-d', $this->getProperty($data, 'shipUntil'));
            if ($shipUntil) {
                $order->setDeliveryDate($shipUntil);
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
        $customer->setCompany($this->prepareCompany($data));
        $customer->setOrocommerceOriginId($this->getProperty($customerUser, 'id'));
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

        if ($primaryAddress = $this->prepareAddress($this->getProperty($data, 'shippingAddress'))) {
            $customer->setPrimaryAddress($primaryAddress);
        }

        return $customer;
    }

    /**
     * @param array $data
     * @return \Extend\Entity\EX_MarelloCustomerBundle_Company|Company|null
     */
    protected function prepareCompany(array $data)
    {
        $companyData = $this->getProperty($data, 'customer');
        if ($companyData) {
            $companyName = $this->getProperty($companyData, 'name');
            $originId = $this->getProperty($companyData, 'id');
            /** @var Company $company */
            $company = $this->registry
                ->getManagerForClass(Company::class)
                ->getRepository(Company::class)
                ->findOneBy(
                    [
                        'name' => $companyName,
                        'orocommerce_origin_id' => $originId
                    ]
                );
            if ($company) {
                return $company;
            }
        }

        return null;
    }

    /**
     * @param array $lineItems
     * @param Order $order
     */
    private function prepareOrderItems(array $lineItems, Order $order)
    {
        foreach ($lineItems as $lineItem) {
            /** @var Product $product */
            $product = $this->registry
                ->getManagerForClass(Product::class)
                ->getRepository(Product::class)
                ->findOneBy([
                    'sku' => $this->getProperty($lineItem, 'productSku'),
                    'organization' => $order->getOrganization()
                ]);

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

        $originId = $this->getProperty($lineItem, 'id');
        // this is not something we're proud of...
        $item = $this->findExistingOrderItem($originId);
        $itemStatus = $this->findExistingOrderItemStatus($order, $lineItem, $item);

        if (!$item) {
            $item = new OrderItem();
        }
        $item
            ->setPrice((float)$price)
            ->setQuantity((float)$quantity)
            ->setTax((float)$row['taxAmount'])
            ->setRowTotalInclTax((float)$row['includingTax'])
            ->setRowTotalExclTax((float)$row['excludingTax'])
            ->setProduct($product)
            ->setProductName((string)$product->getName())
            ->setOrganization($order->getOrganization())
            ->setOrocommerceOriginId($originId);

        if ($itemStatus) {
            $item->setStatus($itemStatus);
        }

        $productUnitCode = $this->getProperty($lineItem, 'productUnitCode');
        if ($productUnitCode) {
            $productUnitClass = ExtendHelper::buildEnumValueClassName('marello_product_unit');
            if (class_exists($productUnitClass)) {
                /** @var EnumValueRepository $repo */
                $repo = $this->registry
                    ->getManagerForClass($productUnitClass)
                    ->getRepository($productUnitClass);
                if ($productUnit = $repo->find($productUnitCode)) {
                    $item->setProductUnit($productUnit);
                } else {
                    $item->setProductUnit($product->getInventoryItems()->first()->getProductUnit());
                }
            }
        }

        $order->addItem($item);
    }

    /**
     * Not the way it should be implemented, but we need to check if the OrderItem
     * exists either based on the newly introduced orocommerce_origin_id or from the the possible
     * existing items based on the Order Reference
     * @param $originId
     * @return \Extend\Entity\EX_MarelloOrderBundle_OrderItem|OrderItem|null
     */
    private function findExistingOrderItem($originId)
    {
        /** @var OrderItem $item */
        $item = $this->registry
            ->getManagerForClass(OrderItem::class)
            ->getRepository(OrderItem::class)
            ->findOneBy(
                [
                    'orocommerce_origin_id' => $originId
                ]
            );
        if ($item) {
            return $item;
        }

        return null;
    }

    /**
     * @param Order $order
     * @param $lineItem
     * @param OrderItem|null $item
     * @return \Extend\Entity\EV_Marello_Item_Status|null
     */
    private function findExistingOrderItemStatus(Order $order, $lineItem, OrderItem $item = null)
    {
        if ($item) {
            return $item->getStatus();
        }

        $existingOrder = $this->registry
            ->getManagerForClass(Order::class)
            ->getRepository(Order::class)
            ->findOneBy(
                [
                    'orderReference' => $order->getOrderReference()
                ]
            );
        if ($existingOrder) {
            // not the ideal way to find an item as there is a possibility that an order can have two items with
            // the exact same SKU and quantity...it's a very small risk we unfortunately need to take in order to keep
            // existing order items correct with ProductUnit and Status...
            foreach ($existingOrder->getItems() as $orderItem) {
                if ($orderItem->getProductSku() === $this->getProperty($lineItem, 'productSku') &&
                    $orderItem->getQuantity() === $this->getProperty($lineItem, 'quantity')
                ) {
                    return $orderItem->getStatus();
                }
            }
        }

        return null;
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
            if (!$countryCode && !$regionCode) {
                return null;
            }
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
                if ($company = $this->getProperty($data, 'organization')) {
                    $address->setCompany($company);
                }

                return $address;
            }
        }

        return null;
    }

    /**
     * Run a string date through the isoNormalizer in order to get the DateTime
     * @param string $date
     * @param string $entityClass
     * @return \DateTime|null
     */
    private function prepareDateTime(string $date, string $entityClass)
    {
        return $this->isoNormalizer->denormalize($date, $entityClass);
    }
}
