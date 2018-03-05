<?php

namespace Marello\Bundle\MageBridgeBundle\Test\Unit;

use Marello\Bundle\MageBridgeBundle\Entity\MagentoRestTransport;

class MagentoRestTransportTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MagentoRestTransport
     */
    protected $target;

    protected function setUp()
    {
        $this->target = new MagentoRestTransport();
    }

    /**
     * @dataProvider settersAndGettersDataProvider
     */
    public function testSettersAndGetters($property, $value)
    {
        $method = 'set' . ucfirst($property);
        $result = $this->target->$method($value);

        $this->assertInstanceOf(get_class($this->target), $result);
        $this->assertEquals($value, $this->target->{'get' . $property}());
    }

    public function testGetSettingsBag()
    {
        $url = 'http://magento_base_url.com';
        $adminUrl = 'http://magento_base_url.com/admin';
        $token = uniqid();

        $this->target->setApiUrl($url);
        $this->target->setAdminUrl($adminUrl);

        $this->target->setClientId($token);
        $this->target->setClientSecret($token);

        $this->target->setTokenKey($token);
        $this->target->setTokenSecret($token);

        $result = $this->target->getSettingsBag();
        $this->assertEquals($result->get('apiUrl'), $url);
        $this->assertEquals($result->get('adminUrl'), $adminUrl);
        $this->assertEquals($result->get('clientId'), $token);
        $this->assertEquals($result->get('clientSecret'), $token);
        $this->assertEquals($result->get('tokenKey'), $token);
        $this->assertEquals($result->get('tokenSecret'), $token);
    }

    /**
     * @return array
     */
    public function settersAndGettersDataProvider()
    {
        return array(
            array('apiUrl', 'magento_base_url.com'),
            array('adminUrl', 'magento_base_url.com/admin'),
            array('clientId', uniqid()),
            array('clientSecret', uniqid()),
            array('tokenKey', uniqid()),
            array('tokenSecret', uniqid()),
            array('salesChannels', []),
        );
    }

}
