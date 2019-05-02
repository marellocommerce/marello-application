<?php

namespace Marello\Bundle\ReportBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration as Config;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

class ReportController extends Controller
{
    /**
     * @Config\Route(
     *      "/static/{reportGroupName}/{reportName}/{_format}",
     *      name="marello_report_index",
     *      requirements={"reportGroupName"="\w+", "reportName"="\w+", "_format"="html|json"},
     *      defaults={"_format" = "html"}
     * )
     * @Config\Template
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
