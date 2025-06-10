<?php

namespace Potager;

class View
{

    protected string $view;
    protected array $params;

    public function __construct(string $view, array $params = [])
    {
        $this->view = $view;
        $this->params = $params;
    }

    /**
     * Add a variable to the view's parameters.
     */
    public function with(string $key, mixed $value): static
    {
        $this->params[$key] = $value;
        return $this;
    }

    public function withMany(array $vars): static
    {
        $this->params = array_merge($this->params, $vars);
        return $this;
    }

    public function render(): string
    {
        return App::useLatte()->render($this->view, $this->params);
    }

}