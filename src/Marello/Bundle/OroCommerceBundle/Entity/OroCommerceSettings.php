<?php

namespace Marello\Bundle\OroCommerceBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
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
    const USERNAME_FIELD = 'username';
    const WAREHOUSE_FIELD = 'warehouse';
    const PRODUCTUNIT_FIELD = 'productunit';
    const CUSTOMERTAXCODE_FIELD = 'customertaxcode';
    const PRICELIST_FIELD = 'pricelist';
    const PRODUCTFAMILY_FIELD = 'productfamily';
    const INVENTORYTHRESHOLD_FIELD = 'inventorythreshold';
    const LOWINVENTORYTHRESHOLD_FIELD = 'lowinventorythreshold';
    const BACKORDER_FIELD = 'backorder';

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
     * @var string
     *
     * @ORM\Column(name="orocommerce_inventorythreshold", type="integer", nullable=false)
     */
    private $inventoryThreshold;

    /**
     * @var string
     *
     * @ORM\Column(name="orocommerce_lowinvthreshold", type="integer", nullable=false)
     */
    private $lowInventoryThreshold;

    /**
     * @var string
     *
     * @ORM\Column(name="orocommerce_backorder", type="boolean", nullable=false)
     */
    private $backOrder;

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
     * @var ParameterBag
     */
    private $settings;

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
     * @return int
     */
    public function getInventoryThreshold()
    {
        return $this->inventoryThreshold;
    }

    /**
     * @param int $inventoryThreshold
     * @return $this
     */
    public function setInventoryThreshold($inventoryThreshold)
    {
        $this->inventoryThreshold = $inventoryThreshold;

        return $this;
    }

    /**
     * @return int
     */
    public function getLowInventoryThreshold()
    {
        return $this->lowInventoryThreshold;
    }

    /**
     * @param int $lowInventoryThreshold
     * @return $this
     */
    public function setLowInventoryThreshold($lowInventoryThreshold)
    {
        $this->lowInventoryThreshold = $lowInventoryThreshold;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBackOrder()
    {
        return $this->backOrder;
    }

    /**
     * @param bool $backOrder
     * @return $this
     */
    public function setBackOrder($backOrder)
    {
        $this->backOrder = $backOrder;

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
                    self::PRODUCTUNIT_FIELD => $this->getProductUnit(),
                    self::CUSTOMERTAXCODE_FIELD => $this->getCustomerTaxCode(),
                    self::PRICELIST_FIELD => $this->getPriceList(),
                    self::PRODUCTFAMILY_FIELD => $this->getProductFamily(),
                    self::INVENTORYTHRESHOLD_FIELD => $this->getInventoryThreshold(),
                    self::LOWINVENTORYTHRESHOLD_FIELD => $this->getLowInventoryThreshold(),
                    self::BACKORDER_FIELD => $this->isBackOrder()
                ]
            );
        }

        return $this->settings;
    }
}
