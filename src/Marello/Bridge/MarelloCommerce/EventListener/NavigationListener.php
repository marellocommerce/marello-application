<?php

namespace Marello\Bridge\MarelloCommerce\EventListener;

use Knp\Menu\ItemInterface;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Symfony\Component\Translation\TranslatorInterface;

class NavigationListener
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param ConfigureMenuEvent $event
     */
    public function onNavigationConfigure(ConfigureMenuEvent $event)
    {
        $menu = $event->getMenu();

        foreach ($menu->getChildren() as $child) {
            if ($this->itemHasChildrenFromDifferentApplications($child)) {
                $subChildren = $child->getChildren();
                $marello = $child->addChild(sprintf('%s.%s', $child->getName(), 'marello'), ['uri' => '#'])
                    ->setLabel('Marello')
                    ->setLinkAttribute('class','unclickable')
                    ->setExtra('position', 10)
                    ->setAttribute('divider_append', true);
                $oro = $child->addChild(sprintf('%s.%s', $child->getName(), 'oro'), ['uri' => '#'])
                    ->setLabel('Oro')
                    ->setLinkAttribute('class','unclickable')
                    ->setExtra('position', 20)
                    ->setExtra('type', 'dropdown');
                foreach ($subChildren as $subChild) {
                    if ($this->startsWith($subChild->getLabel(), 'oro')) {
                        $oro->addChild($subChild->copy());
                    } elseif ($this->startsWith($subChild->getLabel(), 'marello')) {
                        $marello->addChild($subChild->copy());
                    }
                    $child->removeChild($subChild);
                }
            }
        }
    }

    /**
     * @param ItemInterface $item
     * @return bool
     */
    private function itemHasChildrenFromDifferentApplications(ItemInterface $item)
    {
        $oroCnt = 0;
        $marelloCnt = 0;
        foreach ($item->getChildren() as $child) {
            if ($this->startsWith($child->getLabel(), 'oro')) {
                $oroCnt++;
            } elseif ($this->startsWith($child->getLabel(), 'marello')) {
                $marelloCnt++;
            }
        }
        if ($oroCnt > 0 && $marelloCnt > 0) {
            return true;
        }

        return false;
    }

    /**
     * @param string $haystack The string to check
     * @param string $needle   The string to compare
     *
     * @return bool
     */
    protected function startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }
}
