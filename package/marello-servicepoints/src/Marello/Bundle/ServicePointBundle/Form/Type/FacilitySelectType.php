<?php

namespace Marello\Bundle\ServicePointBundle\Form\Type;

use Marello\Bundle\ServicePointBundle\Entity\Facility;
use Oro\Bundle\FormBundle\Form\Type\Select2EntityType;
use Oro\Bundle\LocaleBundle\Helper\LocalizationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FacilitySelectType extends AbstractType
{
    protected $localizationHelper;

    public function __construct(LocalizationHelper $localizationHelper)
    {
        $this->localizationHelper = $localizationHelper;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        return $resolver->setDefaults([
            'class' => Facility::class,
            'choice_label' => function (Facility $facility) {
                return $this->localizationHelper->getLocalizedValue($facility->getLabels());
            },
        ]);
    }

    public function getParent()
    {
        return Select2EntityType::class;
    }
}
