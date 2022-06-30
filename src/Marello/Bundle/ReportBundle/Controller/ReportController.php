<?php

namespace Marello\Bundle\ReportBundle\Controller;

use Oro\Bundle\DataGridBundle\Datagrid\Manager;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportController extends AbstractController
{
    /**
     * @Route(
     *      path="/static/{reportGroupName}/{reportName}/{_format}",
     *      name="marello_report_index",
     *      requirements={"reportGroupName"="\w+", "reportName"="\w+", "_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Template
     * @AclAncestor("oro_report_view")
     *
     * @param string $reportGroupName
     * @param string $reportName
     *
     * @return array
     */
    public function indexAction($reportGroupName, $reportName)
    {
        $gridName  = implode('-', ['marello_report', $reportGroupName, $reportName]);
        $pageTitle = $this->container->get(Manager::class)->getConfigurationForGrid($gridName)['pageTitle'];

        return [
            'pageTitle' => $this->container->get(TranslatorInterface::class)->trans($pageTitle),
            'gridName'  => $gridName,
            'params'    => [
                'reportGroupName' => $reportGroupName,
                'reportName'      => $reportName
            ]
        ];
    }

    public static function getSubscribedServices()
    {
        return array_merge(
            parent::getSubscribedServices(),
            [
                Manager::class,
                TranslatorInterface::class,
            ]
        );
    }
}
