<?php

namespace Marello\Bundle\LayoutBundle\Context;

use Symfony\Component\Form\FormInterface;

interface FormChangeContextInterface
{
    /**
     * @return FormInterface
     */
    public function getForm();

    /**
     * @param FormInterface $form
     * @return $this
     */
    public function setForm(FormInterface $form);

    /**
     * @return array
     */
    public function getSubmittedData();

    /**
     * @param array $submittedData
     * @return $this
     */
    public function setSubmittedData(array $submittedData);

    /**
     * @return array
     */
    public function getResult();

    /**
     * @param array $result
     * @return $this
     */
    public function setResult(array $result);
}
