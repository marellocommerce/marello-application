<?php

namespace Marello\Bundle\CoreBundle\Model;

trait JobIdGenerationTrait
{
    protected function generateJobId($id): int
    {
        $binhash = md5($id, true);
        $numhash = unpack('N2', $binhash);

        return (int) ($numhash[1] . $numhash[2]);
    }
}
