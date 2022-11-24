<?php

namespace MUtil\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zalt\Base\RequestInfo;
use Zalt\Base\TranslateableTrait;
use Zalt\Html\Sequence;
use Zalt\Late\Late;
use Zalt\Ra\Ra;
use Zalt\SnippetsLoader\SnippetResponderInterface;

class SnippetLegacyHandlerAbstract implements RequestHandlerInterface
{
    use TranslateableTrait;

    /**
     * @var array Local store of parameters
     */
    private array $_snippetParams = [];

    /**
     * @var array local store of snippets
     */
    private array $_snippetNames = [];

    /**
     * @var Sequence
     */
    protected Sequence $html;

    /**
     * @var ServerRequestInterface
     */
    protected ServerRequestInterface $request;

    /**
     * @var RequestInfo
     */
    protected RequestInfo $requestInfo;
    
    public function __construct(
        protected SnippetResponderInterface $responder,
        TranslatorInterface $translate)
    {
        $this->translate = $translate;
        $this->html = new Sequence();
        $this->_snippetParams['htmlContent'] = $this->html;
    }

    /**
     * @param string $filename The name of the snippet
     * @param Ra::pairs $parameter_value_pairs name/value pairs ot add to the source for this snippet
     */
    public function addSnippet(string $filename, $parameter_value_pairs = null): void
    {
        $this->addSnippets([$filename], Ra::pairs(func_get_args(), 1));
    }

    /**
     * @param string[]|string $filenames Names of snippets
     * @param Ra::pairs $parameter_value_pairs name/value pairs ot add to the source for this snippet
     * @return void
     */
    public function addSnippets(string|array $filenames, $parameter_value_pairs = null): void
    {
        foreach ((array) $filenames as $filename) {
            $this->_snippetNames[] = $filename;
        }
        foreach (Ra::pairs(func_get_args(), 1) as $key => $value) {
            $this->_snippetParams[$key] = $value;
        }
    }

    protected function getActionName(string $action): string
    {
        $actionParts = explode('-', $action);
        $capitalizedActionParts = array_map('ucfirst', $actionParts);
        return lcfirst(join('', $capitalizedActionParts)) . 'Action';
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->requestInfo = $this->responder->processRequest($request);

        // Add all params to the Late stack (for e.g. routing
        Late::addStack('request', $this->requestInfo->getParams());

        $action   = $this->requestInfo->getCurrentAction() ?: 'index';
        $function = $this->getActionName($action);

        $result = $this->$function();
        if ($result instanceof ResponseInterface) {
            return $result;
        }

        if ($this->html->count() || (! $this->_snippetNames)) {
            $this->_snippetNames[] = 'HtmlContentSnippet';
        }
        return $this->responder->getSnippetsResponse($this->_snippetNames, $this->_snippetParams);
    }
}