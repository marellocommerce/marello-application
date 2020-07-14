<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\Magento2Bundle\Provider\CountryIso2CodeProvider;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class MarelloOrderAddressDataConverter extends IntegrationAwareDataConverter
{
    /** @var CountryIso2CodeProvider */
    protected $iso2CodeProvider;

    /**
     * @param CountryIso2CodeProvider $iso2CodeProvider
     */
    public function __construct(CountryIso2CodeProvider $iso2CodeProvider)
    {
        $this->iso2CodeProvider = $iso2CodeProvider;
    }

    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'firstName' => 'firstname',
            'lastName' => 'lastname',
            'street' => 'street:0',
            'street2' => 'street:1',
            'phone' => 'telephone',
            'postalCode' => 'postcode',
            'city' => 'city'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if (!empty($importedRecord['country_id'])) {
            $importedRecord['country:iso2Code'] = $this->iso2CodeProvider->getIso2CodeByCountryId(
                $importedRecord['country_id']
            );

            if (!empty($importedRecord['region_code']) && null !== $importedRecord['country:iso2Code']) {
                $importedRecord['region:combinedCode'] = $importedRecord['country:iso2Code'] . Region::SEPARATOR .
                    $importedRecord['region_code'];
            }
        }

        if (empty($importedRecord['region:combinedCode'])) {
            $importedRecord['regionText'] = 'region';
        }

        return parent::convertToImportFormat($importedRecord, $skipNullValues);
    }

    /**
     * @return array|void
     */
    protected function getBackendHeader()
    {
        throw new RuntimeException('Normalization is not implemented!');
    }
}
