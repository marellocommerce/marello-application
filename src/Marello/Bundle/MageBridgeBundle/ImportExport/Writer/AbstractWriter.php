<?php
/**
 * Created by PhpStorm.
 * User: muhsin
 * Date: 04/04/2018
 * Time: 09:52
 */

namespace Marello\Bundle\MageBridgeBundle\ImportExport\Writer;


use Marello\Bundle\MageBridgeBundle\Provider\Transport\RestTransport;
use Oro\Bundle\IntegrationBundle\ImportExport\Writer\PersistentBatchWriter;

class AbstractWriter extends PersistentBatchWriter
{
    /** @var MagentoTransportInterface */
    protected $transport;

    /**
     * @param RestTransport $transport
     */
    public function setTransport(RestTransport $transport)
    {
        $this->transport = $transport;
    }
}