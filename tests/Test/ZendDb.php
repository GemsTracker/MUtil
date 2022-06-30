<?php

namespace MUtilTest\Test;

trait ZendDb
{
    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $zendDb;

    public function initZendDb(SqliteFunctions $sqliteFunctions)
    {
        if (!defined('DB_CONNECTION') || DB_CONNECTION === 'pdo_Sqlite') {

            if (!defined('DB_DATABASE')) {
                define('DB_DATABASE', ':memory:');
            }

            $this->zendDb = \Zend_Db::factory(
                'Pdo_sqlite',
                [
                    'dbname' => DB_DATABASE
                ]
            );

            $pdo = $this->zendDb->getConnection();
            $sqliteFunctions->addSqlFunctonsToPdoAdapter($pdo);

        } else {
            $this->zendDb = \Zend_Db::factory('Pdo_Mysql',
                [
                    'dbname' => DB_DATABASE,
                    'host' => DB_HOST,
                    'username' => DB_USERNAME,
                    'password' => DB_PASSWORD,
                ]
            );
        }

        \Zend_Db_Table::setDefaultAdapter($this->zendDb);
    }
}
