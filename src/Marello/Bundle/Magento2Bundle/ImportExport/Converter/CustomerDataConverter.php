<?php

namespace Marello\Bundle\Magento2Bundle\ImportExport\Converter;

use Marello\Bundle\Magento2Bundle\DTO\CustomerIdentityDataDTO;
use Marello\Bundle\Magento2Bundle\Exception\RuntimeException;
use Marello\Bundle\Magento2Bundle\Generator\CustomerHashIdGeneratorInterface;
use Oro\Bundle\IntegrationBundle\ImportExport\DataConverter\IntegrationAwareDataConverter;

class CustomerDataConverter extends IntegrationAwareDataConverter
{
    public const ORIGIN_ID = 'originId';
    public const EMAIL = 'email';
    public const FIRST_NAME = 'firstName';
    public const LAST_NAME = 'lastName';

    /** @var CustomerHashIdGeneratorInterface */
    protected $customerHashIdGenerator;

    /**
     * @param CustomerHashIdGeneratorInterface $customerHashIdGenerator
     */
    public function __construct(CustomerHashIdGeneratorInterface $customerHashIdGenerator)
    {
        $this->customerHashIdGenerator = $customerHashIdGenerator;
    }

    /**
     * {@inheritDoc}
     */
    protected function getHeaderConversionRules()
    {
        return [
            'originId' => self::ORIGIN_ID
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function convertToImportFormat(array $importedRecord, $skipNullValues = true)
    {
        if (null === $importedRecord[self::ORIGIN_ID] &&
            $importedRecord[self::EMAIL] && $importedRecord[self::FIRST_NAME] && $importedRecord[self::LAST_NAME]) {
            $customerIdentityDTO = new CustomerIdentityDataDTO(
                $importedRecord[self::EMAIL],
                $importedRecord[self::FIRST_NAME],
                $importedRecord[self::LAST_NAME]
            );

            $importedRecord['hashId'] = $this->customerHashIdGenerator->generateHashId($customerIdentityDTO);
        }

        $importedRecord = parent::convertToImportFormat($importedRecord, $skipNullValues);

        $importedRecord['innerCustomer'] = [
            'email' => $importedRecord[self::EMAIL],
            'firstName' => $importedRecord[self::FIRST_NAME],
            'lastName' => $importedRecord[self::LAST_NAME],
        ];

        if ($this->context && $this->context->hasOption('salesChannel')) {
            $importedRecord['innerCustomer'] = [
                'salesChannel' => [
                    'id' => $this->context->getOption('salesChannel')
                ]
            ];
        }

        return $importedRecord;
    }

    /**
     * @return array|void
     */
    protected function getBackendHeader()
    {
        throw new RuntimeException('Normalization is not implemented!');
    }
}
