<?php

namespace App\Core;

class ConfigManager
{
    protected static ?Config $instance = null;

    public static function set(Config $config): void
    {
        if (self::$instance !== null) {
            throw new \LogicException('Config already set.');
        }

        self::$instance = $config;
    }
    public static function get(): Config
    {
        if (!self::$instance) {
            throw new \RuntimeException('Config has not been set.');
        }

        return self::$instance;
    }
}

