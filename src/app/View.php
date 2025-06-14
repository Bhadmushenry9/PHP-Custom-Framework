<?php
declare(strict_types=1);

namespace App;

use App\Core\ConfigManager;
use App\Exception\ViewNotFoundException;

class View
{
    protected string $path;

    public function __construct(
        protected string $view,
        protected array $data = [],
        protected bool $useLayout = true
    ) {
        $this->path = ConfigManager::get()->getPath('views');
    }

    public static function make(string $view, array $data = [], bool $useLayout = true): static
    {
        return new static($view, $data, $useLayout);
    }

    public function render(): string
    {
        $viewFile = $this->getViewFilePath($this->view);

        if (!file_exists($viewFile)) {
            throw new ViewNotFoundException($viewFile);
        }

        // Extract data so variables are available in the view
        extract($this->data, EXTR_SKIP);

        // Start output buffering
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        if ($this->useLayout) {
            $layoutFile = $this->path . '/layouts/layouts.php';
            if (!file_exists($layoutFile)) {
                throw new ViewNotFoundException($layoutFile);
            }
            include $layoutFile;
        } else {
            return (string) $content;
        }

        return '';
    }

    protected function getViewFilePath(string $view): string
    {
        // Converts dot notation (e.g., components.badge) into path (e.g., components/badge.php)
        $relativePath = str_replace('.', '/', $view) . '.php';
        return $this->path . '/' . $relativePath;
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
