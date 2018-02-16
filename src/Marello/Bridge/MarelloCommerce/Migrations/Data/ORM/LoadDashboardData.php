<?php

namespace Marello\Bridge\MarelloCommerce\Migrations\Data\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Oro\Bundle\DashboardBundle\Migrations\Data\ORM\AbstractDashboardFixture;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\LoadAdminUserData;
use Oro\Bundle\UserBundle\Migrations\Data\ORM\UpdateUserEntitiesWithOrganization;

class LoadDashboardData extends AbstractDashboardFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadAdminUserData::class,
            UpdateUserEntitiesWithOrganization::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        // create new dashboard
        /*$dashboard = $this->createAdminDashboardModel(
            $manager,      // pass ObjectManager
            'new_dashoard' // dashboard name
        );*/

        // to update existing one
        /*$dashboard = $this->findAdminDashboardModel(
            $manager,      // pass ObjectManager
            'main' // dashboard name
        );

        $dashboard*/
            // if user doesn't have active dashboard this one will be used
            /*->setIsDefault(true)*/

            // dashboard label
            /*->setLabel(
                $this->container->get('translator')->trans('oro.dashboard.title.main')
            )*/

            // add widgets one by one
            /*->addWidget(
                $this->createWidgetModel(
                    'quick_launchpad',  // widget name from yml configuration
                    [
                        0, // column, starting from left
                        10 // position, starting from top
                    ]
                )
            );

        $manager->flush();*/
    }
}
