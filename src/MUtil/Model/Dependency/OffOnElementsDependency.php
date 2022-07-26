<?php

/**
 *
 * @package    MUtil
 * @subpackage Model_Dependency
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Dependency;

/**
 * Set attributes when the dependent attribute is OFF - remove them when ON
 *
 * @package    MUtil
 * @subpackage OnOffElementsDependency
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.7 27-apr-2015 18:50:11
 */
class OffOnElementsDependency extends OnOffElementsDependency
{
    /**
     * Constructor checks any subclass set variables
     *
     * @param string $offElement The element that switches the other fields on or off
     * @param array|string $forElements The elements switched on or off
     * @param array|string $mode The values set OFF when $offElement is true
     * @param \MUtil\Model\ModelAbstract $model The model
     */
    public function __construct($offElement, $forElements, $mode = 'readonly', $model = null)
    {
        parent::__construct($offElement, $forElements, $mode, $model);

        // Switch
        $t             = $this->modeOff;
        $this->modeOff = $this->modeOn;
        $this->modeOn  = $t;
    }
}
