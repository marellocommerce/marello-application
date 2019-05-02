<?php

namespace Marello\Bundle\TaxBundle\Event;

use Marello\Bundle\TaxBundle\Resolver\ResolverInterface;

class ResolverEventConnector
{
    /** @var ResolverInterface */
    protected $resolver;

    /**
     * @param ResolverInterface $resolver
     */
    public function __construct(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param ResolveTaxEvent $event
     */
    public function onResolve(ResolveTaxEvent $event)
    {
        try {
            $this->resolver->resolve($event->getTaxable());
        } catch (\Exception $e) {
            $event->stopPropagation();
        }
    }
}
