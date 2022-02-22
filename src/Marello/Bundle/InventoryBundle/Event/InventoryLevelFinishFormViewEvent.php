<?php

namespace Marello\Bundle\InventoryBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\Form\FormView;

class InventoryLevelFinishFormViewEvent extends Event
{
    const NAME = 'marello_inventory.inventory_level.finish_form_view';

    /**
     * @var FormView
     */
    protected $view;

    /**
     * @param FormView $view
     */
    public function __construct(FormView $view)
    {
        $this->view = $view;
    }

    /**
     * @return FormView
     */
    public function getView()
    {
        return $this->view;
    }
}
