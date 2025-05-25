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

    public function render(): string
    {
        return App::useLatte()->render($this->view, $this->params);
    }

}