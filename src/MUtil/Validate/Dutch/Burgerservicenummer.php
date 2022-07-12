<?php

namespace MUtil\Validate\Dutch;

use MUtil\Validate\ElevenTest;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class Burgerservicenummer extends ElevenTest
{
    protected array|int $numberOrder = [9, 8, 7, 6, 5, 4, 3, 2, -1];

    /**
     * Description of the kind of test
     *
     * @var string
     */
    protected string $testDescription = 'burgerservicenummer';

    /**
     * Defined by \Zend_Validate_Interface
     *
     * Returns true if and only if a token has been set and the provided value
     * matches that token.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value, array $context = []): bool
    {
        if (trim($value, '*')) {
            return parent::isValid($value, $context);
        }
        return true;
    }
}

