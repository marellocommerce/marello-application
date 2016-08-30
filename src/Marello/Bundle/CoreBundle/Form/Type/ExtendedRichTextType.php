<?php

namespace Marello\Bundle\CoreBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroRichTextType as BaseRichTextType;

class ExtendedRichTextType extends BaseRichTextType
{
    /**
     * @url http://www.tinymce.com/wiki.php/Configuration:toolbar
     * @var array
     */
    public static $toolbars = [
        self::TOOLBAR_SMALL   => ['undo redo | bold italic underline | bullist numlist link | image bdesk_photo'],
        self::TOOLBAR_DEFAULT => [
            'undo redo | bold italic underline | forecolor backcolor | bullist numlist | link | code | image bdesk_photo'
        ],
        self::TOOLBAR_LARGE   => [
            'undo redo | bold italic underline | forecolor backcolor | bullist numlist | link | code | image bdesk_photo'
        ],
    ];
}
