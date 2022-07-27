<?php

/**
 *
 * @package    MUtil
 * @subpackage Bootstrap\Form\Element\Hash
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace MUtil\Bootstrap\Form\Element;

use MUtil\Form\Element\LaminasElementValidator;

/**
 *
 * @package    MUtil
 * @subpackage Bootstrap\Form\Element\Hash
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.2 Nov 23, 2016 4:34:13 PM
 */
class Hash extends \Zend_Form_Element_Hash
{
    use LaminasElementValidator;

	/**
     * Load default decorators
     *
     * @return \Zend_Form_Element
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('ViewHelper');
        }
        return $this;
    }
}
