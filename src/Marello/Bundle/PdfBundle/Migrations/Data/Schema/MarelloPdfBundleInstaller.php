<?php

namespace Marello\Bundle\PdfBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Marello\Bundle\PdfBundle\Migrations\Schema\v1_0\MarelloPdfBundle;


/**
 * Class MarelloPdfBundleInstaller
 * @package Marello\Bundle\PdfBundle\Migrations\Schema
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MarelloPdfBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        MarelloPdfBundle::marelloPdfTemplateTable($schema);
        MarelloPdfBundle::marelloPdfTemplateTranslationTable($schema);
    }
}
