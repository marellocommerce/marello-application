<?php

namespace Marello\Bundle\PaymentBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Marello\Bundle\PaymentBundle\Entity\PaymentMethodsConfigsRule;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class PaymentMethodsConfigsRuleHandler
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
     * @param PaymentMethodsConfigsRule $entity
     * @return bool
     */
    public function process(FormInterface $form, PaymentMethodsConfigsRule $entity)
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
