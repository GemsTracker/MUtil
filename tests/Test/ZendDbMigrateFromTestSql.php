<?php

namespace MUtilTest\Test;

trait ZendDbMigrateFromTestSql
{
    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $zendDb;

    protected function migrateDatabase(): void
    {
        $reflection = new \ReflectionClass(static::class);
        $classFileName = $reflection->getFileName();
        $sql  = file_get_contents(str_replace('.php', '.sql', $classFileName));

        foreach (\MUtil_Parser_Sql_WordsParser::splitStatements($sql, false) as $sqlCommand) {
            $stmt = $this->zendDb->query($sqlCommand);
        }
    }

}