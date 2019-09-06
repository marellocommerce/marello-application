<?php

namespace Marello\Bundle\ReportBundle\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

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
        $pageTitle = $this->get('oro_datagrid.datagrid.manager')->getConfigurationForGrid($gridName)['pageTitle'];

        return [
            'pageTitle' => $this->get('translator')->trans($pageTitle),
            'gridName'  => $gridName,
            'params'    => [
                'reportGroupName' => $reportGroupName,
                'reportName'      => $reportName
            ]
        ];
    }
}
