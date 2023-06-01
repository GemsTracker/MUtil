<?php

/**
 *
 * Short description of file
 *
 * @package    MUtil
 * @subpackage JQuery
 * @author     Menno Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Bootstrap\Form\Element;

/**
 * Short description for ToggleCheckboxes
 *
 * Long description for class ToggleCheckboxes (if any)...
 *
 * @package    MUtil
 * @subpackage JQuery
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class ToggleCheckboxes extends \MUtil\Bootstrap\Form\Element\Button
{
    protected $_elementClass = 'btn toggle-btn';

    public function __construct($spec, $options = null)
    {
        parent::__construct($spec, $options);
    }
}