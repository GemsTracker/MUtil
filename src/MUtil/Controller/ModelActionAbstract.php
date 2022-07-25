<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Controller
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Controller;

/**
 * Extends Action with code for working with models.
 *
 * @see \MUtil\Model\ModelAbstract
 *
 * @package    MUtil
 * @subpackage Controller
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
abstract class ModelActionAbstract extends \MUtil\Controller\Action
{
    /**
     *
     * @var boolean $includeNumericFilters When true numeric filter keys (0, 1, 2...) are added to the filter as well
     */
    public bool $includeNumericFilters = false;

    /**
     * Array of the actions that use a summarized version of the model.
     *
     * This determines the value of $detailed in createAction(). As it is usually
     * less of a problem to use a $detailed model with an action that should use
     * a summarized model and I guess there will usually be more detailed actions
     * than summarized ones it seems less work to specify these.
     *
     * @var array $summarizedActions Array of the actions that use a
     * summarized version of the model.
     */
    public array $summarizedActions = [];


    /**
     * Set to true in so $this->html is created at startup.
     *
     * @var boolean $useHtmlView true
     */
    public bool $useHtmlView = true;  // Overrule parent


    /**
     * Created in createModel().
     *
     * Always retrieve using $this->getModel().
     *
     * $var \MUtil\Model\ModelAbstract $_model The model in use
     */
    private ?\MUtil\Model\ModelAbstract $_model;

    /**
     * The request ID value
     *
     * @return string The request ID value
     */
    protected function _getIdParam(): string
    {
        return $this->request->getAttribute(\MUtil\Model::REQUEST_ID);
    }

    /**
     * Adds columns from the model to the bridge that creates the browse table.
     *
     * Overrule this function to add different columns to the browse table, without
     * having to recode the core table building code.
     *
     * @param \MUtil\Model\Bridge\TableBridge $bridge
     * @param \MUtil\Model\ModelAbstract $model
     * @return void
     */
    protected function addBrowseTableColumns(\MUtil\Model\Bridge\TableBridge $bridge, \MUtil\Model\ModelAbstract $model): void
    {
        foreach($model->getItemsOrdered() as $name) {
            if ($label = $model->get($name, 'label')) {
                $bridge->addSortable($name, $label);
            }
        }
    }


    /**
     * Adds elements from the model to the bridge that creates the form.
     *
     * Overrule this function to add different elements to the browse table, without
     * having to recode the core table building code.
     *
     * @param \MUtil\Model\Bridge\FormBridgeInterface $bridge
     * @param \MUtil\Model\ModelAbstract $model
     * @param array $data The data that will later be loaded into the form
     * @param boolean $new Form should be for a new element
     * @return void When an array of new values is return, these are used to update the $data array in the calling function
     */
    protected function addFormElements(\MUtil\Model\Bridge\FormBridgeInterface $bridge, \MUtil\Model\ModelAbstract $model, array $data, $new = false): void
    {
        foreach($model->getItemsOrdered() as $name) {
            if ($model->has($name, 'label') || $model->has($name, 'elementClass')) {
                $bridge->add($name);
            } else {
                $bridge->addHidden($name);
            }
        }
    }


    /**
     * Creates a model for getModel(). Called only for each new $action.
     *
     * The parameters allow you to easily adapt the model to the current action. The $detailed
     * parameter was added, because the most common use of action is a split between detailed
     * and summarized actions.
     *
     * @param boolean $detailed True when the current action is not in $summarizedActions.
     * @param string $action The current action.
     * @return \MUtil\Model\ModelAbstract
     */
    abstract protected function createModel(bool $detailed, string $action): \MUtil\Model\ModelAbstract;


    /**
     * Creates an empty form. Allows overruling in sub-classes.
     *
     * @param mixed $options
     * @return \Zend_Form
     */
    protected function createForm(mixed $options = null): \Zend_Form
    {
        $form = new \Zend_Form($options);

        return $form;
    }


    /**
     * Creates from the model a \MUtil\Html\TableElement that can display multiple items.
     *
     * @param array $baseUrl
     * @param mixed $sort A valid sort for \MUtil\Model\ModelAbstract->load()
     * @return \MUtil\Html\TableElement
     */
    public function getBrowseTable(array $baseUrl = null, $sort = null, $model = null)
    {
        if (empty($model)) {
            $model  = $this->getModel();
        }

        $bridge = $model->getBridgeFor('table');
        $bridge->getOnEmpty()->raw('&hellip;');
        if ($baseUrl) {
            $bridge->setBaseUrl($baseUrl);
        }

        $this->addBrowseTableColumns($bridge, $model);

        return $bridge->getTable();
    }

    /**
     * Returns the model for the current $action.
     *
     * The parameters allow you to easily adapt the model to the current action. The $detailed
     * parameter was added, because the most common use of action is a split between detailed
     * and summarized actions.
     *
     * @return \MUtil\Model\ModelAbstract
     */
    protected function getModel(): \MUtil\Model\ModelAbstract
    {
        $action = null;
        if ($this->request instanceof \Psr\Http\Message\ServerRequestInterface) {
            $action = $this->requestHelper->getActionName();
        }

        // Only get new model if there is no model or the model was for a different action
        if (! ($this->_model && $this->_model->isMeta('action', $action))) {
            $detailed = ! in_array($action, $this->summarizedActions);

            $this->_model = $this->createModel($detailed, $action);
            $this->_model->setMeta('action', $action);

            // Detailed models DO NOT USE $_POST for filtering,
            // multirow models DO USE $_POST parameters for filtering.
            $parameters = $this->request->getQueryParams();
            if (!$detailed) {
                $parameters += $this->request->getParsedBody();
            }

            // Remove all empty values (but not arrays)
            $parameters = array_filter($parameters, function($i) {
                return is_array($i) || strlen($i);
            });

            $this->_model->applyParameters($parameters, $this->includeNumericFilters);
        }

        return $this->_model;
    }


    /**
     * Creates from the model a \Zend_Form using createForm and adds elements
     * using addFormElements().
     *
     * @param array $data The data that will later be loaded into the form, can be changed
     * @param boolean $new Form should be for a new element
     * @return \Zend_Form
     */
    public function getModelForm(array &$data, bool $new = false)
    {
        $model = $this->getModel();

        $bridge = $model->getBridgeFor('form', $this->createForm());

        $this->addFormElements($bridge, $model, $data, $new);

        return $bridge->getForm();
    }

    /**
     * Creates from the model a \MUtil\Html\TableElement for display of a single item.
     *
     * It can and will display multiple items, but that is not what this function is for.
     *
     * @param integer $columns The number of columns to use for presentation
     * @return \MUtil\Html\TableElement
     */
    public function getShowTable(int $columns = 1): \MUtil\Html\TableElement
    {
        $model = $this->getModel();

        $bridge = $model->getBridgeFor('itemTable');
        $bridge->setColumnCount($columns);

        foreach($model->getItemsOrdered() as $name) {
            if ($label = $model->get($name, 'label')) {
                $bridge->addItem($name, $label);
            }
        }

        return $bridge->getTable();
    }


    /**
     * Helper function to determine the ability for the user to create new items
     *
     * return boolean True if the user can add new items
     */
    public function hasNew(): bool
    {
        $model = $this->getModel();

        return $model && $model->hasNew();
    }
}
