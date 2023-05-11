<?php

/**
 *
 * @package    MUtil
 * @subpackage Batch
 * @author     Matijs de Jong <mjong@magnafacta.nl>
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 */

namespace MUtil\Batch;

use Mezzio\Session\SessionInterface;
use MUtil\Batch\Info\InfoInterface;
use MUtil\Batch\Info\ObjectInfo;
use MUtil\Batch\Info\SessionInfo;
use MUtil\Batch\Stack\ObjectStack;
use MUtil\Batch\Stack\SessionStack;
use MUtil\Batch\Stack\Stackinterface;
use MUtil\Registry\TargetAbstract;
use Psr\Log\LoggerInterface;
use Exception;
use Countable;

/**
 * The Batch package is for the sequential processing of commands which may
 * take to long to execute in a single request.
 *
 * The abstract batch handles the command stack, keeping track of batch specific
 * counters and messages and the communication to the end user including display
 * of any messages set during execution and reporting back execution errors
 * occured during a run, e.g. when a job throws an exception during execution.
 *
 * The prefereed method to use this object is to write multiple small jobs using
 * \MUtil\Task\TaskInterface and then use MUtil\Task\TaskBatch to execute these
 * commands.
 *
 * Global objects in the Task will be loaded automatically when they implement the
 * \MUtil\Registry\TargetInterface (the same as happens for with this object). All
 * other parameters for the task should be scalar.
 *
 * The other option use this package by creating a sub class of this class and write
 * the methods that run the code to be executed (and then write the code that adds
 * those functions to be executed). The functions need to return a true value
 * when they completed successfully, otherwise they will be repeated until they do.
 *
 * Each step in the sequence consists of a method name of the child object
 * and any number of scalar variables and array's containing scalar variables.
 *
 * See \MUtil\Batch\WaitBatch for example usage.
 *
 * The storage engine used for the commands is separated from this object so we
 * could use e.g. \Zend_Queue as an alternative for storing the command stack.
 * Currently \MUtil\Batch\Stack\SessionStack is used by default.
 * \MUtil_Batch_Stack_CachStack can be used as well and has the advantage of
 * storing all data in a separate file.
 *
 * @see \MUtil\Task\TaskBatch
 * @see \MUtil_Batch_Stack_StackInterface
 * @see \MUtil\Registry\TargetInterface
 * @see \MUtil\Batch\WaitBatch
 *
 * @package    MUtil
 * @subpackage Batch
 * @copyright  Copyright (c) 2012 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.2
 */
abstract class BatchAbstract extends TargetAbstract implements Countable
{

    protected string $_buttonClass = 'btn';

    /**
     *
     * @var float The timer for _checkReport()
     */
    private $_checkReportStart = null;

    /**
     * Optional form id, for using extra params from the form during batch execution.
     *
     * @var string
     */
    protected $_formId;

    /**
     * Name to prefix the functions, to avoid naming clashes.
     *
     * @var string Default is the classname with an extra underscore
     */
    protected $_functionPrefix;

    /**
     * An id unique for this session.
     *
     * @var string Unique id
     */
    private string $id;

    /**
     * Stack to keep existing id's.
     *
     * @var array
     */
    private static $_idStack = [];

    /**
     * Holds the last message set by the batch job
     *
     * @var string
     */
    private $_lastMessage = null;

    /**
     *
     * @var string
     */
    private $_messageLogFile;

    /**
     *
     * @var boolean
     */
    private bool $_messageLogWhenAdding = false;

    /**
     *
     * @var boolean
     */
    private bool $_messageLogWhenSetting = false;

    /**
     * When true the progressbar should start immediately. When false the user has to perform an action.
     *
     * @var boolean
     */
    public bool $autoStart = false;

    /**
     * The number of bytes to pad during push communication in Kilobytes.
     *
     * This is needed as many servers need extra output passing to avoid buffering.
     *
     * Also this allows you to keep the server buffer high while using this JsPush.
     *
     * @var int
     */
    public int $extraPushPaddingKb = 0;

    /**
     * Manual set of the finish url. Setting an empty string will disable the finish redirect
     * @var string
     */
    public $finishUrl;

    protected InfoInterface $infoContainer;

    /**
     * The number of bytes to pad for the first push communication in Kilobytes. If zero
     * $extraPushPaddingKb is used.
     *
     * This is needed as many servers need extra output passing to avoid buffering.
     *
     * Also this allows you to keep the server buffer high while using this JsPush.
     *
     * @var int
     */
    public int $initialPushPaddingKb = 0;

    protected ?LoggerInterface $logger;

    /**
     * Date format for logging messages
     *
     * @var string
     */
    protected string $messageLogDateFormat = 'Y-m-d H:i:s';

    /**
     * Message log format string for date string and message string
     *
     * @var string
     */
    protected string $messageLogFormatString = '[%s]: %s';

    /**
     * The minimal time used between send progress reports.
     *
     * This enables quicker processing as multiple steps can be taken in a single
     * run(), without the run taking too long to answer.
     *
     * Set to 0 to report back on each step.
     *
     * @var int
     */
    public int $minimalStepDurationMs = 1000;

    /**
     * @var Progress
     */
    protected Progress $progress;

    /**
     * The name of the parameter used for progress panel signals
     *
     * @var string
     */
    public string $progressParameterName = 'progress';

    /**
     * The value required for the progress panel to report and reset
     *
     * @var string
     */
    public string $progressParameterReportValue = 'report';

    /**
     * The value required for the progress panel to restart
     *
     * The value isn't used in itself, but having an empty value screws up the url
     * interpretation.
     *
     * @var string
     */
    public string $progressParameterRestartValue = 'restart';

    /**
     * The value required for the progress panel to start running
     *
     * @var string
     */
    public string $progressParameterRunValue = 'run';

    /**
     * The command stack
     *
     * @var Stackinterface
     */
    protected Stackinterface $stack;

    /**
     * The place to store new variables for the source
     *
     * @var \ArrayObject
     */
    protected $variables;

    /**
     *
     * @param string $id A unique name identifying this batch
     * @param \MUtil\Batch\Stack\Stackinterface $stack Optional different stack than session stack
     */
    public function __construct($id, SessionInterface $session = null, Stackinterface $stack = null, LoggerInterface $logger = null)
    {
        $this->progress = new Progress();
        $this->logger = $logger;
        $this->setBatchId($id);
        $this->_initInfoContainer($session);

        $this->setStack($stack, $session);

        $batchInfo = $this->getBatchInfo();

        if (!isset($batchInfo['processed'])) {
            $this->reset();
            $batchInfo = $this->getBatchInfo();
        }
        if ($batchInfo['finished'] === true) {
            $this->progress->finish();
        }

        $this->_updateProgress();
    }

    /**
     * Check if the aplication should report back to the user
     *
     * @return boolean True when application should report to the user
     */
    private function _checkReport(): bool
    {
        $batchInfo = $this->getBatchInfo();
        // @TODO Might be confusing if one of the first steps adds more steps, make this optional?
        if (isset($batchInfo['processed']) && $batchInfo['processed'] === 1) {
            return true;
        }

        if (null === $this->_checkReportStart) {
            $this->_checkReportStart = microtime(true) + ($this->minimalStepDurationMs / 1000);
            return false;
        }

        if (microtime(true) > $this->_checkReportStart) {
            $this->_checkReportStart = null;
            return true;
        }

        return false;
    }

    /**
     * Signal an loop item has to be run again.
     */
    protected function _extraRun(): void
    {
        $batchInfo = $this->getBatchInfo();
        $batchInfo['count'] += 1;
        $batchInfo['processed'] += 1;

        $this->infoContainer->set($batchInfo);
    }

    /**
     * Helper function to complete the progressbar.
     */
    protected function _finishProgress(): void
    {
        $batchInfo = $this->getBatchInfo();
        $batchInfo['finished'] = true;

        $this->infoContainer->set($batchInfo);

        $this->progress->finish();
    }

    /**
     * Initialize persistent storage
     *
     * @param string $name The id of this batch
     */
    private function _initInfoContainer(SessionInterface $session = null): void
    {
        if ($session instanceof SessionInterface) {
            $sessionId = get_class($this) . '_' . $this->id;
            $this->infoContainer = new SessionInfo($session, $sessionId);
        } else {
            $this->infoContainer = new ObjectInfo();
        }
    }

    /**
     * Helper function to update the progressbar.
     */
    protected function _updateProgress(): void
    {
        $batchInfo = $this->getBatchInfo();
        $this->progress->setCount($batchInfo['count']);
        $this->progress->setProgress($batchInfo['processed']);
    }

    /**
     * Add to exception store
     * @param Exception $e
     * @return self
     */
    public function addException(Exception $e): self
    {
        $message = $e->getMessage();

        $this->addMessage($message);
        $batchInfo = $this->getBatchInfo();
        $batchInfo['exceptions'][] = $message;

        if ($this->logger instanceof LoggerInterface) {
            $messages[] = $message;

            $previous = $e->getPrevious();
            while ($previous) {
                $messages[] = '  Previous exception: ' . $previous->getMessage();
                $previous = $previous->getPrevious();
            }
            $messages[] = $e->getTraceAsString();

            $this->logger->error(implode("\n", $messages));
        }
        return $this;
    }

    /**
     * Add an execution step to the command stack.
     *
     * @param string $method Name of a method of this object
     * @param mixed $param1 Optional scalar or array with scalars, as many parameters as needed allowed
     * @param mixed $param2 ...
     * @return self
     */
    protected function addStep(string $method, mixed $param1 = null): self
    {
        if (! method_exists($this, $method)) {
            throw new \MUtil\Batch\BatchException("Invalid batch method: '$method'.");
        }

        $params = array_slice(func_get_args(), 1);

        if ($this->stack->addStep($method, $params)) {
            $this->addStepCount(1);
        }

        return $this;
    }

    /**
     * Allow to add steps to the counter
     *
     * This should only be used by iterable tasks that execute in more then 1 step
     *
     * @param int $number
     */
    public function addStepCount(int $number): void
    {
        if ($number > 0) {
            $batchInfo = $this->getBatchInfo();
            $batchInfo['count'] += 1;
            $this->progress->setCount($batchInfo['count']);
            $this->infoContainer->set($batchInfo);
        }
    }

    /**
     * Add a message to the message stack.
     *
     * @param string $text A message to the user
     * @return self
     */
    public function addMessage($text): self
    {
        $batchInfo = $this->getBatchInfo();
        $batchInfo['messages'][] = $text;
        $this->infoContainer->set($batchInfo);

        $this->_lastMessage = $text;

        if ($this->_messageLogWhenAdding) {
            $this->logMessage($text);
        }

        return $this;
    }

    /**
     * Increment a named counter
     *
     * @param string $name
     * @param integer $add
     * @return integer
     */
    public function addToCounter($name, $add = 1)
    {
        $batchInfo = $this->getBatchInfo();
        if (! isset($batchInfo['counters'][$name])) {
            $batchInfo['counters'][$name] = 0;
        }
        $batchInfo['counters'][$name] += $add;
        $this->infoContainer->set($batchInfo);

        return $batchInfo['counters'][$name];
    }

    /**
     * The number of commands in this batch (both processed
     * and unprocessed).
     *
     * @return int
     */
    public function count(): int
    {
        $batchInfo = $this->getBatchInfo();
        return $batchInfo['count'];
    }

    public function getBatchInfo(): array
    {
        return $this->infoContainer->get();
    }

    /**
     * Return the value of a named counter
     *
     * @param string $name
     * @return integer
     */
    public function getCounter($name): int
    {
        $batchInfo = $this->getBatchInfo();
        if (isset($batchInfo['counters'][$name])) {
            return $batchInfo['counters'][$name];
        }

        return 0;
    }

    /**
     * Return the stored exceptions.
     *
     * @return array of \Exceptions
     */
    public function getExceptions(): array
    {
        $batchInfo = $this->getBatchInfo();
        return $batchInfo['exceptions'];
    }

    /**
     * Get the optional form id, for using extra params from the form during batch execution.
     *
     * @return string
     */
    public function getFormId(): string
    {
        return $this->_formId;
    }

    /**
     * Returns the prefix used for the function names for the PUSH method to avoid naming clashes.
     *
     * Set automatically to get_class($this) . '_' $this->_id . '_', use different name
     * in case of name clashes.
     *
     * @see setFunctionPrefix()
     *
     * @return string
     */
    protected function getFunctionPrefix(): string
    {
        if (! $this->_functionPrefix) {
            $this->setFunctionPrefix(get_class($this) . '_' . $this->id . '_');
        }

        return (string) $this->_functionPrefix;
    }

    /**
     * Return the batch id
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Returns the lat message set for feedback to the user.
     * @return string
     */
    public function getLastMessage(): ?string
    {
        return $this->_lastMessage;
    }

    /**
     * Get a message from the message stack with a specific id.
     *
     * @param scalar $id
     * @param string $default A default message
     * @return string
     */
    public function getMessage(string $id, ?string $default = null): ?string
    {
        $batchInfo = $this->getBatchInfo();
        if (array_key_exists($id, $batchInfo['messages'])) {
            return $batchInfo['messages'][$id];
        } else {
            return $default;
        }
    }

    /**
     * String of messages from the batch
     *
     * Do not forget to reset() the batch if you're done with it after
     * displaying the report.
     *
     * @param boolean $reset When true the batch is reset afterwards
     * @return array
     */
    public function getMessages(bool $reset = false): array
    {
        $batchInfo = $this->getBatchInfo();
        $messages = $batchInfo['messages'];

        if ($reset) {
            $this->reset();
        }

        return $messages;
    }

    /**
     * The Zend ProgressBar handles the communication through
     * an adapter interface.
     *
     * @return Progress
     */
    public function getProgress(): Progress
    {
        return $this->progress;
    }

    /**
     * Get the current progress percentage
     *
     * @return float
     */
    public function getProgressPercentage(): float
    {
        $batchInfo = $this->getBatchInfo();
        // Use number format to correctly round the number without floating number precision errors
        // Output is json so always use digital dot!
        $value = $batchInfo['processed'] / max($batchInfo['count'], 1) * 100;
        return number_format($value, 2, '.','');
    }

    /**
     * Returns a button that can be clicked to restart the progress bar.
     *
     * @param mixed $arg_array \MUtil\Ra::args() arguments to populate link with
     * @return \MUtil\Html\HtmlElement
     */
    public function getRestartButton($args_array = 'Restart')
    {
        $args = \MUtil\Ra::args(func_get_args());
        $args['onclick'] = new \MUtil\Html\OnClickArrayAttribute(
            new \MUtil\Html\Raw('if (! this.disabled) {location.href = "'),
            new \MUtil\Html\HrefArrayAttribute(
                array($this->progressParameterName => $this->progressParameterRestartValue)
            ),
            new \MUtil\Html\Raw('";} this.disabled = true; event.cancelBubble=true;'));

        $button = new \MUtil\Html\HtmlElement('button', $args);
        $button->appendAttrib('class', $this->_buttonClass.' btn-succes');

        return $button;
    }

    /**
     * Returns a link that can be clicked to restart the progress bar.
     *
     * @param mixed $arg_array \MUtil\Ra::args() arguments to populate link with
     * @return \MUtil\Html\AElement
     */
    public function getRestartLink($args_array = 'Restart')
    {
        $args = \MUtil\Ra::args(func_get_args());
        $args['href'] = array($this->progressParameterName => $this->progressParameterRestartValue);

        return new \MUtil\Html\AElement($args);
    }

    /**
     * Return a variable from the session store.
     *
     * @param string $name Name of the variable
     * @return mixed
     */
    public function getSessionVariable(string $name): mixed
    {
        $batchInfo = $this->getBatchInfo();
        if (isset($batchInfo['source'], $batchInfo['source'][$name])) {
            return $batchInfo['source'][$name];
        }
        return null;
    }

    /**
     * Return the variables from the session store.
     *
     * @return array|null
     */
    protected function getSessionVariables(): ?array
    {
        $batchInfo = $this->getBatchInfo();
        if (isset($batchInfo['source'])) {
            return $batchInfo['source'];
        }
        return null;
    }

    /**
     * Get the current stack
     *
     * @return Stackinterface
     */
    public function getStack(): Stackinterface
    {
        return $this->stack;
    }

    /**
     * Returns a button that can be clicked to start the progress bar.
     *
     * @param mixed $arg_array \MUtil\Ra::args() arguments to populate link with
     * @return \MUtil\Html\HtmlElement
     */
    public function getStartButton($args_array = 'Start')
    {
        $args = \MUtil\Ra::args(func_get_args());
        $args['onclick'] = 'if (! this.disabled) {' . $this->getFunctionPrefix() .
            'Start();} this.disabled = true; event.cancelBubble=true;';

        $button = new \MUtil\Html\HtmlElement('button', $args);
        $button->appendAttrib('class', $this->_buttonClass.' btn-succes');

        return $button;
    }

    /**
     * Return a variable from the general store or from the session store if it exist there.
     *
     * @param string $name Name of the variable
     * @return mixed (continuation pattern)
     */
    public function getVariable(string $name): mixed
    {
        if (isset($this->variables[$name])) {
            return $this->variables[$name];
        }
        return $this->getSessionVariable($name);
    }

    /**
     * Return whether a session variable exists in the session store.
     *
     * @param string $name Name of the variable
     * @return boolean
     */
    public function hasSessionVariable(string $name): bool
    {
        $batchInfo = $this->getBatchInfo();
        return isset($batchInfo['source'], $batchInfo['source'][$name]);
    }

    /**
     * Return whether a variable exists the general store or in the session store.
     *
     * @param string $name Name of the variable
     * @return boolean
     */
    public function hasVariable(string $name): bool
    {
        return isset($this->variables[$name]) || $this->hasSessionVariable($name);
    }

    /**
     * Return true after commands all have been ran.
     *
     * @return boolean
     */
    public function isFinished(): bool
    {
        return $this->progress->isFinished();
    }

    /**
     * Return true when at least one command has been loaded.
     *
     * @return boolean
     */
    public function isLoaded(): bool
    {
        return ($this->progress->getCount() > 0 || $this->progress->getStep() > 0);
    }

    /**
     *
     * @param string $text Line to log
     * @return self
     */
    public function logMessage(?string $text = null): self
    {
        if ($this->_messageLogFile) {
            if ($text) {
                $text = sprintf($this->messageLogFormatString, gmdate($this->messageLogDateFormat), $text);
            }
            file_put_contents($this->_messageLogFile, $text . PHP_EOL, FILE_APPEND);
        }
        if ($this->logger) {
            $this->logger->info($text);
        }

        return $this;
    }

    /**
     * Reset and empty the session storage
     *
     * @return self
     */
    public function reset(): self
    {
        $batchInfo = [
            'count' => 0,
            'counters' => [],
            'exceptions' => [],
            'finished' => false,
            'messages' => [],
            'processed' => 0,
        ];

        $this->infoContainer->set($batchInfo);
        $this->progress->reset();

        $this->stack->reset();

        return $this;
    }

    /**
     * Reset a named counter
     *
     * @param string $name
     * @return self
     */
    public function resetCounter(string $name): self
    {
        $batchInfo = $this->getBatchInfo();
        unset($batchInfo['counters'][$name]);
        $this->infoContainer->set($batchInfo);

        return $this;
    }

    /**
     * Reset a message on the message stack with a specific id.
     *
     * @param scalar $id
     * @return self
     */
    public function resetMessage(string $id): self
    {
        $batchInfo = $this->getBatchInfo();
        unset($batchInfo['messages'][$id]);
        $this->infoContainer->set($batchInfo);

        return $this;
    }

    /**
     * Run as much code as possible, but do report back.
     *
     * Returns true if any output was communicated, i.e. the "normal"
     * page should not be displayed.
     *
     * @param array Request query params
     * @return boolean True when something ran
     */
    public function run(array $requestQueryParams): bool
    {
        // Check for run url
        if (isset($requestQueryParams[$this->progressParameterName]) && $requestQueryParams[$this->progressParameterName] === $this->progressParameterRunValue) {
            // [Try to] remove the maxumum execution time for this session
            @ini_set("max_execution_time", 0);
            @set_time_limit(0);

            /*if ($this->isPush()) {
                return $this->runContinuous();
            }*/

            // Is there something to run?
            if ($this->isFinished() || (! $this->isLoaded())) {
                return false;
            }

            while ($this->step()) {
                // error_log('Cur: ' . microtime(true) . ' report is '. (microtime(true) > $reportRun ? 'true' : 'false'));
                if ($this->_checkReport()) {
                    // Communicate progress
                    return true;
                }
            }

            // Only reached when at end of commands
            $this->_finishProgress();

            // There is progressBar output
            return true;
        } else {
            // No ProgressBar output
            return false;
        }
    }

    /**
     * Run the whole batch at once, without communicating with a progress bar.
     *
     * @return int Number of steps taken
     */
    public function runAll(): int
    {
        // [Try to] remove the maxumum execution time for this session
        @ini_set("max_execution_time", 0);
        @set_time_limit(0);

        while ($this->step());

        $batchInfo = $this->getBatchInfo();
        return $batchInfo['processed'];
    }

    /**
     * Run the whole batch at once, while still communicating with a progress bar.
     *
     * @return boolean True when something ran
     */
    public function runContinuous(): bool
    {
        // Is there something to run?
        if ($this->isFinished() || (! $this->isLoaded())) {
            return false;
        }

        // [Try to] remove the maxumum execution time for this session
        @ini_set("max_execution_time", 0);
        @set_time_limit(0);

        while ($this->step()) {
            if ($this->_checkReport()) {
                // Communicate progress
                $this->_updateProgress();
            }
        }
        $this->_updateProgress();
        $this->_finishProgress();

        return true;
    }

    protected function setBatchId(string $id): void
    {
        $id = preg_replace('/[^a-zA-Z0-9_]/', '', $id);

        if (isset(self::$_idStack[$id])) {
            throw new \MUtil\Batch\BatchException("Duplicate batch id created: '$id'");
        }
        self::$_idStack[$id] = $id;

        $this->id = $id;
    }

    /**
     * Set the optional form id, for using extra params from the form during batch execution.
     *
     * @param string $id
     * @return self
     */
    public function setFormId(string $id): self
    {
        $this->_formId = $id;
        return $this;
    }

    /**
     * Name prefix for PUSH functions.
     *
     * Set automatically to get_class($this) . '_' $this->_id . '_', use different name
     * in case of name clashes.
     *
     * @param string $prefix
     * @return self
     */
    public function setFunctionPrefix(string $prefix): self
    {
        $this->_functionPrefix = $prefix;
        return $this;
    }

    /**
     * Add/set a message on the message stack with a specific id.
     *
     * @param scalar $id
     * @param string $text A message to the user
     * @return self
     */
    public function setMessage(string $id, string $text): self
    {
        $batchInfo = $this->getBatchInfo();
        $batchInfo['messages'][$id] = $text;
        $this->infoContainer->set($batchInfo);

        $this->_lastMessage = $text;

        if ($this->_messageLogWhenSetting && $this->_messageLogFile) {
            $this->logMessage($text);
        }

        return $this;
    }

    /**
     *
     * @param string $filename Filename to log to
     * @param boolean $logSet Log setMessage calls
     * @param boolean $logAdd Log addMessage calls
     * @return $this
     */
    public function setMessageLogFile(string $filename, bool $logSet = true, bool $logAdd = true): self
    {
        $this->_messageLogFile        = $filename;
        $this->_messageLogWhenSetting = $logSet && $filename;
        $this->_messageLogWhenAdding  = $logAdd && $filename;

        return $this;
    }

    public function setMessageLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Set the progress template
     *
     * Available placeholders:
     * {total}      Total time
     * {elapsed}    Elapsed time
     * {remaining}  Remaining time
     * {percent}    Progress percent without the % sign
     * {msg}        Message reveiced
     *
     * @var string
     */
    public function setProgressTemplate(string $template): void
    {
        $this->_progressTemplate = $template;
    }

    protected function setSessionId(string $id): void
    {
        $this->sessionId = get_class($this) . '_' . $id;
    }

    /**
     * Store a variable in the session store.
     *
     * @param string $name Name of the variable
     * @param mixed $variable Something that can be serialized
     * @return self
     */
    public function setSessionVariable(string $name, mixed $variable): self
    {
        $batchInfo = $this->getBatchInfo();
        if (!isset($batchInfo['source'])) {
            $batchInfo['source'] = [];
        }

        $batchInfo['source'][$name] = $variable;
        $this->infoContainer->set($batchInfo);

        return $this;
    }

    protected function setStack(?Stackinterface $stack, ?SessionInterface $session = null): void
    {
        if (null === $stack) {
            if ($session instanceof SessionInterface) {
                $stack = new SessionStack($this->id, $session);
            } else {
                $stack = new ObjectStack();
            }
        }
        $this->stack = $stack;
    }

    /**
     * Add/set an execution step to the command stack. Named to prevent double addition.
     *
     * @param string $method Name of a method of this object
     * @param mixed $id A unique id to prevent double adding of something to do
     * @param mixed $param1 Scalar or array with scalars, as many parameters as needed allowed
     * @return self
     */
    protected function setStep(string $method, ?string $id, mixed $param1 = null): self
    {
        if (! method_exists($this, $method)) {
            throw new BatchException("Invalid batch method: '$method'.");
        }

        $params = array_slice(func_get_args(), 2);

        if ($this->stack->setStep($method, $id, $params)) {
            $this->addStepCount(1);
        }

        return $this;
    }

    /**
     * Store a variable in the general store.
     *
     * These variables have to be reset for every run of the batch.
     *
     * @param string $name Name of the variable
     * @param mixed $variable Something that can be serialized
     * @return self
     */
    public function setVariable(string $name, mixed $variable): self
    {
        if (null === $this->variables) {
            $this->variables = new \ArrayObject();
        }

        $this->variables[$name] = $variable;
        return $this;
    }

    /**
     * Progress a single step on the command stack
     *
     * @return boolean
     */
    protected function step(): bool
    {
        if ($this->stack->hasNext()) {

            try {
                $command = $this->stack->getNext();
                if (! isset($command[0], $command[1])) {
                    throw new BatchException("Invalid batch command: '$command[0]'.");
                }
                list($method, $params) = $command;

                if (! method_exists($this, $method)) {
                    throw new BatchException("Invalid batch method: '$method'.");
                }

                if (call_user_func_array(array($this, $method), $params)) {
                    $this->stack->gotoNext();
                }
                $batchInfo = $this->getBatchInfo();
                $batchInfo['processed'] += 1;
                $this->infoContainer->set($batchInfo);
                $this->_updateProgress();

            } catch (\Exception $e) {
                $this->addMessage('ERROR!!!');
                $this->addMessage(
                    'While calling:' . $command[0] . '(' . implode(',', \MUtil\Ra::flatten($command[1])) . ')'
                );
                $this->addException($e);
                $this->stopBatch($e->getMessage());

                //\MUtil\EchoOut\EchoOut::track($e);
            }
            return true;
        } else {
            return false;
        }
    }

    public function stopBatch(string $message): void
    {
        // Set to stopped
        $batchInfo = $this->getBatchInfo();
        $batchInfo['finished'] = true;
        $this->progress->finish();
        $this->infoContainer->set($batchInfo);

        // Cleanup stack
        $this->stack->reset();

        $this->addMessage($message);
    }

    /**
     * Unload a batch
     *
     * Normally we don't need this, but in unit test we need to be able to run a batch after is was finished
     *
     * @param string $id
     */
    public static function unload(string $id): void
    {
        unset(self::$_idStack[$id]);
    }
}
