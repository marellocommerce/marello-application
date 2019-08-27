<?php

namespace Marello\Bundle\CoreBundle\Twig;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

class FileExtension extends \Twig_Extension
{
    const NAME = 'marello_file';

    /** @var DoctrineHelper $doctrineHelper */
    protected $doctrineHelper;

    /**
     * FileExtension constructor.
     *
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'get_file_by_id',
                [$this, 'getFileById']
            )
        ];
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getFileById($id)
    {
        return $this->doctrineHelper->getEntityRepository('OroAttachmentBundle:File')->find($id);
    }
}
