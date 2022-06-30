<?php

declare(strict_types=1);

namespace MUtilTest\Test;

trait ZendDbTransactions
{
    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $zendDb;

    protected function beginDatabaseTransaction()
    {
        $this->zendDb->beginTransaction();
    }

    protected function rollbackDatabaseTransaction()
    {
        $this->zendDb->rollBack();
    }
}
