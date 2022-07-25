<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Lazy
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Lazy;

/**
 * Wrap lazyness around an object.
 *
 * Calls to methods and properties return a lazy object that will be
 * evaluated only when forced to a string value or when called using ->__toValue().
 *
 * <code>
 * $arrayObj = new \ArrayObject();
 * $arrayObj->setFlags(\ArrayObject::ARRAY_AS_PROPS);
 *
 * $arrayObj['a'] = 'old';
 *
 * $lazy_obj = new \MUtil\Lazy\ObjectWrap($arrayObj);
 * $output = array($arrayObj->a, $lazy_obj->a, $arrayObj->count(), $lazy_obj->count());
 *
 * echo $output[0] . ' -> ' . $output[1] . ' | ' . $output[2] . ' -> ' . $output[3];
 * // Result old -> old | 1 -> 1
 *
 * $arrayObj->a = 'new';
 * $arrayObj[] = 2;
 *
 * echo $output[0] . ' -> ' . $output[1] . ' | ' . $output[2] . ' -> ' . $output[3];
 * // Result old -> new | 1 -> 2
 * </code>
 *
 * @package    MUtil
 * @subpackage Lazy
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class ObjectWrap extends \MUtil\Lazy\LazyAbstract
{
    protected $_object;

    public function __construct($object)
    {
        $this->_object = $object;
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
        return $this->_object;
    }
}
