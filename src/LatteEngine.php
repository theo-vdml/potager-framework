<?php

namespace Potager;

use Latte\Engine;

use RuntimeException;

class LatteEngine
{

    protected Engine $latte;
    protected string $viewsPath;
    protected string $cachePath;

    public function __construct(string $viewsPath, string $cachePath)
    {
        $this->latte = new Engine();
        $this->latte->setTempDirectory($cachePath);
        $this->viewsPath = rtrim($viewsPath, '/');
        $this->cachePath = rtrim($cachePath, '/');
    }

    public function render(string $view, array $params = []): string
    {
        $file = $this->resolveView($view);
        return $this->latte->renderToString($file, $params);
    }

    protected function resolveView(string $view): string
    {
        $path = $this->viewsPath . '/' . str_replace('.', '/', $view) . '.latte';
        if (!file_exists($path))
            throw new RuntimeException("Latte view [{$view}] not found at [{$path}]. Did you forget to create it?");
        return $path;
    }

}