<?php

namespace MUtilTest\Model;

use MUtilTest\Test\ZendDbFixtures;
use MUtilTest\Test\ZendDbMigrateFromTestSql;
use MUtilTest\Test\ZendDbTestCase;

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 *
 *
 * @package    MUtil
 * @subpackage Model
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.6
 */
class TableModelTest  extends ZendDbTestCase
{
    use ZendDbFixtures;
    use ZendDbMigrateFromTestSql;

    /**
     *
     * @var \MUtil\Model\TableModel
     */
    private $_model;

    /**
     * Create the model
     *
     * @return \MUtil\Model\ModelAbstract
     */
    protected function getModel()
    {
        if (! $this->_model) {
            $this->_model = new \MUtil\Model\TableModel('t1');
        }

        return $this->_model;
    }

    public function testHasFirstRow()
    {
        $this->insertFixtures([TableModelFixtures::class]);

        $model = $this->getModel();
        $rows = $model->load();
        $this->assertCount(1, $rows);
    }

    public function testInsertARow()
    {
        $this->insertFixtures([TableModelFixtures::class]);

        $model  = $this->getModel();
        $result = $model->save(array('id' => null, 'c1' => "col1-2", 'c2' => "col2-2"));
        $this->assertEquals(2, $result['id']);

        $rows = $model->load();
        $this->assertCount(2, $rows);
    }
}
