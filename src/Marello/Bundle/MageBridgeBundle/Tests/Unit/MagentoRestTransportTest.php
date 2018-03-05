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
        $url = 'magento_base_url.com';
        $token = uniqid();

        $this->target->setInfosUrl($url);

        $this->target->setClientId($token);
        $this->target->setClientSecret($token);

        $this->target->setToken($token);
        $this->target->setTokenSecret($token);

        $result = $this->target->getSettingsBag();
        $this->assertEquals($result->get('infosUrl'), $url);
        $this->assertEquals($result->get('clientId'), $token);
        $this->assertEquals($result->get('clientSecret'), $token);
        $this->assertEquals($result->get('token'), $token);
        $this->assertEquals($result->get('tokenSecret'), $token);
    }

    /**
     * @return array
     */
    public function settersAndGettersDataProvider()
    {
        return array(
            array('infosUrl', 'magento_base_url.com'),
            array('clientId', uniqid()),
            array('clientSecret', uniqid()),
            array('token', uniqid()),
            array('tokenSecret', uniqid()),
        );
    }
}
