<?php

require_once 'vendor/autoload.php';

try {
    $configFile = __DIR__ . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.ini";
    $config = \fkooman\Config\Config::fromIniFile($configFile);

    // data directory
    $dirName        = $config->s('export')->l('dir', true); // REQ

    // validate classes
    $validators     = $config->s('validator')->s('validate', false, array())->toArray();

    $logger = new \fkooman\janus\log\EntityLog();

    $inputFile = $dirName . DIRECTORY_SEPARATOR . "export.json";
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

    $outputFile = $dirName . DIRECTORY_SEPARATOR . "log.json";
    if (false === @file_put_contents($outputFile, $logger->toJson())) {
        throw new Exception(sprintf("unable to write JSON file '%s' to disk", $outputFile));
    }
} catch (Exception $e) {
    echo sprintf("ERROR: %s", $e->getMessage());
    die(PHP_EOL);
}
