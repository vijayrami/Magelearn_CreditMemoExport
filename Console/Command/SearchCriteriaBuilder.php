<?php

namespace Magelearn\CreditMemoExport\Console\Command;

use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder as CoreSearchCriteriaBuilder;

class SearchCriteriaBuilder
{
    /**
     * @var CoreSearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    public function __construct(CoreSearchCriteriaBuilder $searchCriteriaBuilder)
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function build(
        ?string $incrementId,
        ?string $storeId,
        ?string $requestedFrom,
        ?string $requestedTo
    ): SearchCriteria {
        if ($incrementId) {
            $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId);
        }

        if ($storeId) {
            $this->searchCriteriaBuilder->addFilter('store_id', $storeId);
        }

        if ($requestedFrom) {
            $this->searchCriteriaBuilder->addFilter('date_requested', $requestedFrom, 'gteq');
        }

        if ($requestedTo) {
            $this->searchCriteriaBuilder->addFilter('date_requested', $requestedTo, 'lteq');
        }

        return $this->searchCriteriaBuilder->create();
    }
}
