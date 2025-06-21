<?php
declare(strict_types=1);

namespace App\Helpers;

class ViewHelper
{
    protected static array $sections = [];
    protected static array $stacks = [];
    protected static ?string $currentSection = null;

    public static function startSection(string $name): void
    {
        self::$currentSection = $name;
        ob_start();
    }

    public static function endSection(): void
    {
        $content = ob_get_clean();
        if (self::$currentSection) {
            self::$sections[self::$currentSection] = $content;
            self::$currentSection = null;
        }
    }

    public static function yield(string $name): void
    {
        echo self::$sections[$name] ?? '';
    }

    public static function has(string $name): bool
    {
        return isset(self::$sections[$name]);
    }

    public static function section(string $name): ?string
    {
        return self::$sections[$name] ?? null;
    }

    public static function renderLayout(array $vars = []): string
    {
        extract($vars);
        ob_start();
        require __DIR__ . '/../../resources/views/layouts/layout.php';
        return ob_get_clean();
    }

    public static function setTitle(string $title): void
    {
        self::$sections['title'] = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    }

    public static function title(): void
    {
        echo self::$sections['title'] ?? '';
    }

    public static function pushStyles(string|array $styles): void
    {
        foreach ((array) $styles as $style) {
            self::$stacks['styles'][] = $style;
        }
    }

    public static function styles(): void
    {
        foreach (self::$stacks['styles'] ?? [] as $style) {
            echo $style . "\n";
        }
    }

    // --- Scripts ---
    public static function pushScripts(string|array $scripts): void
    {
        foreach ((array) $scripts as $script) {
            self::$stacks['scripts'][] = $script;
        }
    }

    public static function scripts(): void
    {
        foreach (self::$stacks['scripts'] ?? [] as $script) {
            echo $script . "\n";
        }
    }
}
