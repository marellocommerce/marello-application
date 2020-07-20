<?php

namespace Marello\Bundle\Magento2Bundle\Integration\Provider;

class LastSyncProvider implements LastSyncProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getLastSyncItemDateTime(
        \DateTime $currentLastSyncItemDateTime,
        string $updatedAtColumnName,
        string $updatedAtColumnFormat,
        array $item,
        \DateTime $remoteServerDateTime = null
    ):?\DateTime {
        $updatedAtDateTime = $this->getUpdatedDateTime($item, $updatedAtColumnName, $updatedAtColumnFormat);

        $maxUpdatedDate = $this->getMaxUpdatedDateOrNull(
            $updatedAtDateTime,
            $currentLastSyncItemDateTime
        );

        if (null === $maxUpdatedDate) {
            return null;
        }

        $maxDateTime = $remoteServerDateTime ?? new \DateTime('now', new \DateTimeZone('UTC'));

        return $this->getMinDateBetweenUpdatedAndMaxDate($maxUpdatedDate, $maxDateTime);
    }

    /**
     * @param \DateTime|null $currDateToCompare
     * @param \DateTime|null $prevDateToCompare
     * @return \DateTime|null
     */
    protected function getMaxUpdatedDateOrNull(
        \DateTime $currDateToCompare = null,
        \DateTime $prevDateToCompare = null
    ) {
        if (null === $prevDateToCompare || null === $currDateToCompare) {
            return null;
        }

        return $currDateToCompare > $prevDateToCompare
            ? $currDateToCompare
            : $prevDateToCompare;
    }

    /**
     * Compares updated date with max date and returns the smallest.
     *
     * @param \DateTime $updatedDate
     * @param \DateTime $maxDate
     * @return \DateTime
     */
    protected function getMinDateBetweenUpdatedAndMaxDate(
        \DateTime $updatedDate,
        \DateTime $maxDate
    ) {
        if ($maxDate > $updatedDate) {
            return $updatedDate;
        }

        return $maxDate;
    }

    /**
     * @param array $item
     * @param string $updatedAtColumnName
     * @param string $updatedAtColumnFormat
     * @return \DateTime|null
     */
    protected function getUpdatedDateTime(
        array $item,
        string $updatedAtColumnName,
        string $updatedAtColumnFormat
    ): ?\DateTime {
        if (!empty($item[$updatedAtColumnName])) {
            $updatedAtString = $item[$updatedAtColumnName];

            $updatedAt = \DateTime::createFromFormat(
                $updatedAtColumnFormat,
                $updatedAtString,
                new \DateTimeZone('UTC')
            );

            return $updatedAt instanceof \DateTime ? $updatedAt : null;
        }

        return null;
    }
}
