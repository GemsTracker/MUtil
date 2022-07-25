<?php

/**
 *
 * @package    MUtil
 * @subpackage JQuery
 * @author     Jasper van Gestel <jappie@dse.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\JQuery\View\Helper\JQuery;

use MUtil\Javascript;

/**
 *
 * @package    MUtil
 * @subpackage JQuery
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.6.5
 */
class Container extends \ZendX_JQuery_View_Helper_JQuery_Container
{
    /**
     * Render jQuery stylesheets
     *
     * @return string
     */
    public function renderStylesheets()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return $this->_renderStylesheets();
    }

    /**
     * Renders all javascript file related stuff of the jQuery enviroment.
     *
     * @return string
     */
    public function renderScriptTags()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return $this->_renderScriptTags();
    }

    /**
     * Renders all javascript code related stuff of the jQuery enviroment.
     *
     * @return string
     */
    public function renderExtras()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        return $this->_renderExtras();
    }

    /**
     * String representation of jQuery javascript files and code
     * @return string
     */
    public function renderJavascript()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        $html  = $this->_renderScriptTags() . PHP_EOL
               . $this->_renderExtras();
        return $html;
    }

    /**
     * Renders all javascript code related stuff of the jQuery enviroment.
     *
     * @return string
     */
    protected function _renderExtras()
    {
        $onLoadActions = array();
        if( ($this->getRenderMode() & \ZendX_JQuery::RENDER_JQUERY_ON_LOAD) > 0) {
            foreach ($this->getOnLoadActions() as $callback) {
                $onLoadActions[] = $callback;
            }
        }

        $javascript = '';
        if( ($this->getRenderMode() & \ZendX_JQuery::RENDER_JAVASCRIPT) > 0) {
            $javascript = implode("\n    ", $this->getJavascript());
        }

        $content = '';

        if (!empty($onLoadActions)) {
            if(true === \ZendX_JQuery_View_Helper_JQuery::getNoConflictMode()) {
                $content .= '$j(document).ready(function() {'."\n    ";
            } else {
                $content .= '$(document).ready(function() {'."\n    ";
            }
            $content .= implode("\n    ", $onLoadActions) . "\n";
            $content .= '});'."\n";
        }

        if (!empty($javascript)) {
            $content .= $javascript . "\n";
        }

        if (preg_match('/^\s*$/s', $content)) {
            return '';
        }

        $nonceAttribute = Javascript::getNonceAttributeString();

        $html = '<script type="text/javascript"'.$nonceAttribute.'>' . PHP_EOL
            . (($this->_isXhtml) ? '//<![CDATA[' : '//<!--') . PHP_EOL
            . $content
            . (($this->_isXhtml) ? '//]]>' : '//-->') . PHP_EOL
            . PHP_EOL . '</script>';
        return $html;
    }

    /**
     * Renders all javascript file related stuff of the jQuery enviroment.
     *
     * @return string
     */
    protected function _renderScriptTags()
    {
        $scriptTags = '';
        if( ($this->getRenderMode() & \ZendX_JQuery::RENDER_LIBRARY) > 0) {
            $source = $this->_getJQueryLibraryPath();

            $nonceAttribute = Javascript::getNonceAttributeString();

            $scriptTags .= '<script type="text/javascript" src="' . $source . '"'.$nonceAttribute.'></script>' . PHP_EOL;

            if($this->uiIsEnabled()) {
                $uiPath = $this->_getJQueryUiLibraryPath();
                $scriptTags .= '<script type="text/javascript" src="'.$uiPath.'"'.$nonceAttribute.'></script>' . PHP_EOL;
            }

            if(ZendX_JQuery_View_Helper_JQuery::getNoConflictMode() == true) {
                $scriptTags .= '<script type="text/javascript"'.$nonceAttribute.'>var $j = jQuery.noConflict();</script>' . PHP_EOL;
            }

            foreach($this->getJavascriptFiles() AS $javascriptFile) {
                $scriptTags .= '<script type="text/javascript" src="' . $javascriptFile . '"'.$nonceAttribute.'></script>' . PHP_EOL;
            }
        }

        return $scriptTags;
    }
}
