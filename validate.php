<?php

require_once 'vendor/autoload.php';

try {
    $configFile = __DIR__ . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.ini";
    $config = \fkooman\Config\Config::fromIniFile($configFile);

    // data directory
    $exportDir      = $config->s('output')->l('exportDir', true); // REQ
    $logDir         = $config->s('output')->l('logDir', true); // REQ

    // validate classes
    $validators     = $config->s('validator')->s('validate', false, array())->toArray();

    $timezone       = $config->l('timezone', false, "Europe/Amsterdam");
    date_default_timezone_set($timezone);

    $logger = new \fkooman\janus\log\EntityLog();

    $inputFile = $exportDir . DIRECTORY_SEPARATOR . "export.json";
    $exportData = @file_get_contents($inputFile);
    if (false === $exportData) {
        throw new Exception(sprintf("unable to read JSON file '%s' from disk", $inputFile));
    }

    $entities = json_decode($exportData, true);

    foreach ($validators as $v) {
        echo sprintf("Validator: %s" . PHP_EOL, $v);
        $class = "\\fkooman\\janus\\validate\\validators\\" . $v;
        $validate = new $class($entities, $logger);
        $validate->validateEntities();
    }

    $outputFile = $logDir . DIRECTORY_SEPARATOR . "log.json";
    if (false === @file_put_contents($outputFile, $logger->toJson())) {
        throw new Exception(sprintf("unable to write JSON file '%s' to disk", $outputFile));
    }
} catch (Exception $e) {
    echo sprintf("ERROR: %s", $e->getMessage());
    die(PHP_EOL);
}
