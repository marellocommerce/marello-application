<?php

namespace Marello\Bundle\AddressBundle\Twig;

use Oro\Bundle\LocaleBundle\Formatter\AddressFormatter;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AddressExtension extends AbstractExtension
{
    /**
     * @var AddressFormatter
     */
    private $addressFormatter;

    /**
     * @param AddressFormatter $addressFormatter
     */
    public function __construct(AddressFormatter $addressFormatter)
    {
        $this->addressFormatter = $addressFormatter;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'marello_format_address',
                [$this, 'formatAddress'],
                ['is_safe' => ['html']]
            )
        ];
    }

    /**
     * Formats address according to locale settings.
     *
     * @param AddressInterface $address
     * @param string|null      $country
     * @param string           $newLineSeparator
     *
     * @return string
     */
    public function formatAddress(AddressInterface $address, $country = null, $newLineSeparator = "\n")
    {
        return $this->addressFormatter->format($address, $country, $newLineSeparator);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'marello_address';
    }
}
