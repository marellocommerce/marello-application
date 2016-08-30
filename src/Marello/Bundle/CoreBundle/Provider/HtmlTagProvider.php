<?php

namespace Marello\Bundle\CoreBundle\Provider;

use Oro\Bundle\FormBundle\Provider\HtmlTagProvider as BaseHtmlTagProvider;
/**
 * Class HtmlTagProvider
 *
 * @package Marello\Bundle\CoreBundle\Provider
 */
class HtmlTagProvider extends BaseHtmlTagProvider
{
    public function __construct()
    {
        $elements = $this->elements;
        $newElements = [
            [
                'name'  => 'style',
                'attrs' => 'type=text/css'
            ],
            'head',
            [
                'name'  => 'meta',
                'attrs' => ['http-equiv', 'content', 'charset', 'name', 'width'],
                'hasClosingTag' => false,
            ]
        ];
        $this->elements = array_merge($elements, $newElements);
    }
}
