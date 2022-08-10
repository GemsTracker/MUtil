<?php

/**
 *
 * @package    MUtil
 * @subpackage Controller
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Controller;

use MUtil\Ra;
use MUtil\Translate\Translator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use MUtil\Legacy\RequestHelper;
use Mezzio\Helper\UrlHelper;
use Mezzio\Flash\FlashMessagesInterface;


/**
 * Extends \Zend_Controller_Action with basic functionality and \MUtil\Html
 *
 * Basic functionality provided:
 *  - title attribute for use in htm/head/title element
 *  - flashMessenger use standardised and simplified
 *  - use of \Zend_Translate simplified and shortened in code
 *  - disable \Zend_Layout and \Zend_View with initRawOutput() and $useRawOutput.
 *
 * \MUtil\Html functionality provided:
 *  - semi automatic \MUtil\Html\Sequence initiation
 *  - view script set to html-view.phtml when using html
 *  - snippet usage for repeatably used snippets of html on a page
 *
 * @package MUtil
 * @subpackage Controller
 * @copyright  Copyright (c) 2011 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.0
 */
abstract class Action
{
    /**
     * A session based message store.
     *
     * Standard the flash messenger for storing messages
     *
     * @var FlashMessagesInterface
     */
    protected $messenger;

    public static string $messengerKey = 'action-messages';

    /**
     * Created when $useHtmlView is true or initHtml() is run.
     *
     * Allows you to create html using e.g. $this->html->p();
     *
     * @var \MUtil\Html\Sequence $html The html object to add content to.
     */
    public $html;

    public ?string $redirectUrl = null;

    /**
     * PSR-7 Request
     *
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * Helper class for retrieving legacy data from a PSR-7 Request
     *
     * @var RequestHelper
     */
    protected RequestHelper $requestHelper;

    /**
     * The loader for snippets.
     *
     * @var \MUtil\Snippets\SnippetLoader
     */
    protected $snippetLoader;

    /**
     * The current html/head/title for this page.
     *
     * Can be a string or an array of string values.
     *
     * var string|array $title;
     */
    protected $title;

    /**
     *
     * @var Translator
     */
    public $translate;

    protected UrlHelper $urlHelper;

    /**
     * Set to true in child class for automatic creation of $this->html.
     *
     * To initiate the use of $this->html from the code call $this->initHtml()
     *
     * Overrules $useRawOutput.
     *
     * @see $useRawOutput
     * @var boolean $useHtmlView
     */
    public $useHtmlView = false;

    /**
     * Set to true in child class for automatic use of raw (e.g. echo) output only.
     *
     * Otherwise call $this->initRawOutput() to switch to raw echo output.
     *
     * Overruled in initialization if $useHtmlView is true.
     *
     * @see $useHtmlView
     * @var boolean $useRawOutput
     */
    public $useRawOutput = false;

    public function __construct(ServerRequestInterface $request, UrlHelper $urlHelper, $init = true)
    {
        $this->request = $request;
        $this->requestHelper = new RequestHelper($request);
        $this->urlHelper = $urlHelper;

        //$this->_helper = new \Zend_Controller_Action_HelperBroker($this);

        if ($init) {
            $this->init();
        }
    }

    /**
     * Translates the given message.
     *
     * When a number is provided as a parameter named "%count%", the message is parsed for plural
     * forms and a translation is chosen according to this number using the following rules:
     *
     * Given a message with different plural translations separated by a
     * pipe (|), this method returns the correct portion of the message based
     * on the given number, locale and the pluralization rules in the message
     * itself.
     *
     * The message supports two different types of pluralization rules:
     *
     * interval: {0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples
     * indexed:  There is one apple|There are %count% apples
     *
     * The indexed solution can also contain labels (e.g. one: There is one apple).
     * This is purely for making the translations more clear - it does not
     * affect the functionality.
     *
     * The two methods can also be mixed:
     *     {0} There are no apples|one: There is one apple|more: There are %count% apples
     *
     * An interval can represent a finite set of numbers:
     *  {1,2,3,4}
     *
     * An interval can represent numbers between two numbers:
     *  [1, +Inf]
     *  ]-1,2[
     *
     * The left delimiter can be [ (inclusive) or ] (exclusive).
     * The right delimiter can be [ (exclusive) or ] (inclusive).
     * Beside numbers, you can use -Inf and +Inf for the infinite.
     *
     * @see https://en.wikipedia.org/wiki/ISO_31-11
     *
     * @param string      $id         The message id (may also be an object that can be cast to string)
     * @param array       $parameters An array of parameters for the message
     * @param string|null $domain     The domain for the message or null to use the default
     * @param string|null $locale     The locale or null to use the default
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    public function _(?string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        return $this->translate->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Reroutes the page (i.e. header('Location: ');)
     *
     * @param array $urlOptions Url parts
     * @param boolean $reset Use default module, action and controller instead of current when not specified in $urlOptions
     * @param string $routeName
     * @param boolean $encode
     */
    protected function _reroute(array $urlOptions = array(), $reset = false, $routeName = null, $encode = true)
    {
        /*
         * TODO Reimplement reroute
         */
        /*if ($reset) {
            // \MUtil\EchoOut\EchoOut::r($urlOptions, 'before');
            $urlOptions = \MUtil\Html\UrlArrayAttribute::rerouteUrl($this->getRequest(), $urlOptions);
            // \MUtil\EchoOut\EchoOut::r($urlOptions, 'after');
        }
        $this->_helper->redirector->gotoRoute($urlOptions, $routeName, $reset, $encode);*/
    }

    /**
     * Adds one or more messages to the session based message store.
     *
     * @param mixed $message_args Can be an array or multiple argemuents. Each sub element is a single message string
     * @param string|null $status Optional message status, one of: success, info, warning or danger
     * @return \MUtil\Controller\Action
     */
    public function addMessage(mixed $message, string $status = 'warning')
    {
        $messenger = $this->getMessenger();
        $messages = $messenger->getFlash(static::$messengerKey, []);
        $messages[] = [$message, $status];
        $messenger->flash(static::$messengerKey, $messages);

        return $this;
    }

    /**
     * Searches and loads a .php snippet file and adds the content to $this->html.
     *
     * @param string $filename The name of the snippet
     * @param \MUtil\Ra::pairs $parameter_value_pairs name/value pairs ot add to the source for this snippet
     * @return \MUtil\Snippets\SnippetInterface The snippet if content was possibly added.
     */
    public function addSnippet(string $filename, $parameter_value_pairs = null): ?\MUtil\Snippets\SnippetInterface
    {
        $extraSource = \MUtil\Ra::pairs(func_get_args(), 1);
        $results     = $this->addSnippets([$filename], $extraSource);
        return $results ? reset($results) : null;
    }

    /**
     * Searches and loads multiple .php snippet files and adds them to this->html using the filename as
     * content key, unless that key already exists.
     *
     * @param string[]|string $filenames Names of snippets
     * @param \MUtil\Ra::pairs $parameter_value_pairs name/value pairs ot add to the source for this snippet
     * @return mixed The snippet if content was possibly added.
     */
    public function addSnippets(mixed $filenames, $parameter_value_pairs = null): ?array
    {
        if ($filenames) {
            $extraSource = Ra::pairs(func_get_args(), 1);

            if (is_string($filenames)) {
                $filenames = [$filenames];
            }

            $results  = [];
            $snippets = $this->getSnippets($filenames, $extraSource);
            foreach ($snippets as $filename => $snippet) {

                if ($response = $snippet->getResponse() instanceof ResponseInterface) {
                    return $response;
                }
                if ($snippet->hasHtmlOutput()) {
                    if (isset($this->html[$filename])) {
                        $this->html[] = $snippet;
                    } else {
                        $this->html[$filename] = $snippet;
                    }
                    $results[$filename]    = $snippet;

                } elseif ($snippet->getRedirectRoute()) {
                    $redirectParts = $snippet->getRedirectRoute();
                    if (isset($redirectParts['routeName'])) {
                        $url = $this->urlHelper->generate($redirectParts['routeName']);
                        $this->redirectUrl = $url;
                    }
                    $route = $this->requestHelper->getRoute();
                    $routeName = $route->getName();
                    $routeNameParts = explode('.', $routeName);
                    array_pop($routeNameParts);
                    $routeNameParts[] = $redirectParts['action'];
                    $newRouteName = join('.', $routeNameParts);

                    $nonParams = [
                        'controller' => true,
                        'action' => true,
                    ];
                    $params = array_diff_key($redirectParts, $nonParams);

                    $url = $this->urlHelper->generate($newRouteName, $params);
                    $this->redirectUrl = $url;
                    return null;
                }
            }

            return $results;
        }
        return null;
    }

    /**
     * Appends an extra part to the html/head/title.
     *
     * Forces $this->title to be an array.
     *
     * @param string $extraTitle
     * @return \MUtil\Controller\Action
     */
    public function appendTitle(string $extraTitle): self
    {
        if ($this->title && (! is_array($this->title))) {
            $this->title = [$this->title];
        }
        $this->title[] = $extraTitle;

        return $this;
    }

    /**
     * Disable the use of \Zend_Layout
     *
     * @return self (continuation pattern)
     */
    public function disableLayout(): self
    {
        // TODO reimplement disabling layout

        return $this;
    }

    /**
     * Returns a session based message store for adding messages to.
     *
     * @return \Mezzio\Flash\FlashMessagesInterface
     */
    public function getMessenger(): \Mezzio\Flash\FlashMessagesInterface
    {
        if (! $this->messenger) {
            $this->messenger =  $this->request->getAttribute('flash');
        }

        return $this->messenger;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirectUrl;
    }

    /**
     * Searches and loads a .php snippet file.
     *
     * @param string $filename The name of the snippet
     * @param \MUtil\Ra::pairs $parameter_value_pairs name/value pairs ot add to the source for this snippet
     * @return \MUtil\Snippets\SnippetInterface The snippet
     */
    public function getSnippet(string $filename, $parameter_value_pairs = null): \MUtil\Snippets\SnippetInterface
    {
        $extraSource = \MUtil\Ra::pairs(func_get_args(), 1);
        $results     = $this->getSnippets([$filename], $extraSource);
        return reset($results);
    }

    /**
     * Searches and loads multiple .php snippet file.
     *
     * @param string[] $filenames Array of snippet names with optionally extra parameters included
     * @param \MUtil\Ra::pairs $parameter_value_pairs name/value pairs ot add to the source for this snippet
     * @return array Of filename => \MUtil\Snippets\SnippetInterface snippets
     */
    public function getSnippets(array $filenames, $parameter_value_pairs = null): array
    {
        if (func_num_args() > 1) {
            $extraSourceParameters = \MUtil\Ra::pairs(func_get_args(), 1);
        } else {
            $extraSourceParameters = [];
        }

        list($filenames, $params) = \MUtil\Ra::keySplit($filenames);

        if ($params) {
            $extraSourceParameters = $params + $extraSourceParameters;
        }

        $results = [];

        if ($filenames) {
            $loader = $this->getSnippetLoader();

            foreach ($filenames as $filename) {
                $results[$filename] = $loader->getSnippet($filename, $extraSourceParameters);
            }
        }

        return $results;
    }

    /**
     * Returns a source of values for snippets.
     *
     * @return \MUtil\Snippets\SnippetLoader
     */
    public function getSnippetLoader()
    {
        if (! $this->snippetLoader) {
            $this->loadSnippetLoader();
        }

        return $this->snippetLoader;
    }

    /**
     * Returns the current html/head/title for this page.
     *
     * If the title is an array the seperator concatenates the parts.
     *
     * @param string $separator
     * @return string
     */
    public function getTitle(string $separator = ''): string
    {
        if (is_array($this->title)) {
            return implode($separator, $this->title);
        } else {
            return $this->title;
        }
    }

    /**
     * Returns the translator.
     *
     * Set the translator if not yet set. The default translator is
     * \Zend_Registry::get('Zend_Translate') or a Potemkin Translate adapter
     * when not set in the registry, so the code will still work, it just
     * will not translate.
     *
     * @return \Zend_Translate
     */
    public function getTranslate(): \MUtil\Translate\Translator
    {
        return $this->translate;
    }

    /**
     * Initialize translate and html objects
     *
     * Called from {@link __construct()} as final step of object instantiation.
     *
     * @return void
     */
    public function init(): void
    {
        if ($this->useHtmlView) {
            $this->initHtml();
        } elseif ($this->useRawOutput) {
            $this->initRawOutput();
        }
    }

    /**
     * Intializes the html component.
     *
     * @param boolean $reset Throws away any existing html output when true
     * @return void
     */
    public function initHtml(bool $reset = false): void
    {
        if ($reset || (! $this->html)) {
            \MUtil\Html::setSnippetLoader($this->getSnippetLoader());

            $this->html = new \MUtil\Html\Sequence();

            // Add this variable to the view.
            //$this->view->html = $this->html;

            // Load html-view.phtml from the same directory as this file.
            /*$this->view->setScriptPath(dirname(__FILE__));
            $this->_helper->viewRenderer->setNoController();
            $this->_helper->viewRenderer->setScriptAction('html-view');*/

            $this->useHtmlView  = true;
            $this->useRawOutput = false;
        }
    }

    /**
     * Intializes the raw (echo) output component.
     *
     * @return void
     */
    public function initRawOutput(): void
    {
        // Disable layout ((if any)
        $this->disableLayout();

        // Set view rendering off
        $this->_helper->viewRenderer->setNoRender(true);

        $this->useHtmlView  = false;
        $this->useRawOutput = true;
    }

    /**
     * Stub for overruling default snippet loader initiation.
     */
    protected function loadSnippetLoader(): void
    {
        // Create the snippet with this controller as the parameter source
        $this->snippetLoader = new \MUtil\Snippets\SnippetLoader($this);
    }


    /**
     *
     * Translates the given string using plural notations
     * Returns the translated string
     *
     * @param string             $singular Singular translation string
     * @param string             $plural   Plural translation string
     * @param integer            $number   Number for detecting the correct plural
     * @param string|null        $locale   The locale or null to use the default
     * @return string
     */
    public function plural($singular, $plural, $number, $locale = null)
    {
        $this->translate->plural($singular, $plural, $number, $locale);
    }

    /* currently not in use
   public function setLayout($scriptFileName)
   {
       $this->layout->setLayout($scriptFileName);
   } // */

    /**
     * Set the session based message store.
     *
     * @param \Zend_Controller_Action_Helper_FlashMessenger $messenger
     * @return self
     */
    public function setMessenger(\Zend_Controller_Action_Helper_FlashMessenger $messenger): self
    {
        $this->messenger = $messenger;

        return $this;
    }


    /**
     * Set the html/head/title for this page. Can be a string or an array of string values.
     *
     * @param string|array $title;
     * @return self
     */
    public function setTitle(mixed $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Sets the translator
     *
     * @param \Zend_Translate $translate
     * @return self
     */
    public function setTranslate(\Zend_Translate $translate): self
    {
        $this->translate = $translate;
        $this->translateAdapter = $translate->getAdapter();

        return $this;
    }
}
