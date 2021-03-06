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
 * Displays multiple items in a model below each other in an Html table.
 *
 * To use this class either subclass or use the existing default ModelTableSnippet.
 *
 * @see ModelTableSnippet
 *
 * @package    MUtil
 * @subpackage Snippets
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.3
 */
abstract class MUtil_Snippets_ModelTableSnippetAbstract extends \MUtil_Snippets_ModelSnippetAbstract
{
    /**
     *
     * @var \MUtil_Html_Marker Class for marking text in the output
     */
    protected $_marker;

    /**
     * Url parts added to each link in the resulting table
     *
     * @var array
     */
    public $baseUrl;

    /**
     * One of the \MUtil_Model_Bridge_BridgeAbstract MODE constants
     *
     * @var int
     */
    protected $bridgeMode = \MUtil_Model_Bridge_BridgeAbstract::MODE_LAZY;

    /**
     * Sets pagination on or off.
     *
     * @var boolean
     */
    public $browse = false;

    /**
     * Optional table caption.
     *
     * @var string
     */
    public $caption;

    /**
     * An array of nested arrays, each defining the input for setMultiSort
     *
     * @var array
     */
    public $columns;

    /**
     * Content to show when there are no rows.
     *
     * Null shows '&hellip;'
     *
     * @var mixed
     */
    public $onEmpty = null;

    /**
     * When true the post parameters are removed from the request while filtering
     *
     * @var boolean Should post variables be removed from the request?
     */
    public $removePost = false;

    /**
     * When true (= default) the headers get sortable links.
     *
     * @var boolean
     */
    public $sortableLinks = true;

    /**
     * When true query only the used columns
     *
     * @var boolean
     */
    public $trackUsage = true;

    /**
     * Adds columns from the model to the bridge that creates the browse table.
     *
     * Overrule this function to add different columns to the browse table, without
     * having to recode the core table building code.
     *
     * @param \MUtil_Model_Bridge_TableBridge $bridge
     * @param \MUtil_Model_ModelAbstract $model
     * @return void
     */
    protected function addBrowseTableColumns(\MUtil_Model_Bridge_TableBridge $bridge, \MUtil_Model_ModelAbstract $model)
    {
        if ($this->columns) {
            foreach ($this->columns as $column) {
                call_user_func_array(array($bridge, 'addMultiSort'), $column);
            }
        } elseif ($this->sortableLinks) {
            foreach($model->getItemsOrdered() as $name) {
                if ($model->has($name, 'label')) {
                    $label = $model->get($name, 'label');
                    $bridge->addSortable($name, $label);
                }
            }
        } else {
            foreach($model->getItemsOrdered() as $name) {
                if ($model->has($name, 'label')) {
                    $label = $model->get($name, 'label');
                    $bridge->add($name, $label);
                }
            }
        }
    }

    /**
     * Add the paginator panel to the table.
     *
     * Only called when $this->browse is true. Overrule this function
     * to define your own method.
     *
     * @param \MUtil_Html_TableElement $table
     * $param \Zend_Paginator $paginator
     */
    protected function addPaginator(\MUtil_Html_TableElement $table, \Zend_Paginator $paginator)
    {
        $table->tfrow()->pagePanel($paginator, $this->request, array('baseUrl' => $this->baseUrl));
    }

    /**
     * Creates from the model a \MUtil_Html_TableElement that can display multiple items.
     *
     * Allows overruling
     *
     * @param \MUtil_Model_ModelAbstract $model
     * @return \MUtil_Html_TableElement
     */
    public function getBrowseTable(\MUtil_Model_ModelAbstract $model)
    {
        $bridge = $model->getBridgeFor('table');

        if ($this->caption) {
            $bridge->caption($this->caption);
        }
        if ($this->onEmpty) {
            $bridge->setOnEmpty($this->onEmpty);
        } else {
            $bridge->getOnEmpty()->raw('&hellip;');
        }
        if ($this->baseUrl) {
            $bridge->setBaseUrl($this->baseUrl);
        }

        $this->addBrowseTableColumns($bridge, $model);

        return $bridge->getTable();
    }

    /**
     * Create the snippets content
     *
     * This is a stub function either override getHtmlOutput() or override render()
     *
     * @param \Zend_View_Abstract $view Just in case it is needed here
     * @return \MUtil_Html_HtmlInterface Something that can be rendered
     */
    public function getHtmlOutput(\Zend_View_Abstract $view)
    {
        $model = $this->getModel();

        if ($this->trackUsage) {
            $model->trackUsage();
        }
        $table = $this->getBrowseTable($model);

        if (! $table->getRepeater()) {
            if ($this->browse) {
                $paginator = $model->loadPaginator();
                $table->setRepeater($paginator);
                $this->addPaginator($table, $paginator);
            } elseif ($this->bridgeMode === \MUtil_Model_Bridge_BridgeAbstract::MODE_LAZY) {
                $table->setRepeater($model->loadRepeatable());
            } elseif ($this->bridgeMode === \MUtil_Model_Bridge_BridgeAbstract::MODE_SINGLE_ROW) {
                $table->setRepeater(array($model->loadFirst()));
            } else {
                $table->setRepeater($model->load());
            }
        }

        return $table;
    }

    /**
     * Overrule to implement snippet specific filtering and sorting.
     *
     * @param \MUtil_Model_ModelAbstract $model
     */
    protected function processFilterAndSort(\MUtil_Model_ModelAbstract $model)
    {
        parent::processFilterAndSort($model);

        // Add generic text search filter and marker
        $textKey = $model->getTextFilter();
        if ($searchText = $this->request->getParam($textKey)) {
            // \MUtil_Echo::r($textKey . '[' . $searchText . ']');
            $this->_marker = new \MUtil_Html_Marker($model->getTextSearches($searchText), 'strong', 'UTF-8');

            foreach ($model->getItemNames() as $name) {
                if ($model->get($name, 'label') && (!$model->is($name, 'no_text_search', true))) {
                    $model->set($name, 'markCallback', array($this->_marker, 'mark'));
                }
            }
        }
    }

    /**
     * Render a string that becomes part of the HtmlOutput of the view
     *
     * You should override either getHtmlOutput() or this function to generate output
     *
     * @param \Zend_View_Abstract $view
     * @return string Html output
     */
    public function render(\Zend_View_Abstract $view)
    {
        if ($this->_marker) {
            $this->_marker->setEncoding($view->getEncoding());
        }

        return parent::render($view);
    }
}
