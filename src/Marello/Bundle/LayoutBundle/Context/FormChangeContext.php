<?php

namespace Marello\Bundle\LayoutBundle\Context;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

class FormChangeContext extends ParameterBag implements FormChangeContextInterface
{
    const FORM_FIELD = 'form';
    const SUBMITTED_DATA_FIELD = 'submitted_data';
    const RESULT_FIELD = 'result';

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get(self::FORM_FIELD);
    }

    /**
     * {@inheritdoc}
     */
    public function setForm(FormInterface $form)
    {
        $this->set(self::FORM_FIELD, $form);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubmittedData()
    {
        return $this->get(self::SUBMITTED_DATA_FIELD);
    }

    /**
     * {@inheritdoc}
     */
    public function setSubmittedData(array $submittedData)
    {
        $this->set(self::SUBMITTED_DATA_FIELD, $submittedData);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getResult()
    {
        return $this->get(self::RESULT_FIELD);
    }

    /**
     * {@inheritdoc}
     */
    public function setResult(array $result)
    {
        $this->set(self::RESULT_FIELD, $result);

        return $this;
    }
}
