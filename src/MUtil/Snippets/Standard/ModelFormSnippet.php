<?php

/**
 *
 * @package    MUtil
 * @subpackage Snippets
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Processes and displays a model form using the model
 * set through the $model snippet parameter.
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.4
 */
class MUtil_Snippets_Standard_ModelFormSnippet extends \MUtil_Snippets_ModelFormSnippetAbstract
{
    /**
     *
     * @var \MUtil_Model_ModelAbstract
     */
    protected $model;

    /**
     * Creates the model
     *
     * @return \MUtil_Model_ModelAbstract
     */
    protected function createModel()
    {
        return $this->model;
    }
}
