<?php

namespace Marello\Bundle\ServicePointBundle\Form\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Marello\Bundle\ServicePointBundle\Entity\BusinessHours;
use Marello\Bundle\ServicePointBundle\Form\Type\BusinessHoursType;
use Marello\Bundle\ServicePointBundle\Provider\DayOfWeekProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

class BusinessHoursCollectionEventListener implements EventSubscriberInterface
{
    protected $dayOfWeekProvider;

    public function __construct(DayOfWeekProvider $dayOfWeekProvider)
    {
        $this->dayOfWeekProvider = $dayOfWeekProvider;
    }

    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => ['onPreSetData'],
            FormEvents::POST_SUBMIT => ['onPostSubmit'],
        ];
    }

    public function onPreSetData(FormEvent $event)
    {
        $event->setData(
            $this->appendBusinessHours(
                $event->getData()
            )
        );

        $this->adjustForm($event->getForm());
    }

    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        foreach ($data as $i => $item) {
            if (count($item->getTimePeriods()) === 0) {
                $data->remove($i);
            }
        }
    }

    protected function adjustForm(FormInterface $form)
    {
        $dowCount = count($this->dayOfWeekProvider->getDaysOfWeek());

        for ($i = $form->count(); $i < $dowCount; $i++) {
            $form->add($i, BusinessHoursType::class, []);
        }
    }

    protected function appendBusinessHours($modelValue)
    {
        if ($modelValue === null) {
            $modelValue = new ArrayCollection();
        }

        foreach (array_keys($this->dayOfWeekProvider->getDaysOfWeek()) as $dow) {
            if (!$modelValue->exists(function ($key, BusinessHours $x) use ($dow) {
                return $x->getDayOfWeek() === $dow;
            })) {
                $businessHours = new BusinessHours();
                $businessHours->setDayOfWeek($dow);

                $modelValue->add($businessHours);
            }
        }

//        dump($modelValue);

        return $this->getSortedCollection($modelValue);
    }

    protected function getSortedCollection(Collection $collection): Collection
    {
        $values = $collection->toArray();

        $dows = array_keys($this->dayOfWeekProvider->getDaysOfWeek());

        usort($values, function (BusinessHours $bha, BusinessHours $bhb) use ($dows) {
            return array_search($bha->getDayOfWeek(), $dows) - array_search($bhb->getDayOfWeek(), $dows);
        });

        return new ArrayCollection($values);
    }
}
