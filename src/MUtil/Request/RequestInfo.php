<?php

namespace MUtil\Request;

use Mezzio\Router\Route;

class RequestInfo
{
    /**
     * @var string|null Name of the current action
     */
    protected ?string $currentAction = null;

    /**
     * @var string|null Name of the current controller
     */
    protected ?string $currentController = null;

    protected ?Route $currentRoute = null;

    /**
     * @var bool Is the current request a POST request?
     */
    protected bool $isPost = false;

    /**
     * @var array The matched Route params
     */
    protected array $requestMatchedParams = [];

    /**
     * @var array POST request content
     */
    protected array $requestPost = [];

    /**
     * @var array query params
     */
    protected array $requestQueryParams = [];


    /**
     * @param string|null $currentAction
     */
    public function setCurrentAction(?string $currentAction): void
    {
        $this->currentAction = $currentAction;
    }

    /**
     * @param string|null $currentController
     */
    public function setCurrentController(?string $currentController): void
    {
        $this->currentController = $currentController;
    }

    public function setCurrentRoute(Route $route): void
    {
        $this->currentRoute = $route;
    }

    /**
     * @param bool $isPost
     */
    public function setIsPost(bool $isPost): void
    {
        $this->isPost = $isPost;
    }

    /**
     * @param array $requestMatchedParams
     */
    public function setRequestMatchedParams(array $requestMatchedParams): void
    {
        $this->requestMatchedParams = $requestMatchedParams;
    }

    /**
     * @param array $requestPost
     */
    public function setRequestPost(array $requestPost): void
    {
        $this->requestPost = $requestPost;
    }

    /**
     * @param array $requestQueryParams
     */
    public function setRequestQueryParams(array $requestQueryParams): void
    {
        $this->requestQueryParams = $requestQueryParams;
    }

    /**
     * Get the current action name
     *
     * @return string|null
     */
    public function getCurrentAction(): ?string
    {
        return $this->currentAction;
    }

    /**
     * Get the current Controller name
     *
     * @return string|null
     */
    public function getCurrentController(): ?string
    {
        return $this->currentController;
    }

    public function getCurrentRoute(): ?Route
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getRequestMatchedParams(): array
    {
        return $this->requestMatchedParams;
    }

    /**
     * @return array POST request content
     */
    public function getRequestPostParams(): array
    {
        return $this->requestPost;
    }

    /**
     * @return array query params
     */
    public function getRequestQueryParams(): array
    {
        return $this->requestQueryParams;
    }

    /**
     * @return bool is the current request a POST request?
     */
    public function isPost(): bool
    {
        return $this->isPost;
    }
}