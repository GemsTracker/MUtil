<?php

/**
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Form\Element;

/**
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Exhibitor extends \Zend_Form_Element_Xhtml implements \MUtil\Form\Element\NoFocusInterface
{
    public $helper = 'exhibitor';

    /**
     * Exhibitor is never required
     *
     * @param  bool $flag Default value is true
     * @return \Zend_Form_Element
     */
    public function setRequired($flag = true)
    {
        return $this;
    }
}
