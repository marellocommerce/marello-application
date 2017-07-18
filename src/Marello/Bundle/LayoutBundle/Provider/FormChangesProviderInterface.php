<?php

namespace Marello\Bundle\LayoutBundle\Provider;

use Symfony\Component\Form\FormInterface;

interface FormChangesProviderInterface
{
    /**
     * @param FormInterface $form
     * @param array|null $submittedData
     * @return mixed
     */
    public function getFormChangesData(FormInterface $form, array $submittedData = null);
}
