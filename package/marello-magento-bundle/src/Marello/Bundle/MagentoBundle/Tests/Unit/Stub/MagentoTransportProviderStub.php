<?php

namespace Marello\Bundle\MagentoBundle\Tests\Unit\Stub;

use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Marello\Bundle\MagentoBundle\Entity\Customer;
use Marello\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;

class MagentoTransportProviderStub implements MagentoTransportInterface
{
    /**
     * @var string
     */
    private $extensionVersion;

    /**
     * @var string
     */
    private $magentoVersion;

    /**
     * @var string
     */
    private $requiredExtensionVersion;

    /**
     * @var boolean
     */
    private $isSupportedExtensionVersion;

    /**
     * @var boolean
     */
    private $isExtensionInstalled;

    /**
     * @var string
     */
    private $adminUrl;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->setData($data);
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        foreach ($data as $property => $value) {
            $this->{$property} = $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initWithExtraOptions(Transport $transportEntity, array $clientExtraOptions)
    {
        // @todo: Implement initWithExtraOptions() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isExtensionInstalled()
    {
        return $this->isExtensionInstalled;
    }

    /**
     * {@inheritdoc}
     */
    public function getAdminUrl()
    {
        return $this->adminUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrders()
    {
        // @todo: Implement getOrders() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCarts()
    {
        // @todo: Implement getCarts() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomers()
    {
        // @todo: Implement getCustomers() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isCustomerHasUniqueEmail(Customer $customer)
    {
        // @todo: Implement isCustomerHasUniqueEmail() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerGroups()
    {
        // @todo: Implement getCustomerGroups() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getStores()
    {
        // @todo: Implement getStores() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getWebsites()
    {
        // @todo: Implement getWebsites() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getRegions()
    {
        // @todo: Implement getRegions() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerInfo($originId)
    {
        // @todo: Implement getCustomerInfo() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddresses($originId)
    {
        // @todo: Implement getCustomerAddresses() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorCode(\Exception $e)
    {
        // @todo: Implement getErrorCode() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderInfo($incrementId)
    {
        // @todo: Implement getOrderInfo() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditMemos()
    {
        // @todo: Implement getCreditMemos() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCreditMemoInfo($incrementId)
    {
        // @todo: Implement getCreditMemoInfo() method.
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomer(array $customerData)
    {
        // @todo: Implement createCustomer() method.
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomer($customerId, array $customerData)
    {
        // @todo: Implement updateCustomer() method.
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomerAddress($customerId, array $item)
    {
        // @todo: Implement createCustomerAddress() method.
    }

    /**
     * {@inheritdoc}
     */
    public function updateCustomerAddress($customerAddressId, array $item)
    {
        // @todo: Implement updateCustomerAddress() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomerAddressInfo($customerAddressId)
    {
        // @todo: Implement getCustomerAddressInfo() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getNewsletterSubscribers()
    {
        // @todo: Implement getNewsletterSubscribers() method.
    }

    /**
     * {@inheritdoc}
     */
    public function createNewsletterSubscriber(array $subscriberData)
    {
        // @todo: Implement createNewsletterSubscriber() method.
    }

    /**
     * {@inheritdoc}
     */
    public function updateNewsletterSubscriber($subscriberId, array $subscriberData)
    {
        // @todo: Implement updateNewsletterSubscriber() method.
    }

    /**
     * {@inheritdoc}
     */
    public function isSupportedExtensionVersion()
    {
        return $this->isSupportedExtensionVersion;
    }

    /**
     * @return boolean
     */
    public function isSupportedOrderNoteExtensionVersion()
    {
        return $this->isSupportedExtensionVersion()
            && version_compare($this->getExtensionVersion(), '1.2.19', 'ge');
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensionVersion()
    {
        return $this->extensionVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getMagentoVersion()
    {
        return $this->magentoVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredExtensionVersion()
    {
        return $this->requiredExtensionVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderNoteRequiredExtensionVersion()
    {
        // @todo: Implement getOrderNoteRequiredExtensionVersion() method.
    }

    /**
     * {@inheritdoc}
     */
    public function resetInitialState()
    {
        // @todo: Implement resetInitialState() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getServerTime()
    {
        // @todo: Implement getServerTime() method.
    }

    /**
     * {@inheritdoc}
     */
    public function init(Transport $transportEntity)
    {
        // @todo: Implement init() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        // @todo: Implement getLabel() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsFormType()
    {
        // @todo: Implement getSettingsFormType() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsEntityFQCN()
    {
        // @todo: Implement getSettingsEntityFQCN() method.
    }

    /**
     * @inheritdoc
     */
    public function createProduct(array $productData)
    {
        // TODO: Implement createProduct() method.
    }

    /**
     * @inheritdoc
     */
    public function updateProduct(array $item)
    {
        // TODO: Implement updateProduct() method.
    }

    /**
     * @inheritdoc
     */
    public function getProducts()
    {
        // TODO: Implement getProducts() method.
    }

    /**
     * @inheritdoc
     */
    public function updateStock(array $stockData)
    {
        // TODO: Implement updateStock() method.
    }

    /**
     * @inheritdoc
     */
    public function getCategoryList()
    {
        // TODO: Implement getCategoryList() method.
    }

    /**
     * @inheritdoc
     */
    public function catalogCategoryUpdateProduct(array $categoryLinkData)
    {
        // TODO: Implement catalogCategoryUpdateProduct() method.
    }

    /**
     * @inheritdoc
     */
    public function catalogCategoryAssignProduct(array $categoryLinkData)
    {
        // TODO: Implement catalogCategoryAssignProduct() method.
    }

    /**
     * @inheritdoc
     */
    public function deleteProduct(array $item)
    {
        // TODO: Implement deleteProduct() method.
    }

    /**
     * @inheritdoc
     */
    public function catalogCategoryRemoveProduct($categoryId, $productId, $identifierType = null)
    {
        // TODO: Implement catalogCategoryRemoveProduct() method.
    }

    /**
     * @inheritdoc
     */
    public function catalogCategoryAssignedProducts($categoryId, $store = null)
    {
        // TODO: Implement catalogCategoryAssignedProducts() method.
    }
}
