<?php
declare(strict_types=1);

namespace App\Logging;

use App\Contracts\LoggerInterface;
use DateTime;

class FileLogger implements LoggerInterface
{
    protected string $logFile;

    public function __construct(string $logPath)
    {
        $this->logFile = $logPath;
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0777, true);
        }
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $timestamp = (new DateTime())->format('Y-m-d H:i:s');
        $interpolated = $this->interpolate($message, $context);
        $entry = "[{$timestamp}] {$level}: {$interpolated}" . PHP_EOL;

        file_put_contents($this->logFile, $entry, FILE_APPEND);
    }

    public function emergency(string $message, array $context = []): void { $this->log('EMERGENCY', $message, $context); }
    public function alert(string $message, array $context = []): void     { $this->log('ALERT', $message, $context); }
    public function critical(string $message, array $context = []): void  { $this->log('CRITICAL', $message, $context); }
    public function error(string $message, array $context = []): void     { $this->log('ERROR', $message, $context); }
    public function warning(string $message, array $context = []): void   { $this->log('WARNING', $message, $context); }
    public function notice(string $message, array $context = []): void    { $this->log('NOTICE', $message, $context); }
    public function info(string $message, array $context = []): void      { $this->log('INFO', $message, $context); }
    public function debug(string $message, array $context = []): void     { $this->log('DEBUG', $message, $context); }

    protected function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = (string) $val;
        }
        return strtr($message, $replace);
    }
}
