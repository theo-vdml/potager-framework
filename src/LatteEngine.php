<?php

namespace Potager;

use Latte\Engine;

use RuntimeException;

class LatteEngine
{

    protected Engine $latte;
    protected string $viewsPath;
    protected string $cachePath;

    public function __construct(?string $viewsPath = null, ?string $cachePath = null)
    {
        $this->latte = new Engine();
        $this->viewsPath = rtrim($viewsPath ?? path('/views'), '/');
        $this->cachePath = rtrim($cachePath ?? path('/storage/.cache/latte'), '/');

        // Ensure cache directory exists
        if (!is_dir($this->cachePath)) {
            if (!mkdir($this->cachePath, 0775, true) && !is_dir($this->cachePath)) {
                throw new RuntimeException("Failed to create Latte cache directory at: {$this->cachePath}");
            }
        }

        $this->latte->setTempDirectory($cachePath);
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