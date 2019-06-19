<?php

namespace App;

use App\Exception\FileOpenException;
use DateTime, Exception;
use Throwable;


class Logger {
    /** @var bool|resource $logFile */
    public $logHandler;

    /**
     * Logger constructor
     *
     * Open file handler
     *
     * @throws Exception
     */
    public function __construct()
    {
        // Must be able to open as writable (w automatically overwrites old file)
        $file = defined('PHPUNIT') && PHPUNIT === true ?
            'tests/resources/log.txt' : 'results/log.txt';
        $this->logHandler = fopen(PROJECT_DIR . $file, 'wb');
        if ($this->logHandler === false) {
            throw new FileOpenException('Can not open log file. Make sure permissions are set correctly.');
        }
    }

    /**
     * Add line to logfile
     *
     * Can accept multiple arguments.
     * Consecutive arguments are used for string replacement in sprintf function.
     *
     * @param string|Throwable $text
     * @throws Exception
     */
    public function log($text): void
    {
        // If the text is an Exception we need to create a simple text string from it
        if ($text instanceof Throwable) {
            $text = get_class($text) . ': ' . $text->getMessage() ;
        }

        // Timestamp added to beginning of each file
        $timestamp = new DateTime();
        $args = [$timestamp->format('Y-m-d H:i:s.u')];

        // Add any extra params as arguments for sprintf
        if (func_num_args() > 1) {
            $args = array_merge($args, array_slice(func_get_args(), 1));
        }

        fwrite($this->logHandler,
            sprintf("[%s] $text\r\n", ...$args));
    }

    /**
     * Close file handler
     */
    public function __destruct()
    {
        if ($this->logHandler) {
            fclose($this->logHandler);
        }
    }
}