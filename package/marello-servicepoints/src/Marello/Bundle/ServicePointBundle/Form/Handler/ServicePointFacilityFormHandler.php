<?php

namespace Marello\Bundle\ServicePointBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Marello\Bundle\ServicePointBundle\Entity\ServicePointFacility;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ServicePointFacilityFormHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var EntityManager */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param RequestStack  $requestStack
     * @param ObjectManager $manager
     */
    public function __construct(
        FormInterface $form,
        RequestStack  $requestStack,
        ObjectManager $manager
    ) {
        $this->form = $form;
        $this->request = $requestStack->getCurrentRequest();
        $this->manager = $manager;
    }

    /**
     * @param ServicePointFacility $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(ServicePointFacility $entity)
    {
        $this->form->setData($entity);
        $originalBusinessHours = clone $entity->getBusinessHours();
        $originalTimePeriods = $this->cloneTimePeriods($entity->getBusinessHours());

        $originalBusinessHoursOverrides = clone $entity->getBusinessHoursOverrides();
        $originalTimePeriodsOverrides = $this->cloneTimePeriods($entity->getBusinessHoursOverrides());

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($this->form, $this->request);
            if ($this->form->isValid()) {
                $this->onSuccess(
                    $entity,
                    $originalBusinessHours,
                    $originalTimePeriods,
                    $originalBusinessHoursOverrides,
                    $originalTimePeriodsOverrides
                );

                return true;
            }
        }

        return false;
    }

    /**
     * @param ServicePointFacility $entity
     * @param Collection $originalBusinessHours
     * @param Collection $originalTimePeriods
     * @param Collection $originalBusinessHoursOverrides
     * @param Collection $originalTimePeriodsOverrides
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function onSuccess(
        ServicePointFacility $entity,
        Collection $originalBusinessHours,
        Collection $originalTimePeriods,
        Collection $originalBusinessHoursOverrides,
        Collection $originalTimePeriodsOverrides
    ) {
        $this->manager->persist($entity);

        $this->persistBusinessHours($entity);
        $this->persistBusinessHoursOverrides($entity);
        $this->pruneBusinessHours($entity, $originalBusinessHours, $originalTimePeriods);
        $this->pruneBusinessHoursOverrides($entity, $originalBusinessHoursOverrides, $originalTimePeriodsOverrides);

        $this->manager->flush();
    }

    /**
     * @param ServicePointFacility $entity
     * @param Collection $originalBusinessHours
     * @param Collection $originalTimePeriods
     * @throws \Doctrine\ORM\ORMException
     */
    protected function pruneBusinessHours(
        ServicePointFacility $entity,
        Collection $originalBusinessHours,
        Collection $originalTimePeriods
    ) {
        $this->doPruneBusinessHours($entity->getBusinessHours(), $originalBusinessHours, $originalTimePeriods);
    }

    /**
     * @param ServicePointFacility $entity
     * @param Collection $originalBusinessHoursOverrides
     * @param Collection $originalTimePeriodsOverrides
     * @throws \Doctrine\ORM\ORMException
     */
    protected function pruneBusinessHoursOverrides(
        ServicePointFacility $entity,
        Collection $originalBusinessHoursOverrides,
        Collection $originalTimePeriodsOverrides
    ) {
        $this->doPruneBusinessHours(
            $entity->getBusinessHoursOverrides(),
            $originalBusinessHoursOverrides,
            $originalTimePeriodsOverrides
        );
    }

    /**
     * @param ServicePointFacility $entity
     * @throws \Doctrine\ORM\ORMException
     */
    protected function persistBusinessHours(ServicePointFacility $entity)
    {
        $this->doPersistBusinessHours($entity, $entity->getBusinessHours());
    }

    /**
     * @param ServicePointFacility $entity
     * @throws \Doctrine\ORM\ORMException
     */
    protected function persistBusinessHoursOverrides(ServicePointFacility $entity)
    {
        $this->doPersistBusinessHours($entity, $entity->getBusinessHoursOverrides());
    }

    /**
     * @param Collection $newBusinessHours
     * @param Collection $originalBusinessHours
     * @param Collection $originalTimePeriods
     * @throws \Doctrine\ORM\ORMException
     */
    protected function doPruneBusinessHours(
        Collection $newBusinessHours,
        Collection $originalBusinessHours,
        Collection $originalTimePeriods
    ) {
        foreach ($originalBusinessHours as $businessHours) {
            $matchingBusinessHours = $newBusinessHours->filter(function ($bh) use ($businessHours) {
                return $bh->getId() === $businessHours->getId();
            })->first();

            if ($matchingBusinessHours) {
                foreach ($originalTimePeriods[$businessHours->getId()] as $timePeriod) {
                    if (!$matchingBusinessHours->getTimePeriods()->exists(function ($x, $tp) use ($timePeriod) {
                        return $tp->getId() === $timePeriod->getId();
                    })) {
                        $this->manager->remove($timePeriod);
                    }
                }
            } else {
                $this->manager->remove($businessHours);

                foreach ($originalTimePeriods[$businessHours->getId()] as $timePeriod) {
                    $this->manager->remove($timePeriod);
                }
                unset($originalTimePeriods[$businessHours->getId()]);
            }
        }
    }

    /**
     * @param ServicePointFacility $entity
     * @param Collection $businessHours
     * @throws \Doctrine\ORM\ORMException
     */
    protected function doPersistBusinessHours(ServicePointFacility $entity, Collection $businessHours)
    {
        foreach ($businessHours as $businessHour) {
            $businessHour->setServicePointFacility($entity);
            $this->manager->persist($businessHour);

            foreach ($businessHour->getTimePeriods() as $timePeriod) {
                $timePeriod->setBusinessHours($businessHour);
                $this->manager->persist($timePeriod);
            }
        }
    }

    /**
     * @param Collection $businessHours
     * @return ArrayCollection
     */
    protected function cloneTimePeriods(Collection $businessHours)
    {
        $timePeriods = new ArrayCollection();
        foreach ($businessHours as $businessHour) {
            $timePeriods[$businessHour->getId()] = clone $businessHour->getTimePeriods();
        }

        return $timePeriods;
    }

    /**
     * Returns form instance
     *
     * @return FormView
     */
    public function getFormView()
    {
        return $this->form->createView();
    }
}
