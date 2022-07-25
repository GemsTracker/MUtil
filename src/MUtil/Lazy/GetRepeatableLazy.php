<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Lazy
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Lazy;

/**
 *
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.7.2 Dec 4, 2015 4:28:32 PM
 */
class GetRepeatableLazy extends \MUtil\Lazy\LazyAbstract
{
    /**
     *
     * @var string
     */
    protected $fieldName;

    /**
     *
     * @var \MUtil\Lazy\RepeatableInterface
     */
    protected $repeater;

    /**
     *
     * @param \MUtil\Lazy\RepeatableInterface $bridge
     * @param string $fieldName
     */
    public function __construct(\MUtil\Lazy\RepeatableInterface $repeater, $fieldName)
    {
        $this->repeater  = $repeater;
        $this->fieldName = $fieldName;
    }

    /**
     * The functions that fixes and returns a value.
     *
     * Be warned: this function may return a lazy value.
     *
     * @param \MUtil\Lazy\StackInterface $stack A \MUtil\Lazy\StackInterface object providing variable data
     * @return mixed
     */
    public function __toValue(\MUtil\Lazy\StackInterface $stack)
    {
        $current = $this->repeater->__current();
        if ($current) {
            if (isset($current->{$this->fieldName})) {
                return $current->{$this->fieldName};
            }
        }
    }
}
