<?php

namespace Marello\Bundle\Magento2Bundle\Iterator;

use Marello\Bundle\Magento2Bundle\DTO\SearchParametersDTO;

/**
 * Specifies values that uses to init search criteria
 */
interface UpdatableSearchLoaderInterface extends \Iterator
{
    /**
     * @param SearchParametersDTO $searchParametersMessage
     * @return mixed
     */
    public function setSearchParametersDTO(SearchParametersDTO $searchParametersMessage);

    /**
     * @return SearchParametersDTO
     */
    public function getSearchParametersDTO(): SearchParametersDTO;
}
