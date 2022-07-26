<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Form
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Form\Element;

/**
 * Add Html labels to standard parent
 *
 * @package    MUtil
 * @subpackage Form
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class Select extends \Zend_Form_Element_Select
{
    /**
     * Use formSelect view helper by default
     * @var string
     */
    public $helper = 'formSelectHtml';
}
