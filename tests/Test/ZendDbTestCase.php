<?php

declare(strict_types=1);

namespace MUtilTest\Test;

use PHPUnit\Framework\TestCase;

class ZendDbTestCase extends TestCase
{
    use ZendDb;
    use ZendDbTransactions;

    protected function migrateDatabase(): void
    {
    }

    public function setup(): void
    {
        parent::setup();
        $sqliteFunctions = new SqliteFunctions();
        $this->initZendDb($sqliteFunctions);
        $this->migrateDatabase();
        $this->beginDatabaseTransaction();
    }

    protected function tearDown(): void
    {
        $this->rollbackDatabaseTransaction();
    }
}
