<?php

namespace Marello\Bundle\ServicePointBundle\Twig;

use Marello\Bundle\ServicePointBundle\Provider\DayOfWeekProvider;

class DayOfWeekExtension extends \Twig_Extension
{
    protected $dayOfWeekProvider;

    public function __construct(DayOfWeekProvider $dayOfWeekProvider)
    {
        $this->dayOfWeekProvider = $dayOfWeekProvider;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('format_dow', [$this, 'formatDayOfWeek']),
        ];
    }

    public function formatDayOfWeek($dayOfWeek)
    {
        $daysOfWeek = $this->dayOfWeekProvider->getDaysOfWeek();

        return $daysOfWeek[$dayOfWeek];
    }

    public function getName()
    {
        return 'marello_dayofweek';
    }
}
