<?php

namespace Marello\Bundle\ShippingBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Marello\Bundle\ShippingBundle\Entity\ShippingMethodsConfigsRule;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ShippingMethodsConfigsRuleHandler
{
    use RequestHandlerTrait;

    const UPDATE_FLAG = 'update_methods_flag';

    /** @var Request */
    protected $request;

    /** @var EntityManager */
    protected $em;

    /** @var FormInterface */
    protected $form;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param RequestStack $requestStack
     * @param EntityManager $em
     */
    public function __construct(RequestStack $requestStack, EntityManager $em)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->em = $em;
    }

    /**
     * @param FormInterface $form
     * @param ShippingMethodsConfigsRule $entity
     * @return bool
     */
    public function process(FormInterface $form, ShippingMethodsConfigsRule $entity)
    {
        $form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($form, $this->request);
            if (!$this->request->get(self::UPDATE_FLAG, false) && $form->isValid()) {
                $this->em->persist($entity);
                $this->em->flush();

                return true;
            }
        }

        return false;
    }
}
