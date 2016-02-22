<?php

namespace Marello\Bundle\OrderBundle\Provider;

use Symfony\Bridge\Doctrine\RegistryInterface;

use Oro\Bundle\DashboardBundle\Model\WidgetOptionBag;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatter;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\TranslationBundle\Translation\Translator;

class OrderDashboardStatisticProvider
{
    /** @var RegistryInterface */
    protected $doctrine;

    /** @var NumberFormatter */
    protected $numberFormatter;

    /** @var DateTimeFormatter */
    protected $dateTimeFormatter;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var Translator */
    protected $translator;

    /**
     * @param RegistryInterface $doctrine
     * @param NumberFormatter   $numberFormatter
     * @param DateTimeFormatter $dateTimeFormatter
     * @param AclHelper         $aclHelper
     * @param Translator        $translator
     */
    public function __construct(
        RegistryInterface $doctrine,
        NumberFormatter $numberFormatter,
        DateTimeFormatter $dateTimeFormatter,
        AclHelper $aclHelper,
        Translator $translator
    ) {
        $this->doctrine          = $doctrine;
        $this->numberFormatter   = $numberFormatter;
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->aclHelper         = $aclHelper;
        $this->translator        = $translator;
    }

    /**
     * @param WidgetOptionBag $widgetOptions
     * @param                 $getterName
     * @param                 $dataType
     * @param bool            $lessIsBetter
     * @return array
     */
    public function getValues(WidgetOptionBag $widgetOptions, $getterName, $dataType, $lessIsBetter = false)
    {
        $lessIsBetter     = (bool)$lessIsBetter;
        $result           = [];
        $dateRange        = $widgetOptions->get('dateRange');
        $value            = $this->{$getterName}($dateRange);
        $result['value']  = $this->formatValue($value, $dataType);
        $previousInterval = $widgetOptions->get('usePreviousInterval', []);

        if (count($previousInterval)) {
            $pastResult = $this->{$getterName}($previousInterval);

            $result['deviation'] = $this->translator->trans('marello.order.dashboard.order_statistics.no_changes');

            $deviation = $value - $pastResult;
            if ($pastResult != 0 && $dataType !== 'percent') {
                if ($deviation != 0) {
                    $deviationPercent    = $deviation / $pastResult;
                    $result['deviation'] = sprintf(
                        '%s (%s)',
                        $this->formatValue($deviation, $dataType, true),
                        $this->formatValue($deviationPercent, 'percent', true)
                    );
                    if (!$lessIsBetter) {
                        $result['isPositive'] = $deviation > 0;
                    } else {
                        $result['isPositive'] = !($deviation > 0);
                    }
                }
            } else {
                if (round(($deviation) * 100, 0) != 0) {
                    $result['deviation'] = $this->formatValue($deviation, $dataType, true);
                    if (!$lessIsBetter) {
                        $result['isPositive'] = $deviation > 0;
                    } else {
                        $result['isPositive'] = !($deviation > 0);
                    }
                }
            }

            $result['previousRange'] = sprintf(
                '%s - %s',
                $this->dateTimeFormatter->formatDate($previousInterval['start']),
                $this->dateTimeFormatter->formatDate($previousInterval['end'])
            );
        }

        return $result;
    }

    /**
     * @param array $dateRange
     * @return int
     */
    protected function getTotalRevenueValues($dateRange)
    {
        list($start, $end) = $this->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');

        return $this->doctrine
            ->getRepository('MarelloOrderBundle:Order')
            ->getTotalRevenueValue($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    protected function getTotalOrdersNumberValues($dateRange)
    {
        list($start, $end) = $this->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');

        return $this->doctrine
            ->getRepository('MarelloOrderBundle:Order')
            ->getTotalOrdersNumberValue($start, $end, $this->aclHelper);
    }

    /**
     * @param array $dateRange
     * @return int
     */
    protected function getAverageOrderValues($dateRange)
    {
        list($start, $end) = $this->getPeriod($dateRange, 'MarelloOrderBundle:Order', 'createdAt');

        return $this->doctrine
            ->getRepository('MarelloOrderBundle:Order')
            ->getAverageOrderValue($start, $end, $this->aclHelper);
    }

    /**
     * @param array  $dateRange
     * @param string $entity
     * @param string $field
     * @return array
     */
    protected function getPeriod($dateRange, $entity, $field)
    {
        $start = $dateRange['start'];
        $end   = $dateRange['end'];

        if ($dateRange['type'] === AbstractDateFilterType::TYPE_LESS_THAN) {
            $qb    = $this->doctrine
                ->getRepository($entity)
                ->createQueryBuilder('e')
                ->select(sprintf('MIN(e.%s) as val', $field));
            $start = $this->aclHelper->apply($qb)->getSingleScalarResult();
            $start = new \DateTime($start, new \DateTimeZone('UTC'));
        }

        return [$start, $end];
    }

    /**
     * @param mixed  $value
     * @param string $type
     * @param bool   $isDeviant
     *
     * @return string
     */
    protected function formatValue($value, $type = '', $isDeviant = false)
    {
        $sign = null;

        if ($isDeviant && $value !== 0) {
            $sign  = $value > 0 ? '+' : '&minus;';
            $value = abs($value);
        }
        switch ($type) {
            case 'currency':
                $value = $this->numberFormatter->formatCurrency($value);
                break;
            case 'percent':
                if ($isDeviant) {
                    $value = round(($value) * 100, 0) / 100;
                } else {
                    $value = round(($value) * 100, 2) / 100;
                }

                $value = $this->numberFormatter->formatPercent($value);
                break;
            default:
                $value = $this->numberFormatter->formatDecimal($value);
        }

        return $isDeviant && !is_null($sign) ? sprintf('%s%s', $sign, $value) : $value;
    }
}
