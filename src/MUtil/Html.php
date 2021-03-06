<?php

/**
 *
 * @package    MUtil
 * @subpackage Html
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

/**
 * Collections of static function for using the Html subpackage.
 *
 * @package    MUtil
 * @subpackage Html
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
class MUtil_Html
{
    /**
     *
     * @var \MUtil_Html_Creator
     */
    private static $_creator;

    /**
     *
     * @var \MUtil_Html_Renderer
     */
    private static $_renderer;

    /**
     *
     * @var \MUtil_Snippets_SnippetLoader
     */
    private static $_snippetLoader;

    /**
     * Static variable for debuggging purposes. Toggles the echoing of e.g. of sql
     * select statements, using \MUtil_Echo.
     *
     * Implemention classes can use this variable to determine whether to display
     * extra debugging information or not. Please be considerate in what you display:
     * be as succint as possible.
     *
     * Use:
     *     \MUtil_Html::$verbose = true;
     * to enable.
     *
     * @var boolean $verbose If true echo retrieval statements.
     */
    public static $verbose = false;

    /**
     * @deprecated
     * @param \Zend_Navigation_Container $menu
     * @param string $label
     * @param array $arg_array
     */
    public static function addUrl2Page(\Zend_Navigation_Container $menu, $label, $arg_array = null)
    {
        $args = array_slice(func_get_args(), 2);
        $menu->addPage(self::url($args)->toPage($label));
    }

    public static function attrib($attributeName, $args_array = null)
    {
        $args = \MUtil_Ra::args(func_get_args(), 1);

        return self::getCreator()->createAttribute($attributeName, $args);
    }

    /**
     * Render a BB string to Html
     *
     * @param string $content
     * @return \MUtil_Html_Raw
     */
    public static function bbcode($content)
    {
        return self::getCreator()->create('raw', [\MUtil_Lazy::call('\MUtil_Markup::render', $content, 'Bbcode', 'Html')]);
    }

    /**
     * A br element
     *
     * @return \MUtil_Html_HtmlElement
     */
    public static function br()
    {
        return new \MUtil_Html_HtmlElement('br');
    }

    /**
     * Check if the value can be rendered by the default renderer
     *
     * @param mixed $value
     * @return boolean True when the object can be rendered
     */
    public static function canRender($value)
    {
        return self::getRenderer()->canRender($value);
    }

    /**
     * Create an element or return an element creator
     *
     * @param string $tagName Optional tag to create
     * @param mixed $arg_array Optional \MUtil_Ra::args processed settings
     * @return \MUtil_Html_HtmlElement or \MUtil_Html_Creator
     */
    public static function create($tagName = null, $arg_array = null)
    {
        if (null == $tagName) {
            return self::getCreator();
        }

        $args = array_slice(func_get_args(), 1);

        return self::getCreator()->create($tagName, $args);
    }

    public static function createAttribute($attributeName, array $args = array())
    {
        return self::getCreator()->createAttribute($attributeName, $args);
    }

    /**
     * Creates a new HtmlElement with the arguments specfied in a single array.
     *
     * @param string $tagName (or a Lazy object)
     * @param array $args
     * @return \MUtil_Html_ElementInterface
     */
    public static function createArray($tagName, array $args = array())
    {
        return self::getCreator()->create($tagName, $args);
    }

    /**
     * Create an element bypassing the standard element creation function stored for certain tags.
     *
     * @param string $tagName Optional tag to create
     * @param mixed $arg_array Optional \MUtil_Ra::args processed settings
     * @return \MUtil_Html_HtmlElement Always, never another type
     */
    public static function createRaw($tagName, array $args = array())
    {
        return self::getCreator()->createRaw($tagName, $args);
    }

    /**
     * Creates a div element
     *
     * @param mixed $arg_array Optional \MUtil_Ra::args processed settings
     * @return \MUtil_Html_HtmlElement (with div tagName)
     */
    public static function div($arg_array = null)
    {
        $args = func_get_args();

        return self::getCreator()->create('div', $args);
    }

    public static function element2id(\Zend_Form_Element $element)
    {
        return self::name2id($element->getName(), $element->getBelongsTo());
    }

    /**
     * Helper function to access the core creator.
     *
     * @return \MUtil_Html_Creator
     */
    public static function getCreator()
    {
        if (! self::$_creator) {
            self::$_creator = new \MUtil_Html_Creator();
        }

        return self::$_creator;
    }

    /**
     * Returns the class used to perform the actual rendering
     * of objects and items into html.
     *
     * @return \MUtil_Html_Renderer
     */
    public static function getRenderer()
    {
        if (! self::$_renderer) {
            self::$_renderer = new \MUtil_Html_Renderer();
        }

        return self::$_renderer;
    }

    /**
     * Get the snippet loader for use by self::snippet().
     *
     * @return \MUtil_Snippets_SnippetLoader
     */
    public static function getSnippetLoader()
    {
        if (! self::$_snippetLoader) {
            self::setSnippetLoader(new \MUtil_Snippets_SnippetLoader());
        }
        return self::$_snippetLoader;
    }

    /**
     * Replaces the non html name characters in the name.
     *
     * Helper function for working with \Zend_Form_Element's
     *
     * @param string $name
     * @param string $belongsTo
     * @return string
     */
    public static function name2id($name, $belongsTo = null)
    {
        return preg_replace('/\[([^\]]+)\]/', '-$1', $name . '-' . $belongsTo);
    }

    /**
     * String content that should be rendered without output escaping
     *
     * @param string $content
     * @return \MUtil_Html_Raw
     */
    public static function raw($content)
    {
        return self::getCreator()->create('raw', array($content));
    }

    /**
     * Mimics strip_tags but allows to strip the content of certain tags (like script) too
     *
     * function copied from a comment http://www.php.net/manual/en/function.strip-tags.php#97386
     *
     * @param string $s             The string to strip
     * @param string $keepTags      Pipe | separated tags to keep
     * @param string $removeContent Pipe | separated tags from which contect will be stripped
     * @return string
     */
    public static function removeMarkup($s, $keepTags = '' , $removeContent = 'script|style|noframes|select|option|link')
    {
        /**///prep the string
        $s = ' ' . $s;

        /**///initialize keep tag logic
        if(strlen($keepTags) > 0){
            $k = explode('|', $keepTags);
            for($i=0;$i<count($k);$i++){
                $s = str_replace('<' . $k[$i] . ' ', '[{(' . $k[$i] . ' ', $s); // Tag name followed by space
                $s = str_replace('<' . $k[$i] . '>', '[{(' . $k[$i] . '>', $s); // Tag name followed by clossing bracket
                $s = str_replace('<' . $k[$i] . '/>', '[{(' . $k[$i] . '/>', $s); // Stand alone tag
                $s = str_replace('</' . $k[$i],'[{(/' . $k[$i],$s);
            }
        }

        //begin removal
        /**///remove comment blocks
        while(stripos($s,'<!--') > 0){
            $pos[1] = stripos($s,'<!--');
            $pos[2] = stripos($s,'-->', $pos[1]);
            $len[1] = $pos[2] - $pos[1] + 3;
            $x = substr($s,$pos[1],$len[1]);
            $s = str_replace($x,'',$s);
        }

        /**///remove tags with content between them
        if(strlen($removeContent) > 0){
            $e = explode('|', $removeContent);
            for($i=0;$i<count($e);$i++){
                while(stripos($s,'<' . $e[$i]) > 0){
                    $len[1] = strlen('<' . $e[$i]);
                    $pos[1] = stripos($s,'<' . $e[$i]);
                    $pos[2] = stripos($s,$e[$i] . '>', $pos[1] + $len[1]);
                    $len[2] = $pos[2] - $pos[1] + $len[1];
                    $x = substr($s,$pos[1],$len[2]);
                    $s = str_replace($x,'',$s);
                }
            }
        }

        /**///remove remaining tags
        $start = 0;
        while(stripos($s,'<', $start) > 0){
            $pos[1] = stripos($s,'<', $start);
            $pos[2] = stripos($s,'>', $pos[1]);
            if (!$pos[2]) {
                //No closing tag! Skip this one
                $start = $pos[1]+1;
            } else {
                $len[1] = $pos[2] - $pos[1] + 1;
                $x = substr($s,$pos[1],$len[1]);
                $s = str_replace($x,'',$s);
            }
        }

        if (strlen($keepTags) > 0) {
            /**///finalize keep tag
            for($i=0;$i<count($k);$i++){
                $s = str_replace('[{(' . $k[$i],'<' . $k[$i],$s);
                $s = str_replace('[{(/' . $k[$i],'</' . $k[$i],$s);
            }
        }

        return trim($s);
    }

    /**
     * Renders the $content so that it can be used as output for the $view,
     * including output escaping and encoding correction.
     *
     * @param \Zend_View_Abstract $view
     * @param mixed $content Anything number, string, array, Lazy, HtmlInterface, object with __toString
     * @return string Output to echo to the user
     */
    public static function renderAny(\Zend_View_Abstract $view, $content)
    {
        return self::getRenderer()->renderAny($view, $content);
    }

    public static function renderNew(\Zend_View_Abstract $view, $tagName, $arg_array = null)
    {
        $args = array_slice(func_get_args(), 2);

        $element = self::getCreator()->create($tagName, $args);

        return $element->render($view);
    }

    /**
     * Creates a table element
     *
     * @param mixed $arg_array Optional \MUtil_Ra::args processed settings
     * @return \MUtil_Html_TableElement
     */
    public static function table($arg_array = null)
    {
        $args = func_get_args();

        return self::getCreator()->create('table', $args);
    }

    public static function setCreator(\MUtil_Html_Creator $creator)
    {
        self::$_creator = $creator;
        return self::$_creator;
    }

    public static function setRenderer(\MUtil_Html_Renderer $renderer)
    {
        self::$_renderer = $renderer;
        return self::$_renderer;
    }

    /**
     * Set the snippet loader for use by self::snippet().
     *
     * @param \MUtil_Snippets_SnippetLoaderInterface $snippetLoader
     * @return \MUtil_Snippets_SnippetLoader
     */
    public static function setSnippetLoader(\MUtil_Snippets_SnippetLoaderInterface $snippetLoader)
    {
        self::$_snippetLoader = $snippetLoader;
        return self::$_snippetLoader;
    }

    /**
     *
     * @param string $name Snippet name
     * @param \MUtil_Ra::pairs $parameter_value_pairs Optional extra snippets
     * @return \MUtil_Snippets_SnippetInterface
     */
    public static function snippet($name, $parameter_value_pairs = null)
    {
        if (func_num_args() > 1) {
            $extraSourceParameters = \MUtil_Ra::pairs(func_get_args(), 1);
        } else {
            $extraSourceParameters = array();
        }

        if (is_array($name)) {
            list($names, $params) = \MUtil_Ra::keySplit($name);

            if ($params) {
                $extraSourceParameters = $params + $extraSourceParameters;
            }
            if (isset($names[0])) {
                $name = $names[0];
            } else {
                throw new \MUtil_Html_HtmlException('Missing snippet name in call to create snippet.');
            }
        }

        $loader = self::getSnippetLoader();

        $snippet = $loader->getSnippet($name, $extraSourceParameters);

        if ($snippet->hasHtmlOutput()) {
            return $snippet;
        }
    }

    /**
     * Returns a href attribute
     *
     * @deprecated
     * @param mixed $arg_array \MUtil_Args::ra arguements
     * @return \MUtil_Html_HrefArrayAttribute
     */
    public static function url($arg_array = null)
    {
        $args = func_get_args();
        return new \MUtil_Html_HrefArrayAttribute($args);
    }

    /**
     * Returns relative url string using the current module, controller and action when
     * none where specified.
     *
     * This is url is encoded for url usage, but not for use as attribute values,
     * i.e. this helper function is used for generating url's for internal use.
     *
     * @param array $options Array of parameter values
     * @param \Zend_Controller_Request_Abstract $request
     * @param \Zend_Controller_Router_Route $router
     * @return string
     */
    public static function urlString(array $options, \Zend_Controller_Request_Abstract $request = null, \Zend_Controller_Router_Route $router = null)
    {
        return \MUtil_Html_UrlArrayAttribute::toUrlString($options, $request, $router);
    }
}
