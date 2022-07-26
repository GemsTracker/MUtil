<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Form\Element;

/**
 * Interface for elements that move the focus to one of their sub elements
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
interface SubFocusInterface
{
    /**
     * Get the (possibly focusing) elements/dispalygroups/form contained by this element
     *
     * return array of elements or subforms
     */
    public function getSubFocusElements();
}

