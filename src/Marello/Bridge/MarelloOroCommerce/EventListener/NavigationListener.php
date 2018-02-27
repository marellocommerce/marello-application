<?php

namespace Marello\Bridge\MarelloOroCommerce\EventListener;

use Symfony\Component\Translation\TranslatorInterface;

use Knp\Menu\ItemInterface;

use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;

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
                foreach ($child->getChildren() as $subChild) {
                   if ($this->startsWith($subChild->getLabel(), 'marello')) {
                        $subChild->setLabel(sprintf('ERP %s', $this->translator->trans($subChild->getLabel())));
                   }
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
