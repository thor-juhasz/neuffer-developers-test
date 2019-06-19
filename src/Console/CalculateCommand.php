<?php

namespace App\Console;

use App\Exception\FileOpenException;
use App\Logger;
use ArithmeticError;
use Exception;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;

/**
 * Class CalculateCommand
 * @package App\Console
 */
class CalculateCommand extends Command {
    /** @var array $allowedActions */
    private static $allowedActions = ['plus', 'minus', 'multiply', 'division'];

    /** @var string $action */
    private $action;

    /** @var string $file */
    private $file;

    /** @var bool|resource $file */
    private $fileHandler;

    /** @var bool|resource $resultsHandler */
    private $resultsHandler;

    /** @var Logger $logger */
    private $logger;

    /**
     * Configure console command.
     *
     * Set command name, description and help text
     * Define the options (action and file)
     */
    public function configure(): void
    {
        $inputOptions = [
            new InputOption(
                'action',
                'a',
                InputOption::VALUE_REQUIRED,
                'The type of calculation to perform.',
                'plus'),
            new InputOption(
                'file',
                'f',
                InputOption::VALUE_OPTIONAL,
                'The CSV file containing the number pairs (separated by ";").',
                'input.csv')
        ];

        $this->setName('calculate')
            ->setDescription('Perform a calculation on a pair of numbers from a CSV file.')
            ->setHelp('This command allows you to perform basic calculations on a pair of numbers from a CSV file.')

            ->setDefinition(new InputDefinition($inputOptions));
    }

    /**
     * Set the options to class variables
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->action = $input->getOption('action');
        $this->file = $input->getOption('file');

        // User can define action as a number.
        // 1 = Plus, 2 = Minus
        // 3 = Multiply, 4 = Division
        $this->actionAsNumber();
    }

    /**
     * User can define action as a number.
     * 1 = Plus, 2 = Minus
     * 3 = Multiply, 4 = Division
     */
    private function actionAsNumber(): void {
        if (in_array((int) $this->action, [1, 2, 3, 4], true)) {
            $idx = (int) $this->action - 1;
            $this->action = static::$allowedActions[$idx];
        }
    }

    /**
     * If user omitted one or both of the options when running the command,
     * here we can ask the user for that information.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function interact(InputInterface $input, OutputInterface $output): void
    {
        // This takes care of asking user for input
        $helper = $this->getHelper('question');

        // Action must be valid
        if (!in_array($this->action, static::$allowedActions, false)) {
            $output->writeln('<error>Missing value for type of calculation.</error>');
            $output->writeln('Please pick one of:');
            $output->writeln('  1: Plus');
            $output->writeln('  2: Minus');
            $output->writeln('  3: Multiply');
            $output->writeln('  4: Division');

            $question = new Question('<question>Choice [1]:</question> ', '1');
            $this->action = $helper->ask($input, $output, $question);
            $output->writeln('');

            $this->actionAsNumber();
        }

        // File must be defined
        if ($this->file === '') {
            $output->writeln('<error>Missing value for input CSV file.</error>');
            $output->writeln('Please type the name of the CSV file (relative path):');

            $question = new Question('<question>Filename:</question> ');
            $this->file = $helper->ask($input, $output, $question);
            $output->writeln('');
        }
    }

    /**
     * Run the console command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws FileOpenException
     * @throws Exception
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->prepare();
        $this->start($output);
    }

    /**
     * Return true if path parameter is empty
     *
     * @param $path
     * @return bool
     */
    private function isPathEmpty($path): bool
    {
        return $path === null || $path === '';
    }

    /**
     * Return true if path parameter is an absolute path
     *
     * @param $path
     * @return bool
     */
    private function isAbsolutePath($path): bool
    {
        return $path[0] === DIRECTORY_SEPARATOR || preg_match('~\A[A-Z]:(?![^/\\\\])~i', $path) > 0;
    }

    /**
     * Check basic errors, and open file handlers
     *
     * @throws InvalidOptionException
     * @throws FileOpenException
     */
    private function prepare(): void
    {
        // Action must be valid
        if (!in_array($this->action, static::$allowedActions, false)) {
            throw new InvalidOptionException('<error>Action not allowed, must be one of plus, minus, multiply or division.</error>');
        }

        // File must be defined
        if ($this->isPathEmpty($this->file)) {
            throw new InvalidOptionException('<error>Input filename must be specified.</error>');
        }

        // File must exist
        if (!file_exists($this->file)) {
            throw new InvalidOptionException(
                sprintf('<error>Input filename \"<comment>%s</comment>\" does not exist.</error>', $this->file));
        }

        // Figure the best way to find the file
        if ($this->isAbsolutePath($this->file)) {
            $file = $this->file;
        } else {
            $file = PROJECT_DIR . $this->file;
        }
        $this->fileHandler = fopen($file, 'rb');

        // Must be able to open file and read it
        if ($this->fileHandler === false) {
            throw new FileOpenException('<error>Can not open input CSV file. Make sure permissions are correctly set.</error>');
        }

        // Must be able to open file as writable (w automatically overwrites old file)
        if (defined('PHPUNIT') && PHPUNIT === true) {
            $file = PROJECT_DIR . 'tests/resources/result.csv';
        } else {
            $file = PROJECT_DIR . 'results/result.csv';
        }
        $this->resultsHandler = fopen($file, 'wb');
        if ($this->resultsHandler === false) {
            throw new FileOpenException('<error>Can not open results file. Make sure permissions are set correctly.</error>');
        }

        // Prepare the logger
        $this->logger = new Logger();
    }

    /**
     * Start calculations
     *
     * @param OutputInterface $output
     * @throws Exception
     */
    private function start(OutputInterface $output): void
    {
        // Class name, based on user selected action
        $className = ucfirst($this->action);

        $this->logger->log('Started action %s', $this->action);

        try {
            // Iterate through the CSV file
            while (($data = fgetcsv($this->fileHandler, 1000, ';')) !== FALSE) {
                if (count($data) !== 2) {
                    throw new ArithmeticError('<error>Invalid record in CSV file. Must contain pair of positive or negative integer numbers.</error>');
                }

                // Make sure values are integers
                $data = array_map('intval', $data);

                // Perform calculation
                $result = call_user_func("App\\Calculations\\$className::calc", ...$data);
                $data[] = $result;

                //print_r($data);

                // If result is higher than 0, record it, else log it.
                if ($result > 0) {
                    $this->record(...$data);
                } else {
                    $this->logger->log('Numbers %s and %s are invalid. Result: %s', ...$data);
                }
            }
        } catch (Exception $exception) {
            // Catch any error and log it.
            $this->logger->log($exception);
        }

        // Close file handlers and log end of action

        $output->writeln('<info>Action ended.</info>');
        $output->writeln('Results are saved in <comment>results/result.csv</comment> file.');
        $output->writeln('Any errors are logged in <comment>results/log.txt</comment>.');
        fclose($this->fileHandler);
        fclose($this->resultsHandler);
        $this->logger->log('Action ended');
        $this->logger = null;
    }

    /**
     * Record to results file
     *
     * @param int $a
     * @param int $b
     * @param int $result
     */
    private function record(int $a, int $b, int $result): void
    {
        $line = implode(';', [$a, $b, $result]);
        fwrite($this->resultsHandler, sprintf("%s\r\n", $line));
    }
}
