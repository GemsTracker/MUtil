<?php

namespace MUtil\Request;

use MUtil\Legacy\RequestHelper;
use Psr\Http\Message\ServerRequestInterface;

class RequestInfoFactory
{
    protected ServerRequestInterface $request;

    protected RequestHelper $requestHelper;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
        $this->requestHelper = new RequestHelper($request);
    }

    public function getRequestInfo(): RequestInfo
    {
        $requestInfo = new RequestInfo();
        $requestInfo->setCurrentAction($this->requestHelper->getActionName());
        $requestInfo->setCurrentController($this->requestHelper->getControllerName());
        $requestInfo->setIsPost($this->requestHelper->isPost());
        $requestInfo->setRequestQueryParams($this->request->getQueryParams());
        $requestInfo->setRequestPost($this->request->getParsedBody());

        $routeResult = $this->requestHelper->getRouteResult();
        $requestInfo->setRequestMatchedParams($routeResult->getMatchedParams());

        return $requestInfo;
    }
}