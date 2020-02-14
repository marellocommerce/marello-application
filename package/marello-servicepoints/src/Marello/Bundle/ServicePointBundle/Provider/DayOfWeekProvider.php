<?php

namespace Marello\Bundle\ServicePointBundle\Provider;

use Oro\Bundle\LocaleBundle\Model\Calendar;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;

class DayOfWeekProvider
{
    protected $localeSettings;

    protected $calendar;

    public function __construct(LocaleSettings $localeSettings)
    {
        $this->localeSettings = $localeSettings;
    }

    public function getDaysOfWeekChoices(): array
    {
        return array_flip($this->getDaysOfWeek());
    }

    public function getDaysOfWeek(): array
    {
        $daysOfWeek = $this->getCalendar()->getDayOfWeekNames();
        $firstDayOfWeek = $this->getCalendar()->getFirstDayOfWeek();

        $result = [];
        for ($i = 0; $i < 7; $i++) {
            $dayNumber = (($firstDayOfWeek + $i) % 7) + 1;
            $result[$dayNumber] = $daysOfWeek[$dayNumber];
        }

        return $result;
    }

    protected function getCalendar(): Calendar
    {
        if ($this->calendar === null) {
            $this->calendar = $this->localeSettings->getCalendar();
        }

        return $this->calendar;
    }
}
