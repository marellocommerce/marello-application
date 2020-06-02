<?php

namespace Marello\Bundle\OroCommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Marello\Bundle\SalesBundle\Entity\SalesChannelGroup;
use Oro\Bundle\IntegrationBundle\Entity\Transport;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @ORM\Entity
 */
class OroCommerceSettings extends Transport
{
    const URL_FIELD = 'url';
    const ENTERPRISE_FIELD = 'enterprise';
    const CURRENCY_FIELD = 'currency';
    const KEY_FIELD = 'key';
    const USERNAME_FIELD = 'userName';
    const WAREHOUSE_FIELD = 'warehouse';
    const BUSINESSUNIT_FIELD = 'businessunit';
    const PRODUCTUNIT_FIELD = 'productunit';
    const CUSTOMERTAXCODE_FIELD = 'customertaxcode';
    const PRICELIST_FIELD = 'pricelist';
    const PRODUCTFAMILY_FIELD = 'productfamily';
    const DELETE_REMOTE_DATA_ON_DEACTIVATION = 'deleteRemoteDataOnDeactivation';
    const DELETE_REMOTE_DATA_ON_DELETION = 'deleteRemoteDataOnDeletion';
    const DATA = 'data';

    /**
     * @var string
     *
     * @ORM\Column(name="orocommerce_url", type="string", length=1024, nullable=false)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="orocommerce_currency", type="string", length=3, nullable=false)
     */
    private $currency;

    /**
     * @var string
     *
     * @ORM\Column(name="orocommerce_key", type="string", length=1024, nullable=false)
     */
    private $key;

    /**
     * @var string
     *
     * @ORM\Column(name="orocommerce_username", type="string", length=1024, nullable=false)
     */
    private $userName;

    /**
     * @var int
     *
     * @ORM\Column(name="orocommerce_businessunit", type="integer", nullable=false)
     */
    private $businessUnit;
    
    /**
     * @var string
     *
     * @ORM\Column(name="orocommerce_productunit", type="string", length=20, nullable=false)
     */
    private $productUnit;

    /**
     * @var string
     *
     * @ORM\Column(name="orocommerce_customertaxcode", type="integer", nullable=false)
     */
    private $customerTaxCode;

    /**
     * @var string
     *
     * @ORM\Column(name="orocommerce_pricelist", type="integer", nullable=false)
     */
    private $priceList;

    /**
     * @var string
     *
     * @ORM\Column(name="orocommerce_productfamily", type="integer", nullable=false)
     */
    private $productFamily;

    /**
     * @var bool
     *
     * @ORM\Column(name="orocommerce_deldataondeactiv", type="boolean", nullable=true)
     */
    private $deleteRemoteDataOnDeactivation;

    /**
     * @var bool
     *
     * @ORM\Column(name="orocommerce_deldataondel", type="boolean", nullable=true)
     */
    private $deleteRemoteDataOnDeletion;

    /**
     * @var bool
     *
     * @ORM\Column(name="orocommerce_enterprise", type="boolean", nullable=true)
     */
    private $enterprise;

    /**
     * @var string
     *
     * @ORM\Column(name="orocommerce_warehouse", type="integer", nullable=true)
     */
    private $warehouse;

    /**
     * @var array $data
     *
     * @ORM\Column(name="orocommerce_data", type="json_array", nullable=true)
     */
    protected $data;
    
    /**
     * @var ParameterBag
     */
    private $settings;

    /**
     * @var SalesChannelGroup
     *
     * @ORM\ManyToOne(targetEntity="Marello\Bundle\SalesBundle\Entity\SalesChannelGroup")
     * @ORM\JoinColumn(name="orocommerce_scg_id", referencedColumnName="id", nullable=true)
     */
    private $salesChannelGroup;

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return $this
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }
    
    /**
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     * @return $this
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * @return int
     */
    public function getBusinessUnit()
    {
        return $this->businessUnit;
    }

    /**
     * @param int $businessUnit
     * @return $this
     */
    public function setBusinessUnit($businessUnit)
    {
        $this->businessUnit = $businessUnit;
        
        return $this;
    }

    /**
     * @return string
     */
    public function getProductUnit()
    {
        return $this->productUnit;
    }

    /**
     * @param string $productUnit
     * @return $this
     */
    public function setProductUnit($productUnit)
    {
        $this->productUnit = $productUnit;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerTaxCode()
    {
        return $this->customerTaxCode;
    }

    /**
     * @param int $customerTaxCode
     * @return $this
     */
    public function setCustomerTaxCode($customerTaxCode)
    {
        $this->customerTaxCode = $customerTaxCode;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriceList()
    {
        return $this->priceList;
    }

    /**
     * @param int $priceList
     * @return $this
     */
    public function setPriceList($priceList)
    {
        $this->priceList = $priceList;

        return $this;
    }

    /**
     * @return int
     */
    public function getProductFamily()
    {
        return $this->productFamily;
    }

    /**
     * @param int $productFamily
     * @return $this
     */
    public function setProductFamily($productFamily)
    {
        $this->productFamily = $productFamily;

        return $this;
    }

    /**
     * @deprecated inventoryThreshold is now using the category default in OroCommerce
     * @return int
     */
    public function getInventoryThreshold()
    {
        return 0;
    }

    /**
     * @deprecated inventoryThreshold is now using the category default in OroCommerce
     * @param int $inventoryThreshold
     * @return $this
     */
    public function setInventoryThreshold($inventoryThreshold)
    {
        return $this;
    }

    /**
     * @deprecated lowInventoryThreshold is now using the category default in OroCommerce
     * @return int
     */
    public function getLowInventoryThreshold()
    {
        return 0;
    }

    /**
     * @deprecated lowInventoryThreshold is now using the category default in OroCommerce
     * @param int $lowInventoryThreshold
     * @return $this
     */
    public function setLowInventoryThreshold($lowInventoryThreshold)
    {
        return $this;
    }

    /**
     * @deprecated backOrder from InventoryItem is used in synchronisation
     * @return bool
     */
    public function isBackOrder()
    {
        return false;
    }

    /**
     * @deprecated backOrder from InventoryItem is used in synchronisation
     * @param bool $backOrder
     * @return $this
     */
    public function setBackOrder($backOrder)
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function isDeleteRemoteDataOnDeactivation()
    {
        return $this->deleteRemoteDataOnDeactivation;
    }

    /**
     * @param bool $deleteRemoteDataOnDeactivation
     * @return $this
     */
    public function setDeleteRemoteDataOnDeactivation($deleteRemoteDataOnDeactivation)
    {
        $this->deleteRemoteDataOnDeactivation = $deleteRemoteDataOnDeactivation;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeleteRemoteDataOnDeletion()
    {
        return $this->deleteRemoteDataOnDeletion;
    }

    /**
     * @param boolean $deleteRemoteDataOnDeletion
     * @return OroCommerceSettings
     */
    public function setDeleteRemoteDataOnDeletion($deleteRemoteDataOnDeletion)
    {
        $this->deleteRemoteDataOnDeletion = $deleteRemoteDataOnDeletion;
        
        return $this;
    }

    /**
     * @return boolean
     */
    public function isEnterprise()
    {
        return $this->enterprise;
    }

    /**
     * @param boolean $enterprise
     * @return $this
     */
    public function setEnterprise($enterprise = false)
    {
        $this->enterprise = $enterprise;

        return $this;
    }

    /**
     * @return int
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param int $warehouse
     * @return $this
     */
    public function setWarehouse($warehouse = null)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return SalesChannelGroup|null
     */
    public function getSalesChannelGroup()
    {
        return $this->salesChannelGroup;
    }

    /**
     * @param SalesChannelGroup $salesChannelGroup
     * @return $this
     */
    public function setSalesChannelGroup(SalesChannelGroup $salesChannelGroup)
    {
        $this->salesChannelGroup = $salesChannelGroup;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSettingsBag()
    {
        if (null === $this->settings) {
            $this->settings = new ParameterBag(
                [
                    self::URL_FIELD => $this->getUrl(),
                    self::CURRENCY_FIELD => $this->getCurrency(),
                    self::KEY_FIELD => $this->getKey(),
                    self::ENTERPRISE_FIELD => $this->isEnterprise(),
                    self::USERNAME_FIELD => $this->getUserName(),
                    self::WAREHOUSE_FIELD => $this->getWarehouse(),
                    self::BUSINESSUNIT_FIELD => $this->getBusinessUnit(),
                    self::PRODUCTUNIT_FIELD => $this->getProductUnit(),
                    self::CUSTOMERTAXCODE_FIELD => $this->getCustomerTaxCode(),
                    self::PRICELIST_FIELD => $this->getPriceList(),
                    self::PRODUCTFAMILY_FIELD => $this->getProductFamily(),
                    self::DELETE_REMOTE_DATA_ON_DEACTIVATION => $this->isDeleteRemoteDataOnDeactivation(),
                    self::DELETE_REMOTE_DATA_ON_DELETION => $this->isDeleteRemoteDataOnDeletion(),
                    self::DATA => $this->getData()
                ]
            );
        }

        return $this->settings;
    }
}
