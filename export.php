<?php

/**
 * Copyright 2013 François Kooman <francois.kooman@surfnet.nl>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

require_once 'vendor/autoload.php';

$defaultStates = array("testaccepted", "QApending", "QAaccepted", "prodpending", "prodaccepted");

echo date("Y-m-d H:i:s") . " :: starting Janus entity export\n";

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

    $e = new \SURFnet\janus\export\Export($db);

    $entityData = array();
    foreach ($requestedState as $state) {
        $entityData = array_merge($entityData, $e->getEntities("saml20-idp", $state));
        $entityData = array_merge($entityData, $e->getEntities("saml20-sp", $state));
    }

    $sanitizer = new SURFnet\janus\export\Sanitizer($entityData);
    $sanitizer->sanitizeAll();
    $entityData = $sanitizer->getEntityData();

    $outputFile = $dirName . DIRECTORY_SEPARATOR . "export.json";
    if (false === @file_put_contents($outputFile, json_encode($entityData))) {
        throw new Exception(sprintf("unable to write JSON file '%s' to disk", $outputFile));
    }
} catch (Exception $e) {
    echo sprintf("ERROR: %s", $e->getMessage());
    die(PHP_EOL);
}

echo date("Y-m-d H:i:s") . " :: entity export done\n";
