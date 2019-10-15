<?php

namespace Marello\Bundle\OroCommerceBundle\Form\Extension;

use Marello\Bundle\OroCommerceBundle\Integration\OroCommerceChannelType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class ChannelConnectorsExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            array($this, 'onPreSubmit')
        );
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            array($this, 'onPostSubmit')
        );
    }

    /**
     * @param FormEvent $event
     */
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data || $data['type'] !== OroCommerceChannelType::TYPE) {
            return;
        }
        $data['synchronizationSettings'] = [
            'isTwoWaySyncEnabled' => 1,
            'syncPriority' => 'local'
        ];
        $event->setData($data);
    }
    
    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data || $data->getType() !== OroCommerceChannelType::TYPE) {
            return;
        }
        $options = $event->getForm()['connectors']->getConfig()->getOptions();
        $connectors = array_values($options['choices']);
        $data->setConnectors($connectors);
    }

    /**
     * Set all connectors disabled and checked on view
     *
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $data = $form->getData();
        if (!$data || $data->getType() !==  OroCommerceChannelType::TYPE) {
            return;
        }

        foreach ($view['connectors']->children as $checkbox) {
            $checkbox->vars['checked'] = true;
            $checkbox->vars['disabled'] = true;
        }
        $isTwoWaySyncEnabled = $view['synchronizationSettings']['isTwoWaySyncEnabled'];
        $isTwoWaySyncEnabled->vars['checked'] = true;
        $isTwoWaySyncEnabled->vars['disabled'] = true;

        $syncPriority = $view['synchronizationSettings']['syncPriority'];
        $syncPriority->vars['value'] = 'local';
        $syncPriority->vars['disabled'] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'oro_integration_channel_form';
    }
}
