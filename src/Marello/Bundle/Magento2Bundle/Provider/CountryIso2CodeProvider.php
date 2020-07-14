<?php

namespace Marello\Bundle\Magento2Bundle\Provider;

use Oro\Bundle\AddressBundle\Entity\Repository\CountryRepository;

class CountryIso2CodeProvider
{
    /**
     * @var CountryRepository
     */
    private $countryRepository;

    /**
     * @var array
     */
    private $iso2Mapping = [];

    /**
     * @var array
     */
    private $iso3Mapping = [];

    /**
     * @var array
     */
    private $nameMapping = [];

    /**
     * @param CountryRepository $countryRepository
     */
    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
    }

    /**
     * @param string $countryId
     * @return string|null
     */
    public function getIso2CodeByCountryId(string $countryId): ?string
    {
        $this->ensureMappingsLoaded();

        if (isset($this->iso2Mapping[$countryId])) {
            return $this->iso2Mapping[$countryId];
        }

        if (isset($this->iso3Mapping[$countryId])) {
            return $this->iso3Mapping[$countryId];
        }

        if (isset($this->nameMapping[$countryId])) {
            return $this->nameMapping[$countryId];
        }

        return null;
    }

    private function ensureMappingsLoaded(): void
    {
        if ($this->iso2Mapping && $this->iso3Mapping && $this->nameMapping) {
            return;
        }

        foreach ($this->countryRepository->getAllCountryNamesArray() as $country) {
            $this->iso2Mapping[$country['iso2Code']] = $country['iso2Code'];
            $this->iso3Mapping[$country['iso3Code']] = $country['iso2Code'];
            $this->nameMapping[$country['name']] = $country['iso2Code'];
        }
    }
}
