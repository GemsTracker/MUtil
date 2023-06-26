<?php

declare(strict_types=1);

/**
 * @package    MUtil
 * @subpackage Form\Element
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace MUtil\Form\Element;

use Laminas\Validator\ValidatorInterface;

/**
 * @package    MUtil
 * @subpackage Form\Element
 * @since      Class available since version 1.0
 */
class File extends \Zend_Form_Element_File
{
    use LaminasElementValidatorTrait {
        addValidator as addLaminasValidator;
    }

    public function addValidator($validator, $breakChainOnFailure = false, $options = [])
    {
        $validator = $this->addLaminasValidator($validator, $breakChainOnFailure, $options);

        if (is_array($validator)) {
            $validator = $this->_loadValidator($validator);
        }

        parent::addValidator($validator);
    }
}