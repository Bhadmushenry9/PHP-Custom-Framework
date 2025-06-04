<?php
declare(strict_types=1);
namespace App;

use App\Exception\ViewNotFoundException;

class View
{
    public function __construct(protected string $view, protected array $data = [], protected bool $useLayout = true)
    {
    }

    public static function make(string $view, array $data = [], bool $useLayout = true): static
    {
        return new static($view, $data, $useLayout);
    }
    public function render(): string
    {
        $viewFile = VIEW_PATH . '/' . $this->view . '.php';

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
            $layoutFile = VIEW_PATH_LAYOUTS . '/layouts.php';
            if (!file_exists($layoutFile)) {
                throw new ViewNotFoundException($layoutFile);
            }
            include $layoutFile;
        } else {
            return (string) $content;
        }
        return '';
    }

    public function __tostring(): string
    {
        return $this->render();
    }
}
