<?php

namespace Marello\Bundle\PdfBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class MarelloPdfBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        self::marelloPdfTemplateTable($schema);
        self::marelloPdfTemplateTranslationTable($schema);
    }

    /**
     * Generate table oro_email_template
     *
     * @param Schema $schema
     */
    public static function marelloPdfTemplateTable(Schema $schema)
    {
    }

    /**
     * Generate table oro_email_template_translation
     *
     * @param Schema $schema
     */
    public static function marelloPdfTemplateTranslationTable(Schema $schema)
    {
    }

    
}
