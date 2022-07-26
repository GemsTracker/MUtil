<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Bridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Bridge;

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Bridge
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since 2014 $(date} 22:00:30
 */
interface BridgeInterface
{
    /**
     * Construct the bridge while setting the model.
     *
     * Extra parameters can be added in subclasses, but the first parameter
     * must remain the model.
     *
     * @param \MUtil\Model\ModelAbstract $model
     */
    public function __construct(\MUtil\Model\ModelAbstract $model);

    /**
     *
     * @return \MUtil\Model\ModelAbstract
     */
    #public function getModel();
}
