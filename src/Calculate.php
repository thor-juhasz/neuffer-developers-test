<?php

abstract class Calculate implements Operations {
    /** @var string $action */
    protected $action;

    /** @var string $file */
    protected $file;

    /** @var bool|resource $file */
    protected $fileHandle;

    /** @var bool|resource $resultsHandler */
    protected $resultsHandler;

    /** @var Logger $logger */
    protected $logger;

    /**
     * Initialize variables and file handles
     * @param string $action
     * @param string $file
     * @throws Exception
     */
    public function __construct(string $action, string $file)
    {
        $this->action = $action;
        $this->file = $file;

        $this->prepareFiles();

        $this->logger = new Logger();
        $this->logger->log("Started action %s", $this->action);
    }

    /**
     * Open files
     * @throws Exception
     */
    private function prepareFiles(): void
    {
        if (!file_exists($this->file))
            throw new Exception(sprintf("Input filename  \"%s\"  does not exist.", $this->file), 1);

        $this->fileHandle = fopen($this->file, "r");
        if ($this->fileHandle === false)
            throw new Exception("Can not open input file. Make sure permissions are set correctly.", 2);

        $this->resultsHandler = fopen("results/result.csv", "a+");
        if ($this->resultsHandler === false)
            throw new Exception("Can not open results file. Make sure permissions are set correctly.", 2);

        ftruncate($this->resultsHandler, 0);
    }

    /**
     * Start calculations
     */
    public function start(): void
    {
        while (($data = fgetcsv($this->fileHandle, 1000, ";")) !== FALSE) {
            // Make sure values are integers
            $data = array_map(function($val) {
                return intval($val);
            }, $data);

            // Perform calculation
            $result = $this->calc(...$data);

            // If result is higher than 0, record it, else log it.
            if ($result > 0) {
                $this->record($result, ...$data);
            } else {
                $data[] = $result;
                $this->logger->log("Numbers %s and %s are invalid. Result: %s", ...$data);
            }
        }
    }

    /**
     * Record to results file
     * @param int $result
     * @param int $a
     * @param int $b
     */
    public function record(int $result, int $a, int $b): void
    {
        $line = implode(";", [$a, $b, $result]);
        fwrite($this->resultsHandler, sprintf("%s\r\n", $line));
    }

    public function __destruct()
    {
        fclose($this->fileHandle);
        fclose($this->resultsHandler);
        $this->logger->log("Action ended");
    }
}
