<?php

require_once 'vendor/autoload.php';

$defaultStates = array("testaccepted", "QApending", "QAaccepted", "prodpending", "prodaccepted");

try {
    $configFile = __DIR__ . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "config.ini";
    $config = \fkooman\Config\Config::fromIniFile($configFile);

    // database
    $dbDsn          = $config->s('database')->l('dsn', true); // REQ
    $dbUser         = $config->s('database')->l('user');
    $dbPass         = $config->s('database')->l('pass');
    // data directory
    $dirName        = $config->s('output')->l('exportDir', true); // REQ
    // filter
    $requestedState = $config->s('filter')->s('state', false, $defaultStates)->toArray();

    $db = new PDO($dbDsn, $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $e = new \fkooman\janus\export\Export($db);

    $entityData = array();
    foreach ($requestedState as $state) {
        $entityData = array_merge($entityData, $e->getEntities("saml20-idp", $state));
        $entityData = array_merge($entityData, $e->getEntities("saml20-sp", $state));
    }

    $outputFile = $dirName . DIRECTORY_SEPARATOR . "export.json";
    if (false === @file_put_contents($outputFile, json_encode($entityData))) {
        throw new Exception(sprintf("unable to write JSON file '%s' to disk", $outputFile));
    }
} catch (Exception $e) {
    echo sprintf("ERROR: %s", $e->getMessage());
    die(PHP_EOL);
}
