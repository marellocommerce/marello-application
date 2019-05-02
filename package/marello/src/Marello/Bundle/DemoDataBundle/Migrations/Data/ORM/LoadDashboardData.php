<?php

namespace Marello\Bundle\DemoDataBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\AbstractDashboardFixture;

class LoadDashboardData extends AbstractDashboardFixture implements DependentFixtureInterface
{
    /** @var ObjectManager $manager */
    protected $manager;

    protected $widgets = [
        'my_calendar'     => 'remove',
        'top_revenue_channels_widget' => [0,0],
        'latest_orders_widget' => [1,0],
        'recent_emails'   => [1, 2],
        'quick_launchpad' => [0,1],
    ];

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'Oro\Bundle\CalendarBundle\Migrations\Data\ORM\LoadDashboardData',
            'Oro\Bundle\EmailBundle\Migrations\Data\ORM\LoadDashboardData',
            'Oro\Bundle\DashboardBundle\Migrations\Data\ORM\LoadDashboardData'
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        //get main dashboard
        $mainDashboard = $this->findAdminDashboardModel($manager, 'main');

        if ($mainDashboard) {
            $widgets = $this->getWidgets();
            if (count($widgets) > 0) {
                foreach ($widgets as $widget) {
                    $action = $this->widgets[$widget->getName()];
                    if ($action === 'remove') {
                        $manager->remove($widget);
                    } else {
                        $widget->setLayoutPosition($action);
                        $manager->persist($widget);
                    }
                }

                $manager->flush();
            }
        }
    }

    /**
     * @return array
     */
    protected function getWidgets()
    {
        return $this->manager->getRepository('OroDashboardBundle:Widget')
            ->createQueryBuilder('wdt')
            ->where("wdt.name IN(:widgets)")
            ->setParameter('widgets', array_keys($this->widgets))
            ->getQuery()
            ->getResult()
        ;
    }
}
