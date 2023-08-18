<?php

/**
 *
 * @package    MUtil
 * @subpackage Model_Translator
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 201e Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Model\Translator;

/**
 *
 *
 * @package    MUtil
 * @subpackage Model_Translator
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since \MUtil version 1.3
 */
class StraightTranslator extends \MUtil\Model\ModelTranslatorAbstract
{
    /**
     *
     * @param string $description A description that enables users to choose the transformer they need.
     */
    public function __construct($description = 'Straight import')
    {
        parent::__construct($description);
    }

    /**
     * Get information on the field translations
     *
     * @return array of fields sourceName => targetName
     * @throws \MUtil\Model\ModelException
     */
    public function getFieldsTranslations(): array
    {
        if (! $this->_targetModel instanceof \MUtil\Model\ModelAbstract) {
            throw new \MUtil\Model\ModelTranslateException(sprintf('Called %s without a set target model.', __FUNCTION__));
        }

        $fieldList   = array();

        foreach ($this->_targetModel->getCol('label') as $name => $label) {
            if (! ($this->_targetModel->has($name, 'column_expression') ||
                    $this->_targetModel->is($name, 'elementClass', 'Exhibitor'))) {

                $fieldList[$name] = $name;
            }
        }

        return $fieldList;
    }
}
