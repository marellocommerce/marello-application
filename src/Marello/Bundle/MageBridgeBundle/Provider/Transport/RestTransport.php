<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 1-3-18
 * Time: 11:14
 */

namespace Marello\Bundle\MageBridgeBundle\Provider\Transport;

use Symfony\Component\HttpFoundation\ParameterBag;

use Oro\Bundle\IntegrationBundle\Provider\Rest\Transport\AbstractRestTransport;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\IntegrationBundle\Entity\Transport;

class RestTransport extends AbstractRestTransport
{
    const API_URL_PREFIX = 'api/rest';

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'marello.magento.channel_type.magento.label';
    }

    /**
     * Returns form type name needed to setup transport
     *
     * @return string
     */
    public function getSettingsFormType()
    {
        return 'marello_magebridge_rest_transport_setting_form_type';
    }

    /**
     * Returns entity name needed to store transport settings
     *
     * @return string
     */
    public function getSettingsEntityFQCN()
    {
        return 'Marello\\Bundle\\MageBridgeBundle\\Entity\\MagentoRestTransport';
    }

    /**
     * Get REST client base url
     *
     * @param ParameterBag $parameterBag
     * @return string
     * @throws InvalidConfigurationException
     */
    protected function getClientBaseUrl(ParameterBag $parameterBag)
    {
        return rtrim($parameterBag->get('infosUrl'), '/') . '/' . ltrim(static::API_URL_PREFIX, '/');
    }

    /**
     * Get REST client options
     *
     * @param ParameterBag $parameterBag
     * @return array
     * @throws InvalidConfigurationException
     */
    protected function getClientOptions(ParameterBag $parameterBag)
    {
        //TODO: extract client options here
        return [];
    }
}
