<?php

namespace MUtil\Request;

use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use MUtil\Legacy\RequestHelper;
use Psr\Http\Message\ServerRequestInterface;
use Zalt\Base\BaseDir;

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
        $actionName         = null;
        $controllerName     = null;
        $routeName          = null;
        $routeMatchedParams = [];

        $path = $this->request->getUri()->getPath();
        if (pathInfo($path, PATHINFO_EXTENSION)) {
            $baseUrl = dirname($path);
        } else {
            $baseUrl = $path;
        }
        // return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest')

        $routeResult = $this->request->getAttribute(RouteResult::class);
        if ($routeResult instanceof RouteResult) {
            $routeMatchedParams = $routeResult->getMatchedParams();

            $route = $routeResult->getMatchedRoute();
            if ($route instanceof Route) {
                $routeName = $route->getName();
                $options   = $route->getOptions();

                if (isset($options['controller'])) {
                    $controllerName = $options['controller'];
                }
                if (isset($options['action'])) {
                    $actionName = $options['action'];
                }
            }
        }
        $path = '';
        if ($controllerName) {
            $path = $controllerName;
            if ($actionName) {
                $path .= '/' . $actionName;
            }
        }

        $requestInfo = new RequestInfo(
            $controllerName,
            $actionName,
            $routeName,
            BaseDir::getBaseDir(),
            $path,
            'POST' == $this->request->getMethod(),
            $routeMatchedParams,
            $this->request->getParsedBody() ?: [],
            $this->request->getQueryParams()
        );
        $requestInfo->setRequestPost($this->request->getParsedBody());
        $requestInfo->setCurrentRouteResult($this->requestHelper->getRouteResult());

        $routeResult = $this->requestHelper->getRouteResult();
        $requestInfo->setRequestMatchedParams($routeResult->getMatchedParams());

        return $requestInfo;
    }
}