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
 * @since      Class available since version 1.6.2
 */
class NestedModelTest extends ZendDbTestCase
{
    use ZendDbFixtures;
    use ZendDbMigrateFromTestSql;

    /**
     *
     * @var \MUtil\Model\TableModel
     */
    private $_nestedModel;

    /**
     * Create the model
     *
     * @return \MUtil\Model\ModelAbstract
     */
    protected function getNestedModel()
    {
        if (! $this->_nestedModel) {
            $this->_nestedModel = new \MUtil\Model\TableModel('n1');

            $sub = new \MUtil\Model\TableModel('n2');

            $this->_nestedModel->addModel($sub, array('id' => 'pid'));
        }

        return $this->_nestedModel;
    }
    
    public function testJoinTransformer()
    {
        $this->insertFixtures([NestedModelFixtures::class]);

        $main = new \MUtil\Model\TableModel('n1');
        $sub = new \MUtil\Model\TableModel('n2');
        $transformer = new \MUtil\Model\Transform\JoinTransformer();
        $transformer->addModel($sub, array('id' => 'pid'));

        $main->addTransformer($transformer);

        $rows = $main->load();
        $this->assertCount(3, $rows);             // No duplicate records
        $this->assertEquals(2, $rows[0]['cid']);  //last matching record found will be returned
        $this->assertNull($rows[1]['cid']);       // When no match we get null
    }

    public function testHasTwoTables()
    {
        $this->insertFixtures([NestedModelFixtures::class]);

        $model = $this->getNestedModel();
        $rows  = $model->load();
        // error_log(print_r($rows, true));

        $this->assertCount(3, $rows);
        $this->assertCount(2, $rows[0]['n2']);
        $this->assertCount(0, $rows[1]['n2']);
        $this->assertCount(3, $rows[2]['n2']);

        $model = new \MUtil\Model\TableModel('n2');
        $rows  = $model->load();
        $this->assertCount(5, $rows);
    }

    public function testInsertARow()
    {
        $this->insertFixtures([NestedModelFixtures::class]);

        $model  = $this->getNestedModel();
        $result = $model->save(array(
            'id' => null,
            'c1' => "col1-4",
            'c2' => "col2-4",
            'n2' => array(array('c1' => 'p4col1-6', 'c2' => 'p4col2-6')),
            ));
        // error_log(print_r($result, true));
        
        $this->assertEquals(4, $result['id']);
        $this->assertEquals(6, $result['n2'][0]['cid']);

        $rows = $model->load();
        $this->assertCount(4, $rows);
    }
}
