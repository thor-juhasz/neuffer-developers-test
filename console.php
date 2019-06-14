<?php

/**
 * For description of input parameters, read NOWDOC at bottom of file.
 */
$shortOpts = "a:f::";
$longOpts  = ["action:", "file::"];
$options = getopt($shortOpts, $longOpts);

if (isset($options['a'])) {
    $action = $options['a'];
} elseif (isset($options['action'])) {
    $action = $options['action'];
} else {
    $action = "";
}

if (isset($options['f'])) {
    $file = $options['f'];
} elseif (isset($options['file'])) {
    $file = $options['file'];
} else {
    $file = "input.csv";
}

try {
    include('src/Operations.php');
    include('src/Calculate.php');
    include('src/Logger.php');

    if (!in_array($action, ['plus', 'minus', 'multiply', 'division']))
        throw new Exception(sprintf("The action  \"%s\"  is not valid", $action), 1);

    include("src/Calculations/" . ucfirst($action) . '.php');
    $className = ucfirst($action);
    /** @var Calculate $calc */
    $calc = new $className($action, $file);

    $calc->start();
} catch (Exception $exception) {
    print_r($exception->getMessage() . "\n");
    if ((int) $exception->getCode() === 1) {
        print_r(<<<EOT

Usage:
  php console.php -a=ACTION [-f=FILENAME]

-a|--action=ACTION
  ACTION can be one of: plus, minus, multiply or division.
  No default value.

-f|--file=FILENAME
  FILENAME must be a valid CSV file name, including path
  if it's not in the current directory.
  Default value "input.csv".

EOT
);
    }
    exit(1);
}
