<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Validate
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Validate\Date;

use \MUtil\Model;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
abstract class DateAbstract extends \Zend_Validate_Abstract
        implements \MUtil\Validate\Date\FormatInterface
{
    // Always use accossor functions, never reference these vars straight
    private $_dateFormat;

    public function __construct($dateFormat = null)
    {
        if (null !== $dateFormat) {
            $this->setDateFormat($dateFormat);
        }
    }

    public function getDateFormat()
    {
        if (! $this->_dateFormat) {
            $this->setDateFormat(Model::getTypeDefault(Model::TYPE_DATE, 'dateFormat'));
        }

        return $this->_dateFormat;
    }

    public function setDateFormat($dateFormat)
    {
        $this->_dateFormat = $dateFormat;
        return $this;
    }
}
