<?php

namespace App\Tests\Console;

use App\Console\CalculateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use PHPUnit\Framework\TestCase;

require_once './vendor/autoload.php';
define('PHPUNIT', true);


class CalculateCommandTest extends TestCase
{
    /** @var Command */
    public static $cmd;

    /** @var CommandTester $cmdTester */
    public static $cmdTester;

    /**
     * Load up app and command tester class
     */
    public static function setUpBeforeClass(): void
    {
        // Set project root directory
        define('PROJECT_DIR', dirname(__FILE__, 4) . '/');

        // Load up application
        $app = new Application('CSV Calculation', '0.1');

        // Register our calculation command
        $app->add(new CalculateCommand());

        // Single command application
        //$app->setDefaultCommand('calculate', true);

        // Remove symfony built-in options that we're not using
        $rm = ['ansi', 'no-ansi', 'no-interaction', 'verbose', 'help', 'quiet', 'version'];
        $options = array_diff_key($app->getDefinition()->getOptions(), array_flip($rm));
        $app->getDefinition()->setOptions($options);

        // Get our command and command tester
        self::$cmd = $app->find('calculate');
        self::$cmdTester = new CommandTester(self::$cmd);
    }

    /**
     * Test the plus action
     */
    public function testPlus(): void
    {
        static::$cmdTester->execute([
            'command' => static::$cmd->getName(),
            '--action' => 'plus',
            '--file' => PROJECT_DIR . 'tests/resources/test.csv'
        ]);

        $expectedResults = [42, 75, 123, 16];
        $expectedLogs = [-156, -96, -11, -77, -55, -23];

        $resFile = fopen(PROJECT_DIR . 'tests/resources/result.csv', 'rb');
        $logFile = fopen(PROJECT_DIR . 'tests/resources/log.txt', 'rb');

        $i = 0;
        while (($data = fgetcsv($resFile, 1000, ';')) !== FALSE) {
            $data = array_map('intval', $data);
            $this->assertEquals($expectedResults[$i], $data[2]);
            $i++;
        }

        $i = 0;
        while (($data = fgets($logFile, 1000)) !== FALSE) {
            if (substr($data, -21, -2) === 'Started action plus' ||
                substr($data, -14, -2) === 'Action ended' ||
                strpos($data, "\n") === 0) {
                continue;
            }
            $data = array_map('intval', explode(' ', $data));
            $this->assertEquals($expectedLogs[$i], array_values(array_slice($data, -1))[0]);
            $i++;
        }
    }

    /**
     * Test the minus action
     */
    public function testMinus(): void
    {
        static::$cmdTester->execute([
            'command' => static::$cmd->getName(),
            '--action' => 'minus',
            '--file' => PROJECT_DIR . 'tests/resources/test.csv'
        ]);

        $expectedResults = [62, 95, 19, 15, 115, 21];
        $expectedLogs = [-12, 0, -93, -112];

        $resFile = fopen(PROJECT_DIR . 'tests/resources/result.csv', 'rb');
        $logFile = fopen(PROJECT_DIR . 'tests/resources/log.txt', 'rb');

        $i = 0;
        while (($data = fgetcsv($resFile, 1000, ';')) !== FALSE) {
            $data = array_map('intval', $data);
            $this->assertEquals($expectedResults[$i], $data[2]);
            $i++;
        }

        $i = 0;
        while (($data = fgets($logFile, 1000)) !== FALSE) {
            if (substr($data, -22, -2) === 'Started action minus' ||
                substr($data, -14, -2) === 'Action ended' ||
                strpos($data, "\n") === 0) {
                continue;
            }
            $data = array_map('intval', explode(' ', $data));
            $this->assertEquals($expectedLogs[$i], array_values(array_slice($data, -1))[0]);
            $i++;
        }
    }

    /**
     * Test the multiply action
     */
    public function testMultiply(): void
    {
        static::$cmdTester->execute([
            'command' => static::$cmd->getName(),
            '--action' => 'multiply',
            '--file' => PROJECT_DIR . 'tests/resources/test.csv'
        ]);

        $expectedResults = [6048, 2304, 1426, 3672];
        $expectedLogs = [-520, -850, -60, -1406, -3174, -3072];

        $resFile = fopen(PROJECT_DIR . 'tests/resources/result.csv', 'rb');
        $logFile = fopen(PROJECT_DIR . 'tests/resources/log.txt', 'rb');

        $i = 0;
        while (($data = fgetcsv($resFile, 1000, ';')) !== FALSE) {
            $data = array_map('intval', $data);
            $this->assertEquals($expectedResults[$i], $data[2]);
            $i++;
        }

        $i = 0;
        while (($data = fgets($logFile, 1000)) !== FALSE) {
            if (substr($data, -25, -2) === 'Started action multiply' ||
                substr($data, -14, -2) === 'Action ended' ||
                strpos($data, "\n") === 0) {
                continue;
            }
            $data = array_map('intval', explode(' ', $data));
            $this->assertEquals($expectedLogs[$i], array_values(array_slice($data, -1))[0]);
            $i++;
        }
    }

    /**
     * Test the division action
     */
    public function testDivision(): void
    {
        static::$cmdTester->execute([
            'command' => static::$cmd->getName(),
            '--action' => 'division',
            '--file' => PROJECT_DIR . 'tests/resources/test.csv'
        ]);

        $expectedResults = [1, 1, 1];
        $expectedLogs = [-5, -8, 0, 0, -3, 0, 0];

        $resFile = fopen(PROJECT_DIR . 'tests/resources/result.csv', 'rb');
        $logFile = fopen(PROJECT_DIR . 'tests/resources/log.txt', 'rb');

        $i = 0;
        while (($data = fgetcsv($resFile, 1000, ';')) !== FALSE) {
            $data = array_map('intval', $data);
            $this->assertEquals($expectedResults[$i], $data[2]);
            $i++;
        }

        $i = 0;
        while (($data = fgets($logFile, 1000)) !== FALSE) {
            if (substr($data, -25, -2) === 'Started action division' ||
                substr($data, -14, -2) === 'Action ended' ||
                strpos($data, "\n") === 0) {
                continue;
            }
            $data = array_map('intval', explode(' ', $data));
            $this->assertEquals($expectedLogs[$i], array_values(array_slice($data, -1))[0]);
            $i++;
        }
    }
}
