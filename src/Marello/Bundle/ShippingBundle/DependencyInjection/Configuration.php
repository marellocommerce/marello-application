<?php

namespace Marello\Bundle\ShippingBundle\DependencyInjection;

use Oro\Bundle\ConfigBundle\DependencyInjection\SettingsBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('marello_shipping');

        SettingsBuilder::append(
            $rootNode,
            [
                'shipper_name'                 => ['value' => null],
                'shipper_attention_name'       => ['value' => null],
                'shipper_tax_id'               => ['value' => null],
                'shipper_phone'                => ['value' => null],
                'shipper_email'                => ['value' => null],
                'shipper_address_line_1'       => ['value' => null],
                'shipper_address_line_2'       => ['value' => null],
                'shipper_address_line_3'       => ['value' => null],
                'shipper_address_city'         => ['value' => null],
                'shipper_address_state'        => ['value' => null],
                'shipper_address_postal_code'  => ['value' => null],
                'shipper_address_country_code' => ['value' => null],
                'ups_username'                 => ['value' => null],
                'ups_password'                 => ['value' => null],
                'ups_access_license_key'       => ['value' => null],
                'ups_account_number'           => ['value' => null],
                'ups_api_base_url'             => ['value' => 'https://wwwcie.ups.com/ups.app/xml'],
            ]
        );

        return $treeBuilder;
    }
}
