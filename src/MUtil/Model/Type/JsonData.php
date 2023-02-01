<?php

/**
 *
 * @package    MUtil
 * @subpackage Model_Type
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Type;

use MUtil\Model\ModelAbstract;
use Zalt\Html\ElementInterface;
use Zalt\Html\Html;
use Zalt\Html\HtmlElement;
use Zalt\Html\HtmlInterface;
use Zalt\Html\Sequence;
use Zalt\Html\TableElement;

/**
 *
 * @package    MUtil
 * @subpackage Model_Type
 * @copyright  Copyright (c) 2015 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.7.1 16-apr-2015 15:30:45
 */
class JsonData
{
    /**
     * Maximum number of items in table display
     * @var int
     */
    private int $_maxTable = 3;

    /**
     * Show there are more items
     *
     * @var string
     */
    private string $_more = '...';

    /**
     * The separator for the table items
     *
     * @var string
     */
    private string $_separator;

    /**
     *
     * @param int $maxTable Max number of rows to display in table display
     * @param string $separator Separator in table display
     * @param string $more There is more in table display
     */
    public function __construct(int $maxTable = 3, string $separator = '<br />', string $more = '...')
    {
        $this->_maxTable  = $maxTable;
        $this->_more      = $more;
        $this->_separator = $separator;
    }

    /**
     * Use this function for a default application of this type to the model
     *
     * @param \MUtil\Model\ModelAbstract $model
     * @param string $name The field to set the seperator character
     * @param boolean $detailed When true show detailed information
     */
    public function apply(ModelAbstract $model, string $name, bool $detailed): void
    {
        if ($detailed) {
            $formatFunction = 'formatDetailed';
        } else {
            $formatFunction = 'formatTable';
        }
        $model->set($name, 'formatFunction', [$this, $formatFunction]);
        $model->setOnLoad($name, [$this, 'loadValue']);
        $model->setOnSave($name, [$this, 'saveValue']);
    }

    /**
     * Displays the content
     *
     * @param mixed $value
     * @return string|ElementInterface
     */
    public function formatDetailed(mixed $value): string|ElementInterface
    {
        if ((null === $value) || is_scalar($value)) {
            return $value;
        }
        if (! is_array($value)) {
                return TableElement::createArray($value)
                        ->appendAttrib('class', 'jsonNestedObject');
        }
        foreach ($value as $key => $val) {
            if (! (is_int($key) && (is_scalar($val) || ($val instanceof HtmlInterface)))) {
                return TableElement::createArray($value)
                        ->appendAttrib('class', 'jsonNestedArray');
            }
        }
        return Html::create('ul', $value, ['class' => 'jsonArrayList']);
    }

    /**
     * Displays the content
     *
     * @param mixed $value
     * @return string|ElementInterface
     */
    public function formatTable(mixed $value): string|ElementInterface
    {
        if ((null === $value) || is_scalar($value)) {
            return $value;
        }
        if (is_array($value)) {
            $i = 0;
            $output = new Sequence();
            $output->setGlue($this->_separator);
            foreach ($value as $val) {
                if ($i++ > $this->_maxTable) {
                    $output->append($this->_more);
                    break;
                }
                $output->append($val);
            }
            return $output;
        }
        return TableElement::createArray($value);
    }

    /**
     * A ModelAbstract->setOnLoad() function that concatenates the
     * value if it is an array.
     *
     * @see \MUtil\Model\ModelAbstract
     *
     * @param mixed $value The value being saved
     * @param boolean $isNew True when a new item is being saved
     * @param string $name The name of the current field
     * @param array $context Optional, the other values being saved
     * @param boolean $isPost True when passing on post data
     * @return array Of the values
     */
    public function loadValue(mixed $value, bool $isNew = false, ?string $name = null, array $context = [], bool $isPost = false): ?array
    {
        if ($value === null) {
            return null;
        }
        return json_decode($value, true);
    }

    /**
     * A ModelAbstract->setOnSave() function that concatenates the
     * value if it is an array.
     *
     * @see \MUtil\Model\ModelAbstract
     *
     * @param mixed $value The value being saved
     * @param boolean $isNew True when a new item is being saved
     * @param string $name The name of the current field
     * @param array $context Optional, the other values being saved
     * @return string Of the values concatenated
     */
    public function saveValue(mixed $value, bool$isNew = false, ?string $name = null, array $context = []): ?string
    {
        if ($value === null) {
            return null;
        }
        return json_encode($value);
    }
}
