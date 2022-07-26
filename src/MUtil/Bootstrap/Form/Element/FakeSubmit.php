<?php

/**
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @author     Menno Dekker <menno.dekker@erasmusmc.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Bootstrap\Form\Element;

/**
 * A button element acting as a Submit button, but possibly placed in the
 * form before the "real" submit button.
 *
 * This ensures that pressing "Enter" will activate the real submit button.
 *
 * @package    MUtil
 * @subpackage Form_Element
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5.6
 */
class FakeSubmit extends \MUtil\Bootstrap\Form\Element\Button
{
    /**
     * Use fakeSubmit view helper by default
     * @var string
     */
    public $helper = 'fakeSubmit';

    public $target;
    public $targetValue;
    public $targetValueIsElement;

    public function getTarget()
    {
        return $this->target;
    }

    public function getTargetValue()
    {
        if (! $this->targetValue) {
            $this->targetValue = $this->getLabel();
        }

        return $this->targetValue;
    }

    public function getTargetValueIsElement()
    {
        return $this->targetValueIsElement;
    }

    public function setTarget($targetName, $value = null, $valueIsOfElement = null)
    {
        $this->target = $targetName;

        if (null !== $value) {
            $this->setTargetValue($value, $valueIsOfElement);
        }

        return $this;
    }

    public function setTargetValue($value, $valueIsOfElement = null)
    {
        $this->targetValue = $value;

        if (null !== $valueIsOfElement) {
            $this->setTargetValueIsElement($valueIsOfElement);
        }

        return $this;
    }

    public function setTargetValueIsElement($valueIsOfElement = false)
    {
        $this->targetValueIsElement = $valueIsOfElement;

        return $this;
    }
}