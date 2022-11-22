<?php

declare(strict_types=1);

/**
 *
 * @package    MUtil
 * @subpackage Handlers
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 */

namespace MUtil\Handler;

use DateTimeInterface;
use Mezzio\Session\SessionInterface;
use MUtil\Model;
use MUtil\Model\ModelAbstract;
use MUtil\Model\Bridge\DisplayBridge;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zalt\Base\RequestInfo;
use Zalt\Base\TranslateableTrait;
use Zalt\Html\Sequence;
use Zalt\Late\Late;
use Zalt\Ra\Ra;
use Zalt\Snippets\ModelBridge\TableBridge;
use Zalt\Snippets\ModelDetailTableSnippet;
use Zalt\Snippets\ModelYesNoDeleteSnippet;
use Zalt\Snippets\Zend\ZendModelFormSnippet;
use Zalt\SnippetsLoader\SnippetResponderInterface;

/**
 *
 * @package    MUtil
 * @subpackage Handlers
 * @since      Class available since version 1.9.2
 */
abstract class ModelSnippetLegacyHandlerAbstract implements RequestHandlerInterface
{
    use TranslateableTrait;

    /**
     * Default parameters for the autofilter action. Can be overruled
     * by setting $this->autofilterParameters
     *
     * @var array Mixed key => value array for snippet initialization
     */
    private array $_defaultAutofilterParameters = [
        'searchData'    => 'getSearchData',
        'searchFilter'  => 'getSearchFilter',
    ];

    /**
     * Default parameters for createAction, can be overruled by $this->createParameters
     * or $this->createEditParameters values with the same key.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array
     */
    private array $_defaultCreateParameters = [
        'createData' => true,
    ];

    /**
     * Default parameters for editAction, can be overruled by $this->editParameters
     * or $this->createEditParameters values with the same key.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array
     */
    private array $_defaultEditParameters = [
        'createData' => false,
    ];

    /**
     * Default parameters used for the import action
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    private array $_defaultImportParameters = [
        'defaultImportTranslator' => 'getDefaultImportTranslator',
        'importTranslators'       => 'getImportTranslators',
    ];

    /**
     * Default parameters for all actions, unless overruled by values with the same key at
     * the action level
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array
     */
    private array $_defaultParameters = [
        'cacheTags'             => 'getCacheTags',
        'includeNumericFilters' => 'getIncludeNumericFilters',
        '_messenger'             => 'getMessenger',
        'model'                 => 'getModel',
    ];

    /**
     * Created in createModel().
     *
     * Always retrieve using $this->getModel().
     *
     * $var Model\ModelAbstract $_model The model in use
     */
    private ?ModelAbstract $_model = null;

    /**
     *
     * @var ?array The search data
     */
    private ?array $_searchData = null;

    /**
     *
     * @var ?array The search data
     */
    private ?array $_searchFilter = null;

    /**
     * @var array Local store of parameters
     */
    private array $_snippetParams = [];

    /**
     * @var array local store of snippets
     */
    private array $_snippetNames = [];

    /**
     * The parameters used for the autofilter action.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialisation
     */
    protected array $autofilterParameters = ['columns' => 'getBrowseColumns'];

    /**
     * The snippets used for the autofilter action.
     *
     * @var array snippets name
     */
    protected array $autofilterSnippets = ['ModelTableSnippet'];

    /**
     * Tags for cache cleanup after changes, passed to snippets
     *
     * @var array
     */
    public array $cacheTags = [];

    /**
     * The parameters used for the create and edit actions.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected array $createEditParameters = [];

    /**
     * The snippets used for the create and edit actions.
     *
     * @var mixed String or array of snippets name
     */
    protected array $createEditSnippets = [ZendModelFormSnippet::class];

    /**
     * The parameters used for the edit actions, overrules any values in
     * $this->createEditParameters.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected array $createParameters = [];

    /**
     * Model level parameters used for all actions, overruled by any values set in any other
     * parameters array except the private $_defaultParamters values in this module.
     *
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected array $defaultParameters = [];

    /**
     * The default search data to use.
     *
     * @var array()
     */
    protected array $defaultSearchData = [];

    /**
     * The parameters used for the deactivate action.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected array $deactivateParameters = [];

    /**
     * The snippets used for the deactivate  action.
     *
     * @var mixed String or array of snippets name
     */
    protected array $deactivateSnippets = ['ModelConfirmDataChangeSnippet'];

    /**
     * The parameters used for the delete action.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected array $deleteParameters = [];

    /**
     * The snippets used for the delete action.
     *
     * @var mixed String or array of snippets name
     */
    protected array $deleteSnippets = [
        ModelYesNoDeleteSnippet::class,
        ];

    /**
     * The parameters used for the edit actions, overrules any values in
     * $this->createEditParameters.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected array $editParameters = [];

    /**
     * Array of the actions that use the model in form version.
     *
     * This determines the value of forForm().
     *
     * @var array $formActions Array of the actions that use the model with a form.
     */
    public array $formActions = ['create', 'delete', 'edit', 'import'];

    /**
     * @var Sequence
     */
    protected Sequence $html;

    /**
     * The parameters used for the import action
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected array $importParameters = [];

    /**
     * The snippets used for the import action
     *
     * @var mixed String or array of snippets name
     */
    protected array $importSnippets = ['ModelImportSnippet'];

    /**
     *
     * @var boolean $includeNumericFilters When true numeric filter keys (0, 1, 2...) are added to the filter as well
     */
    public bool $includeNumericFilters = false;

    /**
     * The parameters used for the index action minus those in autofilter.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected array $indexParameters = [];

    /**
     * The snippets used for the index action, before those in autofilter
     *
     * @var mixed String or array of snippets name
     */
    protected array $indexStartSnippets = [];

    /**
     * The snippets used for the index action, after those in autofilter
     *
     * @var mixed String or array of snippets name
     */
    protected array $indexStopSnippets = [];

    /**
     * The parameters used for the reactivate action.
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected array $reactivateParameters = [];

    /**
     * The snippets used for the reactivate action.
     *
     * @var mixed String or array of snippets name
     */
    protected array $reactivateSnippets = ['ModelConfirmDataChangeSnippet'];

    /**
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * @var RequestInfo
     */
    protected RequestInfo $requestInfo;

    /**
     * Optional search field renames
     *
     * The optional sharing of searches between action using searchSessionId's means that sometimes
     * the fields in the search have to be renamed for a specific action.
     *
     * @var array
     */
    protected array $searchFieldRenames = [];

    /**
     * An optional search session id.
     *
     * When set, autosearch gets a session memory. Multiple controllers can share one session id
     *
     * @var string
     */
    protected string $searchSessionId = '';

    /**
     * The parameters used for the show action
     *
     * When the value is a function name of that object, then that functions is executed
     * with the array key as single parameter and the return value is set as the used value
     * - unless the key is an integer in which case the code is executed but the return value
     * is not stored.
     *
     * @var array Mixed key => value array for snippet initialization
     */
    protected array $showParameters = [];

    /**
     * The snippets used for the show action
     *
     * @var array Array of snippets classes or names
     */
    protected array $showSnippets = [
        ModelDetailTableSnippet::class,
        ];

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
    public array $summarizedActions = ['index', 'autofilter'];

    /**
     *
     * @var boolean $useHtmlView true
     */
    public bool $useHtmlView = true;  // Overrule parent

    public function __construct(
        protected SnippetResponderInterface $responder,
        TranslatorInterface $translate)
    {
        $this->translate = $translate;

        $this->html = new Sequence();
        $this->_snippetParams['htmlContent'] = $this->html;
        
        Model::setDefaultBridge('display',  DisplayBridge::class);
        Model::setDefaultBridge('table', TableBridge::class);
    }

    /**
     * The request ID value
     *
     * @return ?string The request ID value
     */
    protected function _getIdParam(): ?string
    {
        return $this->requestInfo->getParam(Model::REQUEST_ID);
    }

    /**
     *
     * @param array $input
     * @return array
     */
    protected function _processParameters(array $input): array
    {
        $output = [];

        foreach ($input + $this->defaultParameters + $this->_defaultParameters as $key => $value) {
            if (is_string($value) && method_exists($this, $value)) {
                $value = $this->$value($key);

                if (is_integer($key) || ($value === null)) {
                    continue;
                }
            }
            $output[$key] = $value;
        }

        return $output;
    }

    /**
     * @param string $filename The name of the snippet
     * @param \MUtil\Ra::pairs $parameter_value_pairs name/value pairs ot add to the source for this snippet
     */
    public function addSnippet(string $filename, $parameter_value_pairs = null): void
    {
        $this->addSnippets([$filename], Ra::pairs(func_get_args(), 1));
    }

    /**
     * @param string[]|string $filenames Names of snippets
     * @param \MUtil\Ra::pairs $parameter_value_pairs name/value pairs ot add to the source for this snippet
     * @return void
     */
    public function addSnippets(mixed $filenames, $parameter_value_pairs = null): void
    {
        foreach ((array) $filenames as $filename) {
            $this->_snippetNames[] = $filename;
        }
        foreach (Ra::pairs(func_get_args(), 1) as $key => $value) {
            $this->_snippetParams[$key] = $value;
        }
    }

    /**
     * Set the action key in request
     *
     * Use this when an action is an Ajax action for retrieving
     * information for use within the screen of another action
     *
     * @param string $alias
     */
    protected function aliasAction(string $alias): void
    {
        /**
         * TODO reimplement alias action
         */
        /*$request = $this->getRequest();
        $request->setActionName($alias);
        $request->setParam($request->getActionKey(), $alias);*/
    }


    /**
     * The automatically filtered result
     *
     * @param $resetMvc bool When true only the filtered resulsts
     */
    public function autofilterAction(bool $resetMvc = true): void
    {
        // Model::$verbose = true;

        // We do not need to return the layout, just the above table
        if ($resetMvc) {
            // Make sure all links are generated as if the current request was index.
            $this->aliasAction('index');

            // \Zend_Layout::resetMvcInstance();
        }

        if ($this->autofilterSnippets) {
            $params = $this->_processParameters($this->autofilterParameters + $this->_defaultAutofilterParameters);

            $this->addSnippets($this->autofilterSnippets, $params);
        }

        if ($resetMvc) {
            // Lazy call here, because any echo calls in the snippets have not yet been
            // performed. so they will appear only in the next call when not lazy.
            $this->html->raw(Late::call(array('\\MUtil\\EchoOut\\EchoOut', 'out')));
        }
    }

    /**
     * Action for showing a create new item page
     */
    public function createAction(): void
    {
        if ($this->createEditSnippets) {
            $params = $this->_processParameters($this->createParameters + $this->createEditParameters + $this->_defaultCreateParameters);

            $this->addSnippets($this->createEditSnippets, $params);
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
     * @return ModelAbstract
     */
    abstract protected function createModel(bool $detailed, string $action): ModelAbstract;

    /**
     * Action for showing a deactivated item page
     */
    public function deactivateAction(): void
    {
        if ($this->deactivateSnippets) {
            $params = $this->_processParameters($this->deactivateParameters);

            $this->addSnippets($this->deactivateSnippets, $params);
        }
    }

    /**
     * Action for showing a deleted item page
     */
    public function deleteAction(): void
    {
        if ($this->deleteSnippets) {
            $params = $this->_processParameters($this->deleteParameters);

            $this->addSnippets($this->deleteSnippets, $params);
        }
    }

    /**
     * Action for showing an edit item page
     */
    public function editAction(): void
    {
        if ($this->createEditSnippets) {
            $params = $this->_processParameters($this->editParameters + $this->createEditParameters + $this->_defaultEditParameters);

            $this->addSnippets($this->createEditSnippets, $params);
        }
    }

    /**
     *
     * @param string $action The current action.
     * @return boolean True when this actions uses a form
     */
    public function forForm(string $action): bool
    {
        return in_array($action, $this->formActions);
    }

    /**
     * Set column usage to use for the browser.
     *
     * Must be an array of arrays containing the input for TableBridge->setMultisort()
     *
     * @return array|bool or false
     */
    public function getBrowseColumns(): bool|array
    {
        return false;
    }

    /**
     * Get the cache tags for this model (if any)
     *
     * @return array
     */
    public function getCacheTags(): array
    {
        return $this->cacheTags;
    }

    /**
     * Name of the default import translator
     *
     * @return string
     */
    public function getDefaultImportTranslator(): string
    {
        return 'default';
    }

    /**
     *
     * @return boolean $includeNumericFilters When true numeric filter keys (0, 1, 2...) are added to the filter as well
     */
    public function getIncludeNumericFilters(): bool
    {
        return $this->includeNumericFilters;
    }

    /**
     * Returns the model for the current $action.
     *
     * The parameters allow you to easily adapt the model to the current action. The $detailed
     * parameter was added, because the most common use of action is a split between detailed
     * and summarized actions.
     *
     * @return ModelAbstract
     */
    protected function getModel(): ModelAbstract
    {
        $action = strtolower($this->requestInfo->getCurrentAction());

        // Only get new model if there is no model or the model was for a different action
        if (! ($this->_model && $this->_model->isMeta('action', $action))) {
            $detailed = ! $this->isSummarized($action);

            /*$container = Model::getSource()->getContainer();
            if ($container instanceof ServiceManager) {
                $container->setService('action', $action);
                $container->setService('detailed', $detailed);
                $container->setService('forForm', $this->forForm($action));
            }*/

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
     * Get the data to use for searching: the values passed in the request + any defaults
     * used in the search form (or any other search request mechanism).
     *
     * It does not return the actual filter used in the query.
     *
     * @see getSearchFilter()
     *
     * @param boolean $useRequest Use the request as source (when false, the session is used)
     * @return array
     */
    public function getSearchData(bool $useRequest = true): array
    {
        if (is_array($this->_searchData)) {
            return $this->_searchData;
        }

        $sessionId = 'ModelSnippetActionAbstract_getSearchData';
        if ($this->searchSessionId) {
            $sessionId .= $this->searchSessionId;
        } else {
            // Always use a search id
            $sessionId .= get_class($this);
        }

        /**
         * @var $session SessionInterface
         */
        $session = $this->request->getAttribute(SessionInterface::class);

        $sessionData = [];
        if ($session->has($sessionId)) {
            $sessionData = $session->get($sessionId);
        }

        $defaults = $this->getSearchDefaults();

        if ($useRequest) {
            $data = $this->request->getQueryParams();
            $data += $this->request->getParsedBody();

            if (isset($data[Model::AUTOSEARCH_RESET]) && $data[Model::AUTOSEARCH_RESET]) {
                // Clean up values
                $sessionData = [];

                //$request->setParam(Model::AUTOSEARCH_RESET, null);
            } else {
                $data = $data + $sessionData;
            }

            // Always remove
            unset($data[Model::AUTOSEARCH_RESET]);

            // Store cleaned values in session (we do not store the defaults now as they may change
            // depending on the request and this way the filter data responds to that).
            // On the other hand we do store empty values in the session when they are in the defaults
            // array. The reason is that otherwise a non-empty default can later overrule an empty
            // value.
            $tmp = [];
            foreach ($data as $k => $v) {
                if (is_array($v) || strlen($v) || array_key_exists($k, $defaults)) {
                    $tmp[$k] = $v;
                }
            }
            $session->set($sessionId, $tmp);
        } else {
            $data = $sessionData;
        }

        // Add defaults to data without cleanup
        if ($defaults) {
            $data = $data + $defaults;
        }

        // \MUtil\EchoOut\EchoOut::track($data, $this->searchSessionId);

        // Remove empty strings and nulls HERE as they are not part of
        // the filter itself, but the values should be stored in the session.
        //
        // Remove all empty values (but not arrays) from the filter
        $this->_searchData = array_filter($data, function($i) { return is_array($i) || $i instanceof DateTimeInterface || strlen($i); });

        // \MUtil\EchoOut\EchoOut::track($this->_searchData, $this->searchSessionId);

        return $this->_searchData;
    }

    /**
     * Function to allow the creation of search defaults in code
     *
     * @see getSearchFilter()
     *
     * @return array
     */
    public function getSearchDefaults(): array
    {
        return $this->defaultSearchData;
    }

    /**
     * Get the filter to use with the model for searching including model sorts, etc..
     *
     * @param boolean $useRequest Use the request as source (when false, the session is used)
     * @return array or false
     */
    public function getSearchFilter(bool $useRequest = true): array
    {
        if (null !== $this->_searchFilter) {
            return $this->_searchFilter;
        }

        $filter = $this->getSearchData($useRequest);
        $this->_searchFilter = [];

        foreach ($filter as $field => $value) {
            if (isset($this->searchFieldRenames[$field])) {
                $field = $this->searchFieldRenames[$field];
            }

            $this->_searchFilter[$field] = $value;
        }

        // \MUtil\EchoOut\EchoOut::track($this->_searchFilter);

        return $this->_searchFilter;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->requestInfo = $this->responder->processRequest($request);
        
        // Add all params to the Late stack (for e.g. routing 
        Late::addStack('request', $this->requestInfo->getParams());
        
        // file_put_contents('data/logs/echo.txt', __CLASS__ . '->' . __FUNCTION__ . '(' . __LINE__ . '): ' .  var_export($this->requestInfo, true) . "\n", FILE_APPEND);
        
        $action   = $this->requestInfo->getCurrentAction() ?: 'index';
        $function = $action . 'Action';

//        file_put_contents('data/logs/echo.txt', strtolower($this->requestInfo->getCurrentController()) . "\n", FILE_APPEND);
//        file_put_contents('data/logs/echo.txt', $function . "\n", FILE_APPEND);

        $this->$function();

//        file_put_contents('data/logs/echo.txt', __FUNCTION__ . '(' . __LINE__ . '): ' . print_r($this->_snippetNames, true) . "\n", FILE_APPEND);
//        file_put_contents('data/logs/echo.txt', __FUNCTION__ . '(' . __LINE__ . '): ' . array_keys($this->_snippetParams), true) . "\n", FILE_APPEND);

        if ($this->html->count() || (! $this->_snippetNames)) {
            $this->_snippetNames[] = 'HtmlContentSnippet';
        }
        return $this->responder->getSnippetsResponse($this->_snippetNames, $this->_snippetParams);
    }

    /**
     * Generic model based import action
     */
    public function importAction(): void
    {
        if ($this->importSnippets) {
            $params = $this->_processParameters($this->importParameters + $this->_defaultImportParameters);

            $this->addSnippets($this->importSnippets, $params);
        }
    }

    /**
     * Action for showing a browse page
     */
    public function indexAction(): void
    {
        $params = null;
        if ($this->indexStartSnippets || $this->indexStopSnippets) {
            $params = $this->_processParameters(
                $this->indexParameters + $this->autofilterParameters + $this->_defaultAutofilterParameters
            );

            if ($this->indexStartSnippets) {
                $this->addSnippets($this->indexStartSnippets, $params);
            }
        }

        $this->autofilterAction(false);

        if ($this->indexStopSnippets) {
            $this->addSnippets($this->indexStopSnippets, $params);
        }
    }

    /**
     *
     * @param string $action The current action.
     * @return boolean True when this actions uses only summary data
     */
    public function isSummarized(string $action): bool
    {
        return in_array($action, $this->summarizedActions);
    }

    /**
     * Action for showing a reactivated item page
     */
    public function reactivateAction(): void
    {
        if ($this->reactivateSnippets) {
            $params = $this->_processParameters($this->reactivateParameters);

            $this->addSnippets($this->reactivateSnippets, $params);
        }
    }

    /**
     * Action for showing an item page
     */
    public function showAction(): void
    {
        if ($this->showSnippets) {
            $params = $this->_processParameters($this->showParameters);

            $this->addSnippets($this->showSnippets, $params);
        }
    }
}
