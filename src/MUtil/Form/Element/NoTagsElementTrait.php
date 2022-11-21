<?php

/**
 *
 * @package    MUtil
 * @subpackage Form\Element
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 */

namespace MUtil\Form\Element;

use Laminas\Validator\ValidatorInterface;
use MUtil\Validate\NoTags;

/**
 *
 * @package    MUtil
 * @subpackage Form\Element
 * @copyright  Copyright (c) 2016, Erasmus MC and MagnaFacta B.V.
 * @license    New BSD License
 * @since      Class available since version 1.8.2 Feb 7, 2017 5:17:48 PM
 */
trait NoTagsElementTrait 
{
    use LaminasElementValidator;
    
    /**
     * Flag indicating whether or not to insert NoTags validator
     * @var bool
     */
    protected $_autoInsertNoTagsValidator = true;

    /** 
     * Add no tags validator if not already set
     *
     * @return \MUtil\Form\Element\NoTagsElementTrait
     */
    public function addNoTagsValidator()
    {
        if (!$this->getValidator('MUtil\\Validate\\NoTags')) {
            $this->addValidator(new NoTags());
        }

        return $this;
    }

    /**
     * Get flag indicating whether a NoTags validator should be inserted
     *
     * @return bool
     */
    public function autoInsertNoTagsValidator()
    {
        return $this->_autoInsertNoTagsValidator;
    }

    /**
     * Retrieve all validators
     *
     * @return array
     */
    public function getValidators()
    {
        if ($this->autoInsertNoTagsValidator()) {
            $this->addNoTagsValidator();
        }
        $validators = [];
        foreach ($this->_validators as $key => $value) {
            if ($value instanceof ValidatorInterface) {
                $validators[$key] = $value;
                continue;
            }
            $validator = $this->_loadValidator($value);
            $validators[get_class($validator)] = $validator;
        }
        return $validators;
    }

    /**
     * Validate element value
     *
     * If a translation adapter is registered, any error messages will be
     * translated according to the current locale, using the given error code;
     * if no matching translation is found, the original message will be
     * utilized.
     *
     * Note: The *filtered* value is validated.
     *
     * @param  mixed $value
     * @param  mixed $context
     * @return boolean
     * /
    public function isValid($value, $context = null)
    {
        if ($this->autoInsertNoTagsValidator()) {
            $this->addNoTagsValidator();
        }
        return parent::isValid($value, $context);
    }


    /**
     * Set flag indicating whether a NoTags validator should be inserted
     *
     * @param  bool $flag
     * @return \MUtil\Form\Element\NoTagsElementTrait
     */
    public function setAutoInsertNoTagsValidator($flag)
    {
        $this->_autoInsertNoTagsValidator = (bool) $flag;
        return $this;
    }
}
