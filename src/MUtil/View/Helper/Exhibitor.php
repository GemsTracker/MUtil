<?php

/**
 *
 * @package    MUtil
 * @subpackage View
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\View\Helper;

use \DateTimeImmutable;
use \DateTimeInterface;

/**
 *
 * @package    MUtil
 * @subpackage View
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Exhibitor extends \Zend_View_Helper_FormElement
{
    /**
     * Generates a fake element that just displays the item with a hidden extra value field.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are extracted in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function exhibitor($name, $value = null, $attribs = null)
    {
        $escape = true;
        $result = $value;

        if (isset($attribs['default'])) {
            if (null === $result) {
                $result = $attribs['default'];
            }
        }

        if (isset($attribs['multiOptions'])) {
            $multiOptions = $attribs['multiOptions'];

            if (is_array($multiOptions)) {
                /*
                 *  Sometimes a field is an array and will be formatted later on using the
                 *  formatFunction -> handle each element in the array.
                 */
                if (is_array($result)) {
                    foreach($result as $key => $arrayValue) {
                        if (is_scalar($arrayValue) && array_key_exists($arrayValue, $multiOptions)) {
                            $result[$key] = $multiOptions[$arrayValue];
                        }
                    }
                } else {
                    if (array_key_exists($result, $multiOptions)) {
                        $result = $multiOptions[$result];
                    }
                }
            }
        }

        if (isset($attribs['formatFunction'])) {
            $callback = $attribs['formatFunction'];
            $result = call_user_func($callback, $result);
        } elseif (isset($attribs['dateFormat'])) {
            $dateFormat    = $attribs['dateFormat'];
            $storageFormat = isset($attribs['storageFormat']) ? $attribs['storageFormat'] : null;

            if (! $result instanceof DateTimeInterface) {
                $date = DateTimeImmutable::createFromFormat($storageFormat, $result);
                if ($date) {
                    $result = $date->format($dateFormat);
                } else {
                    $result = null;
                }
            }

            if ($storageFormat && ($value instanceof DateTimeInterface)) {
                $value = $value->format($storageFormat);
            }
        }

        if (isset($attribs['itemDisplay'])) {
            $function = $attribs['itemDisplay'];

            if (is_callable($function)) {
                $result = call_user_func($function, $result);

            } elseif (is_object($function)) {

                if (($function instanceof \MUtil\Html\ElementInterface)
                    || method_exists($function, 'append')) {

                    $object = clone $function;

                    $result = $object->append($result);
                }

            } elseif (is_string($function)) {
                // Assume it is a html tag when a string

                $result = \MUtil\Html::create($function, $result);
            }
        }

        if ($result instanceof \MUtil\Html\HtmlInterface) {
            $escape = false;    // Html should not be escaped!
            $result = $result->render($this->view);
        }

        // By all appearance not in use.
        /* if (isset($attribs['callback'])) {
            $callback = $attribs['callback'];
            $result = $callback($result);
        } */

        if ($escape) {
            $result = $this->view->escape($result);
        }

        if (isset($attribs['nohidden']) && $attribs['nohidden'] || is_array($value)) {
            return $result;
        } else {
            if ($value instanceof DateTimeInterface) {
                $value = $value->toString('Y-m-d H:i:s');
            }
            return $this->_hidden($name, $value) . $result;
        }
    }
}
