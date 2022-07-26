<?php

namespace MUtil\Validate\Dutch;

use MUtil\Validate\ElevenTest;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.0
 */
class BankAccount extends ElevenTest
{
    protected int $numberLength = 9;

    protected array|int $numberOrder = self::ORDER_RIGHT_2_LEFT;

    /**
     * Description of the kind of test
     *
     * @var string
     */
    protected string $testDescription = 'bank account';
}

