<?php

namespace Marello\Bundle\LocaleBundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;

use Oro\Bundle\EmailBundle\Entity\EmailTemplate;

class EmailTemplateTranslatableRepository extends AbstractTranslatableRepository
{
    protected $class;
    
    /**
     * Initializes a new TranslatableRepository.
     *
     * @param EntityManager $em
     */
    public function __construct($em)
    {
        $this->class = EmailTemplate::class;
        parent::__construct($em, $em->getClassMetadata($this->class));
    }

    /**
     * @param $templateName
     * @param $locale
     * @return null|EmailTemplate
     */
    public function findOneByNameAndLocale($templateName, $locale)
    {
        $qb = $this->_em->createQueryBuilder('et');

        $qb->select('et')
            ->from('OroEmailBundle:EmailTemplate', 'et')
            ->where("et.name = '". $templateName. "'")
        ;

        return $this->getOneOrNullResult($qb, $locale);
    }

    /**
     * @param array $params
     * @param array|null $orderBy
     * @return null|object|EmailTemplate
     */
    public function findOneBy(array $params = [], ?array $orderBy = null)
    {
        return $this->_em->getRepository($this->class)->findOneBy($params, $orderBy);
    }
}
