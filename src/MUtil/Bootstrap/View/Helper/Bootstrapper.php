<?php

/**
 *
 * @package    MUtil
 * @subpackage Bootstrap
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Bootstrap\View\Helper;

use MUtil\Javascript;

/**
 *
 * @package    MUtil
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2014 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.5
 */
class Bootstrapper
{
    protected $_bootstrapScriptPath;

    protected $_bootstrapStylePath;

    protected $_fontawesomeStylePath;

    /**
     * Load CDN Path from SSL or Non-SSL?
     *
     * @var boolean
     */
    protected $_loadSslCdnPath = false;

    /**
     * Default CDN jQuery Library version
     *
     * @var String
     */
    protected $_version = \MUtil\Bootstrap::DEFAULT_BOOTSTRAP_VERSION;
    protected $_fontawesomeVersion = \MUtil\Bootstrap::DEFAULT_FONTAWESOME_VERSION;

    /**
     * View Instance
     *
     * @var \Zend_View_Interface
     */
    public $view = null;

    protected function _getBootstrapCdnPath()
    {
        $protocol = \Zend_Controller_Front::getInstance()->getRequest()->isSecure() ? 'https:' : 'http:';
        return $protocol . \MUtil\Bootstrap::CDN_BASE;
    }

    /**
     * Internal function that constructs the include path of the jQuery library.
     *
     * @return string
     */
    protected function _getBootstrapScriptPath()
    {
        if($this->_bootstrapScriptPath != null) {
            $source = $this->_bootstrapScriptPath;
        } else {
            $baseUri = $this->_getBootstrapCdnPath();
            $source  = $baseUri
                     . $this->getVersion()
                     . \MUtil\Bootstrap::CDN_JS;
        }

        return $source;
    }

    protected function _getFontAwesomeCdnPath()
    {
        $protocol = \Zend_Controller_Front::getInstance()->getRequest()->isSecure() ? 'https:' : 'http:';
        return $protocol . \MUtil\Bootstrap::CDN_FONTAWESOME_BASE;
    }

    /**
     * Internal function that constructs the include path of the jQuery library.
     *
     * @return string
     */
    protected function _getStylesheet()
    {
        if($this->_bootstrapStylePath != null) {
            $source = $this->_bootstrapStylePath;
        } else {
            $baseUri = $this->_getBootstrapCdnPath();
            $source  = $baseUri
                     . $this->getVersion()
                     . \MUtil\Bootstrap::CDN_CSS;
        }

        return $source;
    }

    protected function _getFontAwesomeStylesheet()
    {
        if($this->_fontawesomeStylePath != null) {
            $source = $this->_fontawesomeStylePath;
        } else {
            $baseUri = $this->_getFontAwesomeCdnPath();
            $source  = $baseUri
                     . $this->getFontAwesomeVersion()
                     . \MUtil\Bootstrap::CDN_FONTAWESOME_CSS;
        }

        return $source;
    }

    public function getVersion()
    {
        return $this->_version;
    }

    public function getFontAwesomeVersion()
    {
        return $this->_fontawesomeVersion;
    }

    /**
     * Renders all javascript file related stuff of the jQuery enviroment.
     *
     * @return string
     */
    public function renderJavascript()
    {
        $nonceAttribute = Javascript::getNonceAttributeString();

        $source = $this->_getBootstrapScriptPath();
        $scriptTags = '<script type="text/javascript" src="' . $source . '"'.$nonceAttribute.'></script>' . PHP_EOL;

        return $scriptTags;
    }

    /**
     * Render Bootstrap stylesheet(s)
     *
     * @return string
     */
    public function renderStylesheets()
    {
        $stylesheet = $this->_getStylesheet();

        if ($this->view instanceof \Zend_View_Abstract) {
            $closingBracket = ($this->view->doctype()->isXhtml()) ? ' />' : '>';
        } else {
            $closingBracket = ' />';
        }
        // disable the stylesheat loader for bootstrap now that it gets compiled with the style
        $style = ''; //'<link rel="stylesheet" href="'.$stylesheet.'" type="text/css" media="screen"' . $closingBracket . PHP_EOL;

        if (\MUtil\Bootstrap::$fontawesome === true) {
            $fontawesomeStylesheet = $this->_getFontAwesomeStylesheet();

            $style .= '<link rel="stylesheet" href="'.$fontawesomeStylesheet.'" type="text/css"' . $closingBracket . PHP_EOL;
        }

        return $style;
    }

    /**
     * Sets the (local) Script path to overwrite CDN loading
     * @param string path
     */
    public function setBootstrapScriptPath($path)
    {
        $this->_bootstrapScriptPath = $path;
    }

    /**
     * Sets the (local) Stylesheet path to overwrite CDN loading
     * @param string path
     */
    public function setBootstrapStylePath($path)
    {
        $this->_bootstrapStylePath = $path;
    }

    /**
     * Set Use SSL on CDN Flag
     *
     * @param bool $flag
     * @return \MUtil\Bootstrap\View\Helper\Bootstrapper (continuation pattern)
     */
    public function setCdnSsl($flag)
    {
        $this->_loadSslCdnPath = (boolean) $flag;
        return $this;
    }
    
    /**
     * Sets the (local) Font Awesome Stylesheet path to overwrite CDN loading
     * @param string path
     */
    public function setFontAwesomeStylePath($path)
    {
        $this->_fontawesomeStylePath = $path;
    }

    /**
     * Set view object
     *
     * @param  \Zend_View_Interface $view
     * @return \MUtil\Bootstrap\View\Helper\Bootstrapper (continuation pattern)
     */
    public function setView(\Zend_View_Interface $view)
    {
        $this->view = $view;
        /*$doctype = $this->_view->doctype();

        if ($doctype instanceof \Zend_View_Helper_Doctype) {
            if (! $doctype->isHtml5()) {
                if ($doctype->isXhtml()) {
                    $doctype->setDoctype(\Zend_View_Helper_Doctype::XHTML5);
                } else {
                    $doctype->setDoctype(\Zend_View_Helper_Doctype::HTML5);
                }
            }
        }*/

        return $this;
    }
}