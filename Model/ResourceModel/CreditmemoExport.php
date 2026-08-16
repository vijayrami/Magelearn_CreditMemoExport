<?php

namespace Magelearn\CreditMemoExport\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class CreditmemoExport extends AbstractDb
{
    private const TABLE_NAME = 'magelearn_creditmemo_export';
    private const ID_FIELD = 'entity_id';

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, self::ID_FIELD);
    }
}
