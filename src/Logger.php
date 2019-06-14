<?php

class Logger {
    /** @var bool|resource $logFile */
    public $logHandler;

    public function __construct()
    {
        $this->logHandler = fopen("results/log.txt", "a+");
        if ($this->logHandler === false)
            throw new Exception("Can not open log file. Make sure permissions are set correctly.", 2);

        ftruncate($this->logHandler, 0);
    }

    /**
     * Add line to logfile
     * @param $text
     * @throws Exception
     */
    public function log($text): void
    {
        $timestamp = new DateTime();

        $args = [];
        if (func_num_args() > 1) {
            $args = array_slice(func_get_args(), 1);
        }

        fwrite($this->logHandler, sprintf("[%s] $text\r\n", $timestamp->format("Y-m-d H:i:s.u"), ...$args));
    }

    public function __destruct()
    {
        fclose($this->logHandler);
    }
}