<?php

namespace Marello\Bundle\ServicePointBundle\Form\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Marello\Bundle\ServicePointBundle\Entity\BusinessHours;
use Marello\Bundle\ServicePointBundle\Entity\ServicePointFacility;
use Marello\Bundle\ServicePointBundle\Entity\TimePeriod;
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
        $originalTimePeriods = new ArrayCollection();
        foreach ($originalBusinessHours as $businessHours) {
            $originalTimePeriods[$businessHours->getId()] = clone $businessHours->getTimePeriods();
        }

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->submitPostPutRequest($this->form, $this->request);
            if ($this->form->isValid()) {
                $this->onSuccess($entity, $originalBusinessHours, $originalTimePeriods);

                return true;
            }
        }

        return false;
    }

    /**
     * @param ServicePointFacility $entity
     * @param Collection $originalBusinessHours
     * @param Collection $originalTimePeriods
     */
    protected function onSuccess(
        ServicePointFacility $entity,
        Collection $originalBusinessHours,
        Collection $originalTimePeriods
    ) {
        $this->manager->persist($entity);

        foreach ($entity->getBusinessHours() as $businessHours) {
            $businessHours->setServicePointFacility($entity);
            $this->manager->persist($businessHours);

            foreach ($businessHours->getTimePeriods() as $timePeriod) {
                $timePeriod->setBusinessHours($businessHours);
                $this->manager->persist($timePeriod);
            }
        }

        foreach ($originalBusinessHours as $businessHours) {
            $newBusinessHours = $entity->getBusinessHours()->filter(function (BusinessHours $bh) use ($businessHours) {
                return $bh->getId() === $businessHours->getId();
            })->first();

            if ($newBusinessHours) {
                foreach ($originalTimePeriods[$businessHours->getId()] as $timePeriod) {
                    if (!$newBusinessHours->getTimePeriods()->exists(function ($x, TimePeriod $tp) use ($timePeriod) {
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

        $this->manager->flush();
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
