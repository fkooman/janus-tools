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
    // export
    $dirName        = $config->s('export')->l('dir', true); // REQ
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

    if (false === @file_put_contents($dirName . DIRECTORY_SEPARATOR . "export.json", json_encode($entityData))) {
        throw new Exception("unable to write JSON file to disk");
    }
} catch (Exception $e) {
    echo sprintf("ERROR: %s", $e->getMessage());
    die(PHP_EOL);
}
