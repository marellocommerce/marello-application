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
        'recent_emails'   => [0, 10],
        'quick_launchpad' => [1, 20],
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
     * get widgets
     * @return mixed
     */
    protected function getWidgets()
    {
        $rp = $this->manager->getRepository('OroDashboardBundle:Widget');
        $qb = $rp->createQueryBuilder('wdt');

        return $qb
            ->where("wdt.name IN(:widgets)")
            ->setParameter('widgets', array_keys($this->widgets))
            ->getQuery()
            ->getResult();
    }
}
