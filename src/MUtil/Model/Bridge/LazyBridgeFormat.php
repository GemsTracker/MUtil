<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Model\Bridge
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Bridge;

/**
 *
 *
 * @package    MUtil
 * @subpackage Model\Bridge
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.7.2 Dec 4, 2015 3:00:44 PM
 */
class LazyBridgeFormat extends \MUtil\Lazy\LazyAbstract
{
    /**
     *
     * @var \MUtil\Model\Bridge\BridgeAbstract
     */
    protected $bridge;

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
     * @param \MUtil\Model\Bridge\BridgeInterface $bridge
     * @param string $fieldName
     */
    public function __construct(\MUtil\Model\Bridge\BridgeAbstract $bridge, $fieldName)
    {
        $this->bridge    = $bridge;
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
        if (! $this->repeater) {
            $this->repeater = $this->bridge->getRepeater();
        }

        $out     = null;
        $current = $this->repeater->__current();
        if ($current) {
            if (isset($current->{$this->fieldName})) {
                $out = $current->{$this->fieldName};
            }
        }
        return $this->bridge->format($this->fieldName, $out);
     }
}
