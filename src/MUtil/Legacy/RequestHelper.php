<?php

declare(strict_types=1);


namespace MUtil\Legacy;


use Mezzio\Router\Route;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ServerRequestInterface;

class RequestHelper
{
    protected ServerRequestInterface $request;

    protected array $routeOptions = [];

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function getControllerName(): ?string
    {
        $options = $this->getRouteOptions();
        if (isset($options['controller'])) {
            return $options['controller'];
        }
        return null;
    }

    public function getActionName(): ?string
    {
        $options = $this->getRouteOptions();
        if (isset($options['action'])) {
            return $options['action'];
        }
        return null;
    }

    public function getRoute(): ?Route
    {
        $routeResult = $this->getRouteResult();
        if (!$routeResult instanceof RouteResult) {
            return null;
        }
        $route = $routeResult->getMatchedRoute();
        if ($route instanceof Route) {
            return $route;
        }
        return null;
    }

    public function getRouteResult(): ?RouteResult
    {
        return $this->request->getAttribute(RouteResult::class);
    }

    public function getRouteOptions(): ?array
    {
        if (!$this->routeOptions) {
            $route = $this->getRoute();
            if (!$route) {
                return null;
            }
            $this->routeOptions = $route->getOptions();
        }

        return $this->routeOptions;
    }

    public function isPost(): bool
    {
        $method = $this->request->getMethod();
        if ($method == 'POST') {
            return true;
        }
        return false;
    }
}
