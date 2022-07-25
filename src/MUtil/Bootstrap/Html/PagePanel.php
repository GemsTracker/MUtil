<?php

/**
 *
 *
 * @package    MUtil
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Bootstrap\Html;

/**
 * Html Element used to display paginator page links and links to increase or decrease
 * the number of items shown.
 *
 * Includes functions for specirfying your own text and separators.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class PagePanel extends \MUtil\Html\PagePanel
{

    protected $range = false;

    /**
     * Returns an element with a conditional tagName: it will become either an A or a SPAN
     * element.
     *
     * @param \MUtil\Lazy $condition Condition for link display
     * @param int $page    Page number of this link
     * @param array $args  Content of the page
     * @return \MUtil\Html\HtmlElement
     */
    public function createPageLink($condition, $page, array $args)
    {
        $element = new \MUtil\Html\HtmlElement(
                \MUtil\Lazy::iff($condition, 'a', 'span'),
                array('href' => \MUtil\Lazy::iff($condition, $this->_createHref($this->_currentPageParam, $page))),
                $this->_applyDefaults($condition, $args)
                );
        $conditionLiClass = 'disabled';
        if ($this->range) {
            $conditionLiClass = 'active';
        }
        $li = \MUtil\Html::create()->li(array('class' => \MUtil\Lazy::iff($condition, '', $conditionLiClass)));
        $li[] = $element;
        return $li;
    }
    

    /**
     * Returns a sequence of frist, previous, range, next and last conditional links.
     *
     * The condition is them being valid links, otherwise they are returned as span
     * elements.
     *
     * Note: This sequence is not added automatically to this object, you will have to
     * position it manually.
     *
     * @param string $first Label for goto first page link
     * @param string $previous Label for goto previous page link
     * @param string $next Label for goto next page link
     * @param string $last Label for goto last page link
     * @param string $glue In between links glue
     * @param mixed $args \MUtil\Ra::args extra arguments applied to all links
     * @return \MUtil\Html\Sequence
     */
    public function pageLinks($first = '<<', $previous = '<', $next = '>', $last = '>>', $glue = ' ', $args = null)
    {
        $argDefaults = array('first' => '<<', 'previous' => '<', 'next' => '>', 'last' => '>>', 'glue' => ' ');
        $argNames    = array_keys($argDefaults);

        $args = \MUtil\Ra::args(func_get_args(), $argNames, $argDefaults);

        foreach ($argNames as $name) {
            $$name = $args[$name];
            unset($args[$name]);
        }

        $container = \MUtil\Html::create()->ul(array('class' => 'pagination pagination-sm pull-left'));

        if ($first) { // Can be null or array()
            $container[] = $this->firstPage((array) $first + $args);
        }
        if ($previous) { // Can be null or array()
            $container[] = $this->previousPage((array) $previous + $args);
        }
        $this->range = true;
        $container[] = $this->rangePages('', $args);
        $this->range = false;
        if ($next) { // Can be null or array()
            $container[] = $this->nextPage((array) $next + $args);
        }
        if ($last) { // Can be null or array()
            $container[] = $this->lastPage((array) $last + $args);
        }

        return \MUtil\Lazy::iff(\MUtil\Lazy::comp($this->pages->pageCount, '>', 1), $container);
    }
}
