<?php

namespace MUtil\Validate;

use Laminas\Validator\AbstractValidator;

/**
 *
 * @package    MUtil
 * @subpackage Validate
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class ElevenTest extends AbstractValidator
{
    /**
     * Constant for setting 1 as the first number and then incrementing each next number as weight
     */
    public const ORDER_LEFT_2_RIGHT = 1;

    /**
     * Constant for setting 1 as the last number and then incrementing each previous number as weight
     */
    public const ORDER_RIGHT_2_LEFT = 2;

    /**
     * Error codes
     * @const string
     */
    public const NOT_CHECK   = 'notCheck';
    public const NOT_NUMBER  = 'notNumber';
    public const TOO_LONG     = 'tooLong';
    public const TOO_SHORT    = 'tooShort';

    /**
     * Templates for different error message types
     *
     * @var array
     */
    protected array$messageTemplates = [
        self::NOT_CHECK  => "This is not a valid %testDescription%.",
        self::NOT_NUMBER => "A %testDescription% cannot contain letters.",
        self::TOO_LONG    => "%value% is too long for a %testDescription%. Should be %length% digits.",
        self::TOO_SHORT   => "%value% is too short for a %testDescription%. Should be %length% digits.",
    ];

    /**
     * @var array
     */
    protected array $messageVariables = [
        'testDescription' => 'testDescription',
        'length' => 'length'
    ];

    /**
     * The length of the number (when not 0).
     *
     * Not used when $_numberOrder is an array
     *
     * @var int
     */
    protected int $numberLength = 0;

    /**
     * The allowed lenght of the number, set by the isvalid function to be used in message templates
     */
    protected int $length = 0;

    /**
     * Decides the weight addressed to each number
     *
     * Set to array to specify weight value for each position.
     *
     * @var array|int ORDER_LEFT_2_RIGHT|ORDER_RIGHT_2_LEFT
     */
    protected array|int $numberOrder = self::ORDER_LEFT_2_RIGHT;

    /**
     * Description of the kind of test
     *
     * @var string
     */
    protected string $testDescription = 'input number';

    /**
     *
     * @param string $testDescription Description of data used in the error message
     */
    public function __construct($testDescription = null)
    {
        parent::__construct();
        if ($testDescription) {
            $this->setTestDescription($testDescription);
        }
    }

    /**
     * Calculate the weights with whom each number position in the input
     * should be multiplied for the test.
     *
     * @param int $valueLength
     * @return array
     */
    protected function getCalculateWeights($valueLength)
    {
        $order = $this->getNumberOrder();

        if (is_array($order)) {
            return $order;
        }

        $length = $this->getNumberLength();
        if ($length < 1) {
            $length = $valueLength;
        }

        $newOrder = range(1, $length);

        if ($order == self::ORDER_RIGHT_2_LEFT) {
            return array_reverse($newOrder);
        } else {
            return $newOrder;
        }
    }

    /**
     * The length of the number (when not 0).
     *
     * Not used when $_numberOrder is an array
     *
     * @return int
     */
    public function getNumberLength(): int
    {
        return $this->numberLength;
    }

    /**
     * Decides the weight addressed to each number
     *
     * Set to array to specify weight value for each position.
     *
     * @return array|int ORDER_LEFT_2_RIGHT|ORDER_RIGHT_2_LEFT
     */
    public function getNumberOrder(): array|int
    {
        return $this->numberOrder;
    }

    /**
     * Get description of the kind of test, used in the error message
     *
     * @return String
     */
    public function getTestDescription()
    {
        return $this->testDescription;
    }

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
        $this->setValue((string) $value);

        if ($value === null) {
            $this->error(self::NOT_NUMBER);
            return false;
        }

        // Remove non letter characters like . _ - \s
        $value = preg_replace('/[\W_]/', '', $value);

        // Make sure it is a number
        if (preg_match('/[\D]/', $value)) {
            $this->error(self::NOT_NUMBER);
            return false;
        }

        $weights = $this->getCalculateWeights(strlen($value));
        $count   = count($weights);

        //Set the length for the message template
        $this->_length = $count;
        // \MUtil\EchoOut\EchoOut::rs($value, $weights);

        // Simple length checks
        if ($count != strlen($value)) {
            if ($count < strlen($value)) {
                $this->error(self::TOO_LONG);
                return false;
            } else {
                $this->error(self::TOO_SHORT);
                return false;
            }
        }

        // The actual calculation
        $sum = 0;
        for ($i = 0; $i < $count; $i++) {
            $sum += ($value[$i] * $weights[$i]);
        }
        // The actual test
        if ($sum % 11) {
            $this->error(self::NOT_CHECK);
            return false;
        }

        return true;
    }

    /**
     * The length of the number (when not 0).
     *
     * Not used when $_numberOrder is an array
     *
     * @param int $numberLength
     * @return self
     */
    public function setNumberLength(int $numberLength): self
    {
        $this->numberLength = $numberLength;
        return $this;
    }

    /**
     * Decides the weight addressed to each number
     *
     * Set to array to specify weight value for each position.
     *
     * @param array|int $numberOrder ORDER_LEFT_2_RIGHT|ORDER_RIGHT_2_LEFT
     * @return self
     */
    public function setNumberOrder(array|int $numberOrder): self
    {
        $this->numberOrder = $numberOrder;
        return $this;
    }

    /**
     * Set description of the kind of test
     *
     * @param string $description
     * @return $this
     */
    public function setTestDescription($description): self
    {
        $this->testDescription = $description;
        return $this;
    }
}
